<?php

namespace App\Actions\Teacher;

use App\Models\Question;

class MoveQuestionDownAction
{
    public function execute(Question $question): void
    {
        $next = Question::where('quiz_id', $question->quiz_id)
            ->where('order', '>', $question->order)
            ->orderBy('order')
            ->first();

        if ($next) {
            $oldOrder = $question->order;
            $question->update(['order' => $next->order]);
            $next->update(['order' => $oldOrder]);
        }
    }
}
