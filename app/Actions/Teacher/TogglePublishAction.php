<?php

namespace App\Actions\Teacher;

use App\Models\Quiz;

class TogglePublishAction
{
    public function execute(Quiz $quiz): array
    {
        if (! $quiz->is_published) {
            $questions = $quiz->questions()->with('options')->get();

            if ($questions->isEmpty()) {
                return ['success' => false, 'message' => 'Cannot publish a quiz with no questions.'];
            }

            foreach ($questions as $question) {
                if ($question->options->where('is_correct', true)->isEmpty()) {
                    return [
                        'success' => false,
                        'message' => "Question \"{$question->question_text}\" has no correct option. Fix it before publishing.",
                    ];
                }
            }
        }

        $quiz->update(['is_published' => ! $quiz->is_published]);

        return [
            'success' => true,
            'message' => $quiz->is_published ? 'Quiz published.' : 'Quiz unpublished.',
        ];
    }
}
