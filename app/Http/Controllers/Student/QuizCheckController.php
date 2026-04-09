<?php

namespace App\Http\Controllers\Student;

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

        // If in-progress attempt exists, go straight to quiz
        $inProgress = Attempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('started_at')
            ->whereNull('submitted_at')
            ->first();

        if ($inProgress) {
            return redirect()->route('student.attempts.take', $inProgress);
        }

        // If submitted and no retake allowed, block
        $submitted = Attempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->exists();

        if ($submitted && ! $quiz->allow_retake) {
            return redirect()->route('student.quizzes.index', $quiz->subject)
                ->with('error', 'You have already completed this quiz and retakes are not allowed.');
        }

        // Create a new attempt
        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'started_at' => Carbon::now(),
        ]);

        return view('student.quizzes.check', compact('quiz', 'attempt'));
    }

    public function start(Quiz $quiz)
    {
        $student = Auth::user();

        $attempt = Attempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('started_at')
            ->whereNull('submitted_at')
            ->firstOrFail();

        return redirect()->route('student.attempts.take', $attempt);
    }
}
