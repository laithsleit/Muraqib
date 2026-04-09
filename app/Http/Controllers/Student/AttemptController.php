<?php

namespace App\Http\Controllers\Student;

use App\Actions\Student\SubmitAttemptAction;
use App\Http\Controllers\Controller;
use App\Models\Attempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttemptController extends Controller
{
    public function take(Attempt $attempt)
    {
        abort_unless($attempt->student_id === Auth::id(), 403);
        abort_unless($attempt->isInProgress(), 404);

        $quiz = $attempt->quiz;
        $questions = $quiz->questions()->with('options')->orderBy('order')->get();
        $existingAnswers = $attempt->answers()->pluck('selected_option_id', 'question_id');
        $endTime = $attempt->started_at->addMinutes($quiz->duration_minutes);

        return view('student.attempts.take', compact('attempt', 'quiz', 'questions', 'existingAnswers', 'endTime'));
    }

    public function saveAnswer(Request $request, Attempt $attempt)
    {
        abort_unless($attempt->student_id === Auth::id(), 403);
        abort_unless($attempt->isInProgress(), 404);

        $request->validate([
            'question_id' => 'required|integer',
            'option_id' => 'required|integer',
        ]);

        \App\Models\AttemptAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $request->question_id],
            ['selected_option_id' => $request->option_id],
        );

        return response()->json(['saved' => true]);
    }

    public function submit(Request $request, Attempt $attempt, SubmitAttemptAction $action)
    {
        abort_unless($attempt->student_id === Auth::id(), 403);
        abort_unless($attempt->isInProgress(), 404);

        $answers = $request->input('answers', []);
        $action->execute($attempt, $answers);

        return redirect()->route('student.attempts.results', $attempt)
            ->with('success', 'Quiz submitted successfully!');
    }

    public function results(Attempt $attempt)
    {
        abort_unless($attempt->student_id === Auth::id(), 403);
        abort_unless($attempt->submitted_at !== null, 404);

        $quiz = $attempt->quiz;
        $questions = $quiz->questions()->with('options')->orderBy('order')->get();
        $answers = $attempt->answers()->pluck('selected_option_id', 'question_id');

        return view('student.attempts.results', compact('attempt', 'quiz', 'questions', 'answers'));
    }
}
