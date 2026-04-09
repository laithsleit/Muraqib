<?php

namespace App\Actions\Teacher;

use App\Models\Subject;
use App\Models\User;

class UnenrollStudentAction
{
    public function execute(Subject $subject, User $student): void
    {
        $subject->students()->detach($student->id);
    }
}
