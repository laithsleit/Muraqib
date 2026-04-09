<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $teacher = Auth::user();
        $subjectIds = $teacher->subjects()->pluck('id');

        $totalSubjects = $subjectIds->count();
        $totalQuizzes = Quiz::whereIn('subject_id', $subjectIds)->count();
        $publishedQuizzes = Quiz::whereIn('subject_id', $subjectIds)->where('is_published', true)->count();

        $quizIds = Quiz::whereIn('subject_id', $subjectIds)->pluck('id');
        $flaggedAttempts = Attempt::whereIn('quiz_id', $quizIds)->where('is_flagged', true)->count();

        $subjects = $teacher->subjects()
            ->withCount(['students', 'quizzes'])
            ->get();

        return view('teacher.dashboard', compact(
            'totalSubjects',
            'totalQuizzes',
            'publishedQuizzes',
            'flaggedAttempts',
            'subjects',
        ));
    }
}
