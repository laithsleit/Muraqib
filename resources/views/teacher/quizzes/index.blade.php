@extends('layouts.app')
@section('title', 'Quizzes — Muraqib')

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item active">{{ $subject->name }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0">Quizzes</h4>
            <a href="{{ route('teacher.quizzes.create', $subject) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New Quiz</a>
        </div>
    </div>

    @if($quizzes->isEmpty())
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No quizzes yet. Create your first one.</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Duration</th>
                                <th>Questions</th>
                                <th>Threshold</th>
                                <th>Status</th>
                                <th>Flagged</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quizzes as $quiz)
                                <tr>
                                    <td class="fw-semibold">{{ $quiz->title }}</td>
                                    <td>{{ $quiz->duration_minutes }} min</td>
                                    <td>{{ $quiz->questions_count }}</td>
                                    <td>{{ $quiz->score_threshold }}</td>
                                    <td>
                                        @if($quiz->is_published)
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($quiz->attempts_count > 0)
                                            <span class="badge-flagged">{{ $quiz->attempts_count }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('teacher.questions.index', $quiz) }}" class="btn btn-outline-primary btn-sm">Questions</a>
                                            <a href="{{ route('teacher.quizzes.edit', [$subject, $quiz]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                            <form action="{{ route('teacher.quizzes.togglePublish', [$subject, $quiz]) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-secondary" title="{{ $quiz->is_published ? 'Unpublish' : 'Publish' }}">
                                                    <i class="bi bi-{{ $quiz->is_published ? 'eye-slash' : 'eye' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('teacher.quizzes.destroy', [$subject, $quiz]) }}" method="POST" onsubmit="return confirm('Delete this quiz?')">
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
