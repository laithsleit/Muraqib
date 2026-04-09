<?php

namespace App\Actions\Teacher;

use App\Models\Question;
use App\Models\QuestionOption;

class AddOptionAction
{
    public function execute(Question $question, array $data): QuestionOption
    {
        $isCorrect = $data['is_correct'] ?? false;

        if ($isCorrect) {
            $question->options()->update(['is_correct' => false]);
        }

        return QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => $data['option_text'],
            'is_correct' => $isCorrect,
        ]);
    }
}
