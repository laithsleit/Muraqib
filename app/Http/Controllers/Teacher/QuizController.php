<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Teacher\CreateQuizAction;
use App\Actions\Teacher\DeleteQuizAction;
use App\Actions\Teacher\TogglePublishAction;
use App\Actions\Teacher\UpdateQuizAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function index(Subject $subject)
    {
        $this->authorizeTeacher($subject);

        $quizzes = $subject->quizzes()
            ->withCount([
                'questions',
                'attempts as total_attempts_count',
                'attempts as flagged_attempts_count' => fn ($q) => $q->where('is_flagged', true),
            ])
            ->latest()
            ->get();

        $studentsCount = $subject->students()->count();

        return view('teacher.quizzes.index', compact('subject', 'quizzes', 'studentsCount'));
    }

    public function create(Subject $subject)
    {
        $this->authorizeTeacher($subject);

        return view('teacher.quizzes.create', compact('subject'));
    }

    public function store(StoreQuizRequest $request, Subject $subject, CreateQuizAction $action)
    {
        $this->authorizeTeacher($subject);
        $action->execute($subject, $request->validated());

        return redirect()->route('teacher.quizzes.index', $subject)
            ->with('success', 'Quiz created.');
    }

    public function edit(Subject $subject, Quiz $quiz)
    {
        $this->authorizeTeacher($subject);

        return view('teacher.quizzes.edit', compact('subject', 'quiz'));
    }

    public function update(UpdateQuizRequest $request, Subject $subject, Quiz $quiz, UpdateQuizAction $action)
    {
        $this->authorizeTeacher($subject);
        $action->execute($quiz, $request->validated());

        return redirect()->route('teacher.quizzes.index', $subject)
            ->with('success', 'Quiz updated.');
    }

    public function togglePublish(Subject $subject, Quiz $quiz, TogglePublishAction $action)
    {
        $this->authorizeTeacher($subject);
        $result = $action->execute($quiz);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function destroy(Subject $subject, Quiz $quiz, DeleteQuizAction $action)
    {
        $this->authorizeTeacher($subject);
        $result = $action->execute($quiz);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function authorizeTeacher(Subject $subject): void
    {
        abort_unless($subject->teacher_id === Auth::id(), 403);
    }
}
