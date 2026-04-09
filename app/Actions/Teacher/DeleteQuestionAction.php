<?php

namespace App\Actions\Teacher;

use App\Models\Question;

class DeleteQuestionAction
{
    public function execute(Question $question): array
    {
        if ($question->quiz->questions()->count() <= 1) {
            return ['success' => false, 'message' => 'Cannot delete the only question in a quiz.'];
        }

        $question->delete();

        return ['success' => true, 'message' => 'Question deleted.'];
    }
}
