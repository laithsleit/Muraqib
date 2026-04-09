<?php

namespace App\Http\Controllers\Teacher;

use App\Actions\Teacher\CreateSubjectAction;
use App\Actions\Teacher\DeleteSubjectAction;
use App\Actions\Teacher\UpdateSubjectAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Auth::user()->subjects()
            ->withCount(['students', 'quizzes'])
            ->latest()
            ->get();

        return view('teacher.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('teacher.subjects.create');
    }

    public function store(StoreSubjectRequest $request, CreateSubjectAction $action)
    {
        $action->execute($request->validated(), Auth::user());

        return redirect()->route('teacher.subjects.index')
            ->with('success', 'Subject created.');
    }

    public function edit(Subject $subject)
    {
        $this->authorizeTeacher($subject);

        return view('teacher.subjects.edit', compact('subject'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject, UpdateSubjectAction $action)
    {
        $this->authorizeTeacher($subject);
        $action->execute($subject, $request->validated());

        return redirect()->route('teacher.subjects.index')
            ->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject, DeleteSubjectAction $action)
    {
        $this->authorizeTeacher($subject);
        $action->execute($subject);

        return redirect()->route('teacher.subjects.index')
            ->with('success', 'Subject deleted.');
    }

    private function authorizeTeacher(Subject $subject): void
    {
        abort_unless($subject->teacher_id === Auth::id(), 403);
    }
}
