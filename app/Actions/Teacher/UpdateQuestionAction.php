<?php

namespace App\Actions\Teacher;

use App\Models\Question;

class UpdateQuestionAction
{
    public function execute(Question $question, array $data): Question
    {
        $question->update([
            'question_text' => $data['question_text'],
        ]);

        return $question;
    }
}
