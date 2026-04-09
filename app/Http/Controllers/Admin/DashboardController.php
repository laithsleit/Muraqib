<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $totalTeachers = User::teachers()->count();
        $totalStudents = User::students()->count();
        $totalSubjects = Subject::count();
        $totalQuizzes = Quiz::count();
        $totalFlagged = Attempt::where('is_flagged', true)->count();

        return view('admin.dashboard', compact(
            'totalTeachers',
            'totalStudents',
            'totalSubjects',
            'totalQuizzes',
            'totalFlagged',
        ));
    }
}
