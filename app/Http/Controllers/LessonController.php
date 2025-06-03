<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LessonController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of lessons
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lesson::query();

        // Filter by subject if provided
        if ($request->has('subject')) {
            $query->where('subject', $request->subject);
        }

        // Filter by grade level if provided
        if ($request->has('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        // Search functionality
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $lessons = $query->with('creator:id,name')
                        ->orderBy('created_at', 'desc')
                        ->paginate(12);

        return response()->json($lessons);
    }

    /**
     * Store a newly created lesson
     */
    public function store(Request $request): JsonResponse
    {
        // Only admins/teachers can create lessons
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
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

            // Generate AI summary for the lesson
            $lesson->summary = $this->aiService->summarizeLesson($lesson);
            $lesson->save();

            return response()->json([
                'message' => 'Lesson created successfully',
                'lesson' => $lesson->load('creator:id,name')
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified lesson
     */
    public function show(Lesson $lesson): JsonResponse
    {
        $lesson->load('creator:id,name');
        
        // Increment view count
        $lesson->increment('view_count');

        return response()->json($lesson);
    }

    /**
     * Update the specified lesson
     */
    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        // Only the creator or admin can update
        if (!Auth::user()->hasRole('admin') && $lesson->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string|min:50',
                'subject' => 'sometimes|required|string|max:100',
                'grade_level' => 'sometimes|required|string|max:50',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50'
            ]);

            $lesson->update($validated);

            // Regenerate summary if content changed
            if (isset($validated['content'])) {
                $lesson->summary = $this->aiService->summarizeLesson($lesson);
                $lesson->save();
            }

            return response()->json([
                'message' => 'Lesson updated successfully',
                'lesson' => $lesson->load('creator:id,name')
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified lesson
     */
    public function destroy(Lesson $lesson): JsonResponse
    {
        // Only the creator or admin can delete
        if (!Auth::user()->hasRole('admin') && $lesson->created_by !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted successfully']);
    }

    /**
     * Get lessons created by the authenticated user
     */
    public function myLessons(): JsonResponse
    {
        $lessons = Lesson::where('created_by', Auth::id())
                        ->withCount(['questions'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return response()->json($lessons);
    }

    /**
     * Get popular lessons
     */
    public function popular(): JsonResponse
    {
        $lessons = Lesson::withCount(['questions'])
                        ->orderByDesc('view_count')
                        ->orderByDesc('questions_count')
                        ->take(6)
                        ->get();

        return response()->json($lessons);
    }

    /**
     * Get lessons by subject
     */
    public function bySubject(string $subject): JsonResponse
    {
        $lessons = Lesson::where('subject', $subject)
                        ->with('creator:id,name')
                        ->orderBy('created_at', 'desc')
                        ->paginate(12);

        return response()->json($lessons);
    }

    /**
     * Get lesson statistics (for admin dashboard)
     */
    public function statistics(): JsonResponse
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_lessons' => Lesson::count(),
            'total_questions' => \App\Models\Question::count(),
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

        return response()->json($stats);
    }
}