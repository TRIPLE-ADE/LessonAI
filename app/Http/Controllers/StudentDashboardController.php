<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get dashboard statistics
        $dashboardData = $this->getDashboardStats($user);
        
        // Get recent activity (last 10 questions)
        $recentActivity = $this->getRecentActivity($user);
        
        // Get featured lessons (latest 5 lessons)
        $featuredLessons = $this->getFeaturedLessons();
        
        // Get continue lesson (last lesson the user asked a question about)
        $continueLesson = $this->getContinueLesson($user);
        
        return Inertia::render('student/Dashboard', [
            'completedLessons' => $dashboardData['completed_lessons'],
            'totalQuestions' => $dashboardData['total_questions'],
            'learningStreak' => $dashboardData['learning_streak'],
            'recentActivity' => $recentActivity,
            'featuredLessons' => $featuredLessons,
            'continueLesson' => $continueLesson,
        ]);
    }
    
    /**
     * Get dashboard statistics for the user
     */
    private function getDashboardStats(User $user): array
    {
        // Count lessons where user has asked at least one question (considered "completed")
        $completedLessons = Question::where('user_id', $user->id)
            ->distinct('lesson_id')
            ->count();
        
        // Total questions asked by user
        $totalQuestions = Question::where('user_id', $user->id)->count();
        
        // Calculate learning streak (consecutive days with questions)
        $learningStreak = $this->calculateLearningStreak($user);
        
        return [
            'completed_lessons' => $completedLessons,
            'total_questions' => $totalQuestions,
            'learning_streak' => $learningStreak,
        ];
    }
    
    /**
     * Get recent activity for the user
     */
    private function getRecentActivity(User $user): array
    {
        return Question::where('user_id', $user->id)
            ->with(['lesson:id,title'])
            ->select('id', 'question', 'lesson_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'lesson_title' => $question->lesson->title,
                    'created_at' => $question->created_at->toISOString(),
                ];
            })
            ->toArray();
    }
    
    /**
     * Get featured lessons for the dashboard
     */
    private function getFeaturedLessons(): array
    {
        return Lesson::with(['questions' => function ($query) {
                $query->select('lesson_id', DB::raw('count(*) as questions_count'))
                      ->groupBy('lesson_id');
            }])
            ->select('id', 'title', 'content', 'subject', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'content' => $lesson->content,
                    'subject' => $lesson->subject,
                    'questions_count' => $lesson->questions->count(),
                    'created_at' => $lesson->created_at->toISOString(),
                ];
            })
            ->toArray();
    }
    
    /**
     * Get the lesson to continue (last lesson user interacted with)
     */
    private function getContinueLesson(User $user): ?array
    {
        $lastQuestion = Question::where('user_id', $user->id)
            ->with(['lesson:id,title,subject'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        if (!$lastQuestion || !$lastQuestion->lesson) {
            return null;
        }
        
        return [
            'id' => $lastQuestion->lesson->id,
            'title' => $lastQuestion->lesson->title,
            'subject' => $lastQuestion->lesson->subject,
        ];
    }
    
    /**
     * Calculate learning streak in days
     */
    private function calculateLearningStreak(User $user): int
    {
        // Get all unique dates when user asked questions
        $questionDates = Question::where('user_id', $user->id)
            ->select(DB::raw('DATE(created_at) as question_date'))
            ->distinct()
            ->orderBy('question_date', 'desc')
            ->pluck('question_date')
            ->map(function ($date) {
                return Carbon::parse($date);
            })
            ->toArray();
        
        if (empty($questionDates)) {
            return 0;
        }
        
        $streak = 0;
        $currentDate = Carbon::today();
        
        // Check if user has activity today or yesterday to start counting
        $lastActivityDate = $questionDates[0];
        $daysDiff = $currentDate->diffInDays($lastActivityDate);
        
        if ($daysDiff > 1) {
            return 0; // Streak broken if no activity for more than 1 day
        }
        
        // Count consecutive days
        foreach ($questionDates as $index => $date) {
            if ($index === 0) {
                $streak = 1;
                continue;
            }
            
            $previousDate = $questionDates[$index - 1];
            $daysBetween = $previousDate->diffInDays($date);
            
            if ($daysBetween === 1) {
                $streak++;
            } else {
                break; // Streak broken
            }
        }
        
        return $streak;
    }
}