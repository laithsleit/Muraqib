<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class SubjectQuizController extends Controller
{
    public function index(Subject $subject)
    {
        $student = Auth::user();
        abort_unless($subject->students()->where('student_id', $student->id)->exists(), 403);

        $quizzes = $subject->quizzes()
            ->where('is_published', true)
            ->withCount('questions')
            ->get();

        // Eager load latest attempt per quiz for this student
        $quizIds = $quizzes->pluck('id');
        $attempts = Attempt::where('student_id', $student->id)
            ->whereIn('quiz_id', $quizIds)
            ->get()
            ->groupBy('quiz_id');

        return view('student.quizzes.index', compact('subject', 'quizzes', 'attempts'));
    }
}
