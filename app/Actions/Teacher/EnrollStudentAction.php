<?php

namespace App\Actions\Teacher;

use App\Models\Subject;
use App\Models\User;

class EnrollStudentAction
{
    public function execute(Subject $subject, User $student): void
    {
        if (! $subject->students()->where('student_id', $student->id)->exists()) {
            $subject->students()->attach($student->id);
        }
    }
}
