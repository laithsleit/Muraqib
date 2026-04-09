<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Teacher\AddQuestionAction;
use App\Actions\Teacher\DeleteQuestionAction;
use App\Actions\Teacher\MoveQuestionDownAction;
use App\Actions\Teacher\MoveQuestionUpAction;
use App\Actions\Teacher\UpdateQuestionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function index(Quiz $quiz)
    {
        $this->authorizeTeacher($quiz);

        $questions = $quiz->questions()->with('options')->orderBy('order')->get();
        $subject = $quiz->subject;

        return view('teacher.questions.index', compact('quiz', 'questions', 'subject'));
    }

    public function store(StoreQuestionRequest $request, Quiz $quiz, AddQuestionAction $action)
    {
        $this->authorizeTeacher($quiz);
        $action->execute($quiz, $request->validated());

        return back()->with('success', 'Question added.');
    }

    public function update(Request $request, Question $question, UpdateQuestionAction $action)
    {
        $this->authorizeTeacher($question->quiz);

        $data = $request->validate(['question_text' => ['required', 'string']]);
        $action->execute($question, $data);

        return back()->with('success', 'Question updated.');
    }

    public function destroy(Question $question, DeleteQuestionAction $action)
    {
        $this->authorizeTeacher($question->quiz);
        $result = $action->execute($question);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function moveUp(Question $question, MoveQuestionUpAction $action)
    {
        $this->authorizeTeacher($question->quiz);
        $action->execute($question);

        return back();
    }

    public function moveDown(Question $question, MoveQuestionDownAction $action)
    {
        $this->authorizeTeacher($question->quiz);
        $action->execute($question);

        return back();
    }

    private function authorizeTeacher(Quiz $quiz): void
    {
        abort_unless($quiz->subject->teacher_id === Auth::id(), 403);
    }
}
