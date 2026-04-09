<?php

namespace App\Actions\Student;

use App\Models\Attempt;
use App\Models\AttemptAnswer;
use Carbon\Carbon;

class SubmitAttemptAction
{
    public function execute(Attempt $attempt, array $answers): Attempt
    {
        $quiz = $attempt->quiz;
        $questions = $quiz->questions()->with('options')->get();
        $totalQuestions = $questions->count();
        $correctCount = 0;

        foreach ($questions as $question) {
            $selectedOptionId = $answers[$question->id] ?? null;

            AttemptAnswer::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ],
                [
                    'selected_option_id' => $selectedOptionId,
                ]
            );

            if ($selectedOptionId) {
                $correctOption = $question->options->where('is_correct', true)->first();
                if ($correctOption && $correctOption->id == $selectedOptionId) {
                    $correctCount++;
                }
            }
        }

        $score = $totalQuestions > 0 ? ($correctCount / $totalQuestions) * 100 : 0;

        $attempt->update([
            'score' => round($score, 2),
            'submitted_at' => Carbon::now(),
        ]);

        $attempt->checkAndFlag();

        return $attempt;
    }
}
