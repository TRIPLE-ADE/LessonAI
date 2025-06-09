<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LessonController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function askQuestion(Request $request, Lesson $lesson)
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

        // Get updated messages (this includes both user question and AI response)
        $messages = $this->getFormattedMessages($lesson);
        
        // Get updated questions count
        $questionsCount = Question::where('lesson_id', $lesson->id)
                                ->where('user_id', Auth::id())
                                ->count();

        // Return the new user message and AI response for immediate display
        $newUserMessage = [
            'id' => 'user_' . $question->id,
            'type' => 'user',
            'content' => $validated['question'],
            'created_at' => $question->created_at->toISOString()
        ];

        $newAiMessage = [
            'id' => 'ai_' . $question->id,
            'type' => 'assistant',
            'content' => $aiResponse,
            'created_at' => $question->created_at->addSeconds(1)->toISOString()
        ];

        return response()->json([
            'success' => true,
            'newUserMessage' => $newUserMessage,
            'newAiMessage' => $newAiMessage,
            'allMessages' => $messages,
            'questionsCount' => $questionsCount,
            'message' => 'Question answered successfully!'
        ]);

    } catch (\Exception $e) {
        \Log::error('Question processing error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Failed to process question. Please try again.'
        ], 422);
    }
}
    /**
     * Clear chat history for a lesson
     */
    public function clearChat(Lesson $lesson)
    {
        Question::where('lesson_id', $lesson->id)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with([
            'messages' => [],
            'questionsCount' => 0,
            'flash' => [
                'success' => 'Chat history cleared successfully!'
            ]
        ]);
    }

    /**
     * Get formatted messages for chat interface
     */
    private function getFormattedMessages(Lesson $lesson)
    {
        $questions = Question::where('lesson_id', $lesson->id)
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get();

        $messages = [];
        foreach ($questions as $question) {
            // User message
            $messages[] = [
                'id' => 'user_' . $question->id,
                'type' => 'user',
                'content' => $question->question,
                'created_at' => $question->created_at->toISOString()
            ];

            // AI response
            $messages[] = [
                'id' => 'ai_' . $question->id,
                'type' => 'assistant',
                'content' => $question->ai_response,
                'created_at' => $question->created_at->addSeconds(1)->toISOString()
            ];
        }

        return $messages;
    }
    public function adminDashboard()
    {
        // Ensure only admins can access
        if (!Auth::user()->isAdmin) {
            abort(403, 'Unauthorized');
        }

        // Get recent lessons with creator information
        $lessons = Lesson::with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get counts for dashboard stats
        $studentsCount = User::where('isAdmin', false)->count();
        $questionsCount = Question::count();

        return Inertia::render('admin/Dashboard', [
            'lessons' => $lessons,
            'studentsCount' => $studentsCount,
            'questionsCount' => $questionsCount,
        ]);
    }

    /**
     * Display admin lessons management page
     */
    public function adminIndex()
    {
        // Ensure only admins can access
        if (!Auth::user()->isAdmin) {
            abort(403, 'Unauthorized');
        }

        // Get all lessons with creator info and question count
        $lessons = Lesson::with('creator:id,name')
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('admin/ManageLesson', [
            'lessons' => $lessons,
        ]);
    }

    /**
     * Display lessons for students with proper data structure for the Vue component
     */
    public function studentIndex(Request $request)
    {
        $userId = Auth::id();

        // Get all lessons with necessary relationships
        $lessons = Lesson::with(['creator:id,name'])
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'content' => $lesson->content,
                    'subject' => $lesson->subject,
                    'grade_level' => $lesson->grade_level,
                    'tags' => $lesson->tags ?? [],
                    'view_count' => $lesson->view_count ?? 0,
                    'created_at' => $lesson->created_at,
                    'updated_at' => $lesson->updated_at,
                    'creator' => $lesson->creator,
                    'questions_count' => $lesson->questions_count,
                ];
            });

        // Get completed lessons for current user
        $completedLessons = $this->getCompletedLessonsForUser($userId);

        // Get lesson progress for current user
        $lessonProgress = $this->getLessonProgressForUser($userId);

        // Get question counts per lesson for current user
        $questionCounts = $this->getQuestionCountsForUser($userId);

        return Inertia::render('student/BrowseLessons', [
            'lessons' => $lessons,
            'completedLessons' => $completedLessons,
            'lessonProgress' => $lessonProgress,
            'questionCounts' => $questionCounts,
        ]);
    }

    /**
     * Get completed lessons for a user
     * You'll need to implement this based on your completion tracking system
     */
    private function getCompletedLessonsForUser($userId)
    {
        return [];
    }

    /**
     * Get lesson progress for a user
     */
    private function getLessonProgressForUser($userId)
    {
        return (object) [];
    }

    /**
     * Get question counts per lesson for a user
     */
    private function getQuestionCountsForUser($userId)
    {
        $questionCounts = Question::where('user_id', $userId)
            ->select('lesson_id', DB::raw('count(*) as count'))
            ->groupBy('lesson_id')
            ->pluck('count', 'lesson_id')
            ->toArray();

        return $questionCounts;
    }



    public function create()
    {
        if (!Auth::user()->isAdmin) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('admin/CreateLesson');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
            'subject' => 'required|string|max:100',
            'grade_level' => 'required|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50'
        ]);

        $lesson = Lesson::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'subject' => $validated['subject'],
            'grade_level' => $validated['grade_level'],
            'tags' => $validated['tags'] ?? [],
            'created_by' => Auth::id(),
        ]);

        $lesson->save();

        return redirect()->route('admin.dashboard')->with('success', 'Lesson created successfully');
    }
    public function show(Lesson $lesson)
    {
        // Load creator relationship and question count
        $lesson->load('creator:id,name');
        $lesson->loadCount('questions');

        // Only increment view count for students, not admins
        if (!Auth::user()->isAdmin) {
            $lesson->increment('view_count');
        }

        // Get existing messages and question count for current user
        $messages = $this->getFormattedMessages($lesson);
        $questionsCount = Question::where('lesson_id', $lesson->id)
            ->where('user_id', Auth::id())
            ->count();


        // Check if this is an admin accessing the lesson
        if (Auth::user()->isAdmin) {
            return Inertia::render('admin/LessonShow', [
                'lesson' => $lesson,
                'messages' => $messages,
                'questionsCount' => $questionsCount,
            ]);
        }

        // Regular student view
        return Inertia::render('student/LessonView', [
            'lesson' => $lesson,
            'messages' => $messages,
            'questionsCount' => $questionsCount,
            'isCompleted' => false, // You'll need to implement completion tracking
        ]);
    }

    public function edit(Lesson $lesson)
    {
        if (!Auth::user()->isAdmin && $lesson->created_by !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('admin/Lessons/Edit', [
            'lesson' => $lesson
        ]);
    }

    public function update(Request $request, Lesson $lesson)
    {
        if (!Auth::user()->isAdmin && $lesson->created_by !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string|min:50',
            'subject' => 'sometimes|required|string|max:100',
            'grade_level' => 'sometimes|required|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50'
        ]);

        $lesson->update($validated);

        if (isset($validated['content'])) {
            // Regenerate summary if content changed
            // $lesson->summary = $this->aiService->summarizeLesson($lesson);
            $lesson->save();
        }

        return redirect()->route('admin.lessons.show', $lesson->id)->with('success', 'Lesson updated successfully');
    }

    public function destroy(Lesson $lesson)
    {
        if (!Auth::user()->isAdmin && $lesson->created_by !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $lesson->delete();

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson deleted successfully');
    }

    public function myLessons()
    {
        $lessons = Lesson::where('created_by', Auth::id())
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('admin/Lessons/MyLessons', [
            'lessons' => $lessons,
        ]);
    }

    public function popular()
    {
        $lessons = Lesson::withCount(['questions'])
            ->orderByDesc('view_count')
            ->orderByDesc('questions_count')
            ->take(6)
            ->get();

        return Inertia::render('student/Lessons/Popular', [
            'lessons' => $lessons,
        ]);
    }

    public function bySubject(string $subject)
    {
        $lessons = Lesson::where('subject', $subject)
            ->with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return Inertia::render('student/Lessons/Subject', [
            'subject' => $subject,
            'lessons' => $lessons,
        ]);
    }

    public function statistics()
    {
        if (!Auth::user()->isAdmin) {
            abort(403, 'Unauthorized');
        }

        $stats = [
            'total_lessons' => Lesson::count(),
            'total_questions' => Question::count(),
            'most_popular_subject' => Lesson::select('subject')
                ->selectRaw('count(*) as count')
                ->groupBy('subject')
                ->orderByDesc('count')
                ->first(),
            'recent_lessons' => Lesson::with('creator:id,name')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'most_questioned_lessons' => Lesson::withCount('questions')
                ->orderByDesc('questions_count')
                ->take(5)
                ->get()
        ];

        return Inertia::render('admin/Lessons/Statistics', [
            'stats' => $stats,
        ]);
    }
}