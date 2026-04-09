<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'duration_minutes',
        'is_published',
        'score_threshold',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getFlaggedAttemptsCountAttribute(): int
    {
        return $this->attempts()->where('is_flagged', true)->count();
    }
}
