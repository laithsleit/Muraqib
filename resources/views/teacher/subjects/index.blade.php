@extends('layouts.app')
@section('title', 'Subjects — Muraqib')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Subjects</h4>
        <a href="{{ route('teacher.subjects.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New Subject</a>
    </div>

    @if($subjects->isEmpty())
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No subjects yet. Create your first one.</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Students</th>
                                <th>Quizzes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                                <tr>
                                    <td class="fw-semibold">{{ $subject->name }}</td>
                                    <td class="text-muted">{{ Str::limit($subject->description, 60) ?: '—' }}</td>
                                    <td>{{ $subject->students_count }}</td>
                                    <td>{{ $subject->quizzes_count }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('teacher.quizzes.index', $subject) }}" class="btn btn-outline-primary btn-sm">Quizzes</a>
                                            <a href="{{ route('teacher.subjects.students', $subject) }}" class="btn btn-outline-secondary btn-sm">Students</a>
                                            <a href="{{ route('teacher.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                            <form action="{{ route('teacher.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Delete this subject and all its quizzes?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
