<?php

namespace App\Actions\Teacher;

use App\Models\Question;
use App\Models\Quiz;

class AddQuestionAction
{
    public function execute(Quiz $quiz, array $data): Question
    {
        $maxOrder = $quiz->questions()->max('order') ?? 0;

        return Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => $data['question_text'],
            'order' => $maxOrder + 1,
        ]);
    }
}
