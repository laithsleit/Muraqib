<?php

namespace App\Actions\Teacher;

use App\Models\Question;

class MoveQuestionUpAction
{
    public function execute(Question $question): void
    {
        $previous = Question::where('quiz_id', $question->quiz_id)
            ->where('order', '<', $question->order)
            ->orderByDesc('order')
            ->first();

        if ($previous) {
            $oldOrder = $question->order;
            $question->update(['order' => $previous->order]);
            $previous->update(['order' => $oldOrder]);
        }
    }
}
