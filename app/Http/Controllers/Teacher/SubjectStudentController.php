<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Teacher\EnrollStudentAction;
use App\Actions\Teacher\UnenrollStudentAction;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SubjectStudentController extends Controller
{
    public function index(Subject $subject)
    {
        abort_unless($subject->teacher_id === Auth::id(), 403);

        $enrolled = $subject->students()->orderBy('name')->get();
        $enrolledIds = $enrolled->pluck('id');

        $available = User::students()
            ->active()
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get();

        return view('teacher.subjects.students', compact('subject', 'enrolled', 'available'));
    }

    public function enroll(Subject $subject, User $student, EnrollStudentAction $action)
    {
        abort_unless($subject->teacher_id === Auth::id(), 403);

        $action->execute($subject, $student);

        return back()->with('success', "{$student->name} enrolled.");
    }

    public function unenroll(Subject $subject, User $student, UnenrollStudentAction $action)
    {
        abort_unless($subject->teacher_id === Auth::id(), 403);

        $action->execute($subject, $student);

        return back()->with('success', "{$student->name} removed.");
    }
}
