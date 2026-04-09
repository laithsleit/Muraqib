<?php

namespace App\Actions\Teacher;

use App\Models\Quiz;
use App\Models\Subject;

class CreateQuizAction
{
    public function execute(Subject $subject, array $data): Quiz
    {
        return Quiz::create([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'allow_retake' => $data['allow_retake'] ?? false,
            'is_published' => $data['is_published'] ?? false,
            'score_threshold' => $data['score_threshold'],
        ]);
    }
}
