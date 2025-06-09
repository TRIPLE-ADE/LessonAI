<?php

use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QuestionController;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Protected routes (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard - redirects based on user role
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('student.dashboard');
    })->name('dashboard');
    
    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

           Route::get('/dashboard', [LessonController::class, 'adminDashboard'])->name('dashboard');
        
        // Lesson 
        
        // Lesson management routes
        Route::get('/lessons', [LessonController::class, 'adminIndex'])->name('lessons.index');
        Route::get('/lessons/create', [LessonController::class, 'create'])->name('lessons.create');
        Route::post('/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
        Route::get('/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
        
        // View questions/responses for lessons
        Route::get('/lessons/{lesson}/questions', [QuestionController::class, 'adminIndex'])->name('lessons.questions');
    });
    
    // Student routes
    Route::middleware(['student'])->prefix('student')->name('student.')->group(function () {
        // Updated student dashboard route
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        
        // Student lesson viewing
        Route::get('/lessons', [LessonController::class, 'studentIndex'])->name('lessons.index');
        Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

         // Lesson interactions
        Route::post('/lessons/{lesson}/ask', [LessonController::class, 'askQuestion'])->name('lessons.ask');
        Route::delete('/lessons/{lesson}/clear-chat', [LessonController::class, 'clearChat'])->name('lessons.clear-chat');
        Route::post('/lessons/{lesson}/complete', [LessonController::class, 'markComplete'])->name('lessons.complete');
        Route::post('/lessons/{lesson}/bookmark', [LessonController::class, 'toggleBookmark'])->name('lessons.bookmark');
        
        // Student questions
        Route::post('/lessons/{lesson}/questions', [QuestionController::class, 'askQuestion'])->name('questions.store');
        Route::get('/lessons/{lesson}/questions', [QuestionController::class, 'getLessonQuestions'])->name('questions.index');
        Route::get('/questions/history', [QuestionController::class, 'getUserQuestions'])->name('questions.history');
        Route::post('/questions/{question}/rate', [QuestionController::class, 'rateResponse'])->name('questions.rate');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';