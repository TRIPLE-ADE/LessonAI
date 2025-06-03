<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'user_id',
        'question',
        'ai_response',
        'rating',
        'feedback',
        'rated_at'
    ];

    protected $casts = [
        'rated_at' => 'datetime',
        'rating' => 'integer'
    ];

    /**
     * Get the lesson this question belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the user who asked this question
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for getting questions with ratings
     */
    public function scopeRated($query)
    {
        return $query->whereNotNull('rating');
    }

    /**
     * Scope for getting questions with feedback
     */
    public function scopeWithFeedback($query)
    {
        return $query->whereNotNull('feedback');
    }

    /**
     * Scope for recent questions
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

