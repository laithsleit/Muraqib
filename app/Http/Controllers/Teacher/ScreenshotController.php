<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SuspiciousEvent;
use Illuminate\Support\Facades\Storage;

class ScreenshotController extends Controller
{
    public function show(SuspiciousEvent $event)
    {
        abort_unless($event->attempt->quiz->subject->teacher_id === auth()->id(), 403);

        if (!$event->screenshot_path || !Storage::disk(config('anticheat.screenshot_disk'))->exists($event->screenshot_path)) {
            abort(404);
        }

        return response(
            Storage::disk(config('anticheat.screenshot_disk'))->get($event->screenshot_path),
            200,
            ['Content-Type' => 'image/jpeg']
        );
    }
}
