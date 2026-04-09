<?php

namespace App\Actions\Teacher;

use App\Models\Subject;

class DeleteSubjectAction
{
    public function execute(Subject $subject): void
    {
        $subject->delete();
    }
}
