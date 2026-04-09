<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttemptController extends Controller
{
    public function index(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->subject->teacher_id === Auth::id(), 403);

        $query = $quiz->attempts()->with('student')->latest('started_at');

        if ($request->query('filter') === 'flagged') {
            $query->where('is_flagged', true);
        } elseif ($request->query('filter') === 'in_progress') {
            $query->whereNotNull('started_at')->whereNull('submitted_at');
        }

        $attempts = $query->get();

        $totalAttempts = $quiz->attempts()->count();
        $averageScore = $quiz->attempts()->whereNotNull('submitted_at')->avg('score');
        $flaggedCount = $quiz->attempts()->where('is_flagged', true)->count();

        $subject = $quiz->subject;

        return view('teacher.attempts.index', compact('quiz', 'attempts', 'totalAttempts', 'averageScore', 'flaggedCount', 'subject'));
    }

    public function review(Attempt $attempt)
    {
        abort_unless($attempt->quiz->subject->teacher_id === Auth::id(), 403);

        $attempt->load(['student', 'quiz.subject', 'suspiciousEvents' => fn ($q) => $q->orderBy('occurred_at'), 'answers.selectedOption']);
        $quiz = $attempt->quiz;
        $questions = $quiz->questions()->with('options')->orderBy('order')->get();
        $answers = $attempt->answers->pluck('selected_option_id', 'question_id');
        $events = $attempt->suspiciousEvents;

        return view('teacher.attempts.review', compact('attempt', 'quiz', 'questions', 'answers', 'events'));
    }
}
