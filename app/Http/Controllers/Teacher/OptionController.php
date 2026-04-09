<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Teacher\AddOptionAction;
use App\Actions\Teacher\DeleteOptionAction;
use App\Actions\Teacher\UpdateOptionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Auth;

class OptionController extends Controller
{
    public function store(StoreOptionRequest $request, Question $question, AddOptionAction $action)
    {
        abort_unless($question->quiz->subject->teacher_id === Auth::id(), 403);

        $action->execute($question, $request->validated());

        return back()->with('success', 'Option added.');
    }

    public function update(UpdateOptionRequest $request, QuestionOption $option, UpdateOptionAction $action)
    {
        abort_unless($option->question->quiz->subject->teacher_id === Auth::id(), 403);

        $action->execute($option, $request->validated());

        return back()->with('success', 'Option updated.');
    }

    public function destroy(QuestionOption $option, DeleteOptionAction $action)
    {
        abort_unless($option->question->quiz->subject->teacher_id === Auth::id(), 403);

        $action->execute($option);

        return back()->with('success', 'Option deleted.');
    }
}
