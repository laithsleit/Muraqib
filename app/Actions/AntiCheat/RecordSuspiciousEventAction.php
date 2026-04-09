<?php

namespace App\Actions\AntiCheat;

use App\Models\Attempt;
use App\Models\SuspiciousEvent;

class RecordSuspiciousEventAction
{
    public function execute(Attempt $attempt, array $data): array
    {
        $eventType = $data['event_type'];
        $points = config('anticheat.event_points')[$eventType];

        SuspiciousEvent::create([
            'attempt_id' => $attempt->id,
            'event_type' => $eventType,
            'points' => $points,
            'screenshot' => $data['screenshot'] ?? null,
            'occurred_at' => $data['occurred_at'],
        ]);

        $attempt->increment('anticheat_score', $points);
        $attempt->refresh();
        $attempt->checkAndFlag();

        return [
            'success' => true,
            'flagged' => $attempt->is_flagged,
            'anticheat_score' => $attempt->anticheat_score,
        ];
    }
}
