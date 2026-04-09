<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'student_id',
        'started_at',
        'submitted_at',
        'score',
        'anticheat_score',
        'is_flagged',
        'flag_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function suspiciousEvents(): HasMany
    {
        return $this->hasMany(SuspiciousEvent::class);
    }

    public function isInProgress(): bool
    {
        return $this->started_at !== null && $this->submitted_at === null;
    }

    public function checkAndFlag(): void
    {
        if ($this->anticheat_score >= $this->quiz->score_threshold) {
            $this->is_flagged = true;
            $this->flag_reason = "Anti-cheat score ({$this->anticheat_score}) exceeded threshold ({$this->quiz->score_threshold})";
            $this->save();
        }
    }
}
