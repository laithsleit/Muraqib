<?php

namespace App\Http\Controllers\Student;

use App\Actions\Student\SubmitAttemptAction;
use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Quiz;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class QuizCheckController extends Controller
{
    public function show(Quiz $quiz)
    {
        $student = Auth::user();
        abort_unless($quiz->is_published, 404);
        abort_unless($quiz->subject->students()->where('student_id', $student->id)->exists(), 403);

        $inProgress = Attempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('started_at')
            ->whereNull('submitted_at')
            ->first();

        if ($inProgress) {
            $endTime = $inProgress->started_at->addMinutes($quiz->duration_minutes);

            if ($endTime->isPast()) {
                // Time expired — submit with whatever answers were auto-saved
                app(SubmitAttemptAction::class)->execute($inProgress, []);
            } else {
                // Still has time — redirect back to the quiz
                return redirect()->route('student.attempts.take', $inProgress);
            }
        }

        $submitted = Attempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->exists();

        if ($submitted) {
            return redirect()->route('student.quizzes.index', $quiz->subject)
                ->with('error', 'You have already completed this quiz.');
        }

        return view('student.quizzes.check', compact('quiz'));
    }

    public function start(Quiz $quiz)
    {
        $student = Auth::user();
        abort_unless($quiz->is_published, 404);
        abort_unless($quiz->subject->students()->where('student_id', $student->id)->exists(), 403);

        // If there's already an in-progress attempt, redirect to it
        $existing = Attempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('started_at')
            ->whereNull('submitted_at')
            ->first();

        if ($existing) {
            return redirect()->route('student.attempts.take', $existing);
        }

        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'started_at' => Carbon::now(),
        ]);

        return redirect()->route('student.attempts.take', $attempt);
    }
}
