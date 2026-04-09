<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuspiciousEvent extends Model
{
    protected $fillable = [
        'attempt_id',
        'event_type',
        'points',
        'screenshot',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function getEventLabelAttribute(): string
    {
        return config('anticheat.event_labels')[$this->event_type] ?? $this->event_type;
    }
}
