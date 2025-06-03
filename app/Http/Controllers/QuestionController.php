<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Question;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Ask a question about a specific lesson
     */
    public function askQuestion(Request $request, Lesson $lesson): JsonResponse
    {
        try {
            $validated = $request->validate([
                'question' => 'required|string|min:5|max:500'
            ]);

            // Get AI answer
            $aiResponse = $this->aiService->answerQuestion($lesson, $validated['question']);

            // Save the question and response
            $question = Question::create([
                'lesson_id' => $lesson->id,
                'user_id' => Auth::id(),
                'question' => $validated['question'],
                'ai_response' => $aiResponse,
            ]);

            // Get lesson recommendations based on the question
            $allLessons = Lesson::where('id', '!=', $lesson->id)->get();
            $recommendations = $this->aiService->recommendLessons($lesson, $validated['question'], $allLessons);

            return response()->json([
                'answer' => $aiResponse,
                'question_id' => $question->id,
                'recommendations' => $recommendations->map(function($rec) {
                    return [
                        'id' => $rec->id,
                        'title' => $rec->title,
                        'subject' => $rec->subject,
                        'summary' => $rec->summary
                    ];
                })->take(3)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process question',
                'message' => 'Please try again later'
            ], 500);
        }
    }

    /**
     * Get all questions for a specific lesson
     */
    public function getLessonQuestions(Lesson $lesson): JsonResponse
    {
        $questions = Question::where('lesson_id', $lesson->id)
                           ->where('user_id', Auth::id()) // Only user's own questions
                           ->orderBy('created_at', 'desc')
                           ->take(10)
                           ->get(['id', 'question', 'ai_response', 'created_at']);

        return response()->json($questions);
    }

    /**
     * Get user's question history across all lessons
     */
    public function getUserQuestions(Request $request): JsonResponse
    {
        $query = Question::where('user_id', Auth::id())
                        ->with('lesson:id,title,subject');

        // Filter by lesson if provided
        if ($request->has('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }

        // Search in questions
        if ($request->has('search')) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }

        $questions = $query->orderBy('created_at', 'desc')
                          ->paginate(15);

        return response()->json($questions);
    }

    /**
     * Get popular questions (for admin insights)
     */
    public function popularQuestions(): JsonResponse
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Find similar questions by grouping by question content similarity
        $questions = Question::with(['lesson:id,title', 'user:id,name'])
                           ->select('question', 'lesson_id')
                           ->selectRaw('count(*) as frequency')
                           ->groupBy('question', 'lesson_id')
                           ->having('frequency', '>', 1)
                           ->orderByDesc('frequency')
                           ->take(20)
                           ->get();

        return response()->json($questions);
    }

    /**
     * Rate a question/answer pair (for improving AI responses)
     */
    public function rateResponse(Request $request, Question $question): JsonResponse
    {
        // Only the question owner can rate
        if ($question->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'feedback' => 'nullable|string|max:500'
            ]);

            $question->update([
                'rating' => $validated['rating'],
                'feedback' => $validated['feedback'] ?? null,
                'rated_at' => now()
            ]);

            return response()->json(['message' => 'Thank you for your feedback!']);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        }
    }

    /**
     * Delete a question (user can delete their own questions)
     */
    public function destroy(Question $question): JsonResponse
    {
        // Only the question owner or admin can delete
        if ($question->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully']);
    }

    /**
     * Get question analytics for a lesson (admin only)
     */
    public function lessonAnalytics(Lesson $lesson): JsonResponse
    {
        if (!Auth::user()->hasRole('admin') && $lesson->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $analytics = [
            'total_questions' => $lesson->questions()->count(),
            'unique_students' => $lesson->questions()->distinct('user_id')->count(),
            'average_rating' => $lesson->questions()->whereNotNull('rating')->avg('rating'),
            'common_topics' => $this->extractCommonTopics($lesson),
            'recent_questions' => $lesson->questions()
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get(['question', 'created_at', 'user_id', 'rating']),
            'difficulty_indicators' => $this->analyzeDifficulty($lesson)
        ];

        return response()->json($analytics);
    }

    /**
     * Bulk export questions for a lesson (admin feature)
     */
    public function exportLessonQuestions(Lesson $lesson): JsonResponse
    {
        if (!Auth::user()->hasRole('admin') && $lesson->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $questions = $lesson->questions()
                          ->with('user:id,name,email')
                          ->orderBy('created_at', 'desc')
                          ->get();

        $export = $questions->map(function($q) {
            return [
                'student_name' => $q->user->name,
                'student_email' => $q->user->email,
                'question' => $q->question,
                'ai_response' => $q->ai_response,
                'rating' => $q->rating,
                'feedback' => $q->feedback,
                'asked_at' => $q->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'lesson_title' => $lesson->title,
            'total_questions' => $questions->count(),
            'questions' => $export
        ]);
    }

    /**
     * Extract common topics from questions using basic keyword analysis
     */
    private function extractCommonTopics(Lesson $lesson): array
    {
        $questions = $lesson->questions()->pluck('question');
        $words = [];
        
        foreach ($questions as $question) {
            $questionWords = str_word_count(strtolower($question), 1);
            foreach ($questionWords as $word) {
                if (strlen($word) > 3) { // Filter out short words
                    $words[] = $word;
                }
            }
        }

        return array_slice(array_count_values($words), 0, 10, true);
    }

    /**
     * Analyze question difficulty patterns
     */
    private function analyzeDifficulty(Lesson $lesson): array
    {
        $questions = $lesson->questions();
        
        return [
            'questions_with_low_rating' => $questions->where('rating', '<=', 2)->count(),
            'questions_with_feedback' => $questions->whereNotNull('feedback')->count(),
            'repeat_questions' => $questions->select('question')
                ->groupBy('question')
                ->havingRaw('count(*) > 1')
                ->count()
        ];
    }
}