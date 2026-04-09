<?php

namespace App\Actions\Teacher;

use App\Models\Subject;
use App\Models\User;

class CreateSubjectAction
{
    public function execute(array $data, User $teacher): Subject
    {
        return Subject::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'teacher_id' => $teacher->id,
        ]);
    }
}
