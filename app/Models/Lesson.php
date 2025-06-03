<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'subject',
        'grade_level',
        'summary',
        'tags',
        'view_count',
        'created_by'
    ];

    protected $casts = [
        'tags' => 'array',
        'view_count' => 'integer'
    ];

    /**
     * Get the user who created this lesson
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all questions for this lesson
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Scope for filtering by subject
     */
    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    /**
     * Scope for filtering by grade level
     */
    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * Scope for searching lessons
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%")
              ->orWhere('subject', 'like', "%{$term}%");
        });
    }

    /**
     * Get popular lessons
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->orderByDesc('view_count')
                    ->withCount('questions')
                    ->orderByDesc('questions_count')
                    ->limit($limit);
    }

    /**
     * Get recent lessons
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }
}

