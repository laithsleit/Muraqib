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

        // Available quizzes: published, in enrolled subjects, where student has no submitted attempt OR retake allowed
        $submittedQuizIds = Attempt::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->pluck('quiz_id');

        $availableQuizzes = Quiz::whereIn('subject_id', $subjectIds)
            ->where('is_published', true)
            ->where(function ($q) use ($submittedQuizIds) {
                $q->whereNotIn('id', $submittedQuizIds)
                    ->orWhere('allow_retake', true);
            })
            ->count();

        $completedQuizzes = Attempt::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->count();

        // Count available quizzes per subject for display
        foreach ($enrolledSubjects as $subject) {
            $subject->available_quizzes_count = Quiz::where('subject_id', $subject->id)
                ->where('is_published', true)
                ->where(function ($q) use ($submittedQuizIds) {
                    $q->whereNotIn('id', $submittedQuizIds)
                        ->orWhere('allow_retake', true);
                })
                ->count();
        }

        return view('student.dashboard', compact(
            'enrolledSubjects',
            'availableQuizzes',
            'completedQuizzes',
        ));
    }
}
