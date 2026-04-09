<?php

namespace App\Actions\Teacher;

use App\Models\Subject;

class UpdateSubjectAction
{
    public function execute(Subject $subject, array $data): Subject
    {
        $subject->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $subject;
    }
}
