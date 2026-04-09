<?php

namespace App\Actions\Teacher;

use App\Models\QuestionOption;

class DeleteOptionAction
{
    public function execute(QuestionOption $option): void
    {
        $option->delete();
    }
}
