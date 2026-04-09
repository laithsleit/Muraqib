<?php

namespace App\Actions\Teacher;

use App\Models\Quiz;

class UpdateQuizAction
{
    public function execute(Quiz $quiz, array $data): Quiz
    {
        $quiz->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'is_published' => $data['is_published'] ?? false,
            'score_threshold' => $data['score_threshold'],
        ]);

        return $quiz;
    }
}
