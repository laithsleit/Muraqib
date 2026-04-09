<?php

namespace App\Actions\Teacher;

use App\Models\QuestionOption;

class UpdateOptionAction
{
    public function execute(QuestionOption $option, array $data): QuestionOption
    {
        $isCorrect = $data['is_correct'] ?? false;

        if ($isCorrect) {
            $option->question->options()->where('id', '!=', $option->id)->update(['is_correct' => false]);
        }

        $option->update([
            'option_text' => $data['option_text'],
            'is_correct' => $isCorrect,
        ]);

        return $option;
    }
}
