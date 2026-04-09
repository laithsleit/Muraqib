<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $student = Auth::user();
        $subjectIds = $student->enrolledSubjects()->pluck('subjects.id');

        $enrolledSubjects = $student->enrolledSubjects()
            ->with('teacher')
            ->get();

        $submittedQuizIds = Attempt::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->pluck('quiz_id');

        $availableQuizzes = Quiz::whereIn('subject_id', $subjectIds)
            ->where('is_published', true)
            ->whereNotIn('id', $submittedQuizIds)
            ->count();

        $completedQuizzes = Attempt::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->count();

        foreach ($enrolledSubjects as $subject) {
            $subject->available_quizzes_count = Quiz::where('subject_id', $subject->id)
                ->where('is_published', true)
                ->whereNotIn('id', $submittedQuizIds)
                ->count();
        }

        return view('student.dashboard', compact(
            'enrolledSubjects',
            'availableQuizzes',
            'completedQuizzes',
        ));
    }
}
