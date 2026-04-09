<?php

namespace App\Actions\Teacher;

use App\Models\Quiz;

class DeleteQuizAction
{
    public function execute(Quiz $quiz): array
    {
        if ($quiz->attempts()->whereNotNull('submitted_at')->exists()) {
            return ['success' => false, 'message' => 'Cannot delete a quiz that has submitted attempts.'];
        }

        $quiz->delete();

        return ['success' => true, 'message' => 'Quiz deleted.'];
    }
}
