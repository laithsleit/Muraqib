@extends('layouts.app')
@section('title', 'Questions — Muraqib')

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.quizzes.index', $subject) }}">{{ $subject->name }}</a></li>
                <li class="breadcrumb-item active">{{ $quiz->title }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">Questions</h4>
        <p class="text-muted small">Manage questions and options for <strong>{{ $quiz->title }}</strong>.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            @if($questions->isEmpty())
                <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No questions yet. Add your first one using the form on the right.</div>
            @endif

            @foreach($questions as $question)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <span class="badge bg-primary me-1">Q{{ $question->order }}</span>
                            {{ $question->question_text }}
                        </span>
                        <div class="d-flex gap-1">
                            <form action="{{ route('teacher.questions.moveUp', $question) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" title="Move Up"><i class="bi bi-chevron-up"></i></button>
                            </form>
                            <form action="{{ route('teacher.questions.moveDown', $question) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" title="Move Down"><i class="bi bi-chevron-down"></i></button>
                            </form>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editQ{{ $question->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('teacher.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Delete this question?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>

                    <div class="collapse" id="editQ{{ $question->id }}">
                        <div class="card-body border-bottom" style="background: var(--surface);">
                            <form action="{{ route('teacher.questions.update', $question) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="input-group">
                                    <input type="text" class="form-control" name="question_text" value="{{ $question->question_text }}" required>
                                    <button class="btn btn-primary btn-sm">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if($question->options->isEmpty())
                            <div class="p-3 text-muted small">No options yet.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($question->options as $option)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($option->is_correct)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-circle text-muted"></i>
                                            @endif
                                            <span style="font-size: 0.9rem;">{{ $option->option_text }}</span>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editOpt{{ $option->id }}" title="Edit"><i class="bi bi-pencil" style="font-size: 0.7rem;"></i></button>
                                            <form action="{{ route('teacher.options.destroy', $option) }}" method="POST" onsubmit="return confirm('Delete this option?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x" style="font-size: 0.7rem;"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="collapse" id="editOpt{{ $option->id }}">
                                        <div class="list-group-item" style="background: var(--surface);">
                                            <form action="{{ route('teacher.options.update', $option) }}" method="POST" class="d-flex gap-2 align-items-center">
                                                @csrf @method('PUT')
                                                <input type="text" class="form-control form-control-sm" name="option_text" value="{{ $option->option_text }}" required>
                                                <div class="form-check form-switch flex-shrink-0">
                                                    <input class="form-check-input" type="checkbox" name="is_correct" value="1" {{ $option->is_correct ? 'checked' : '' }}>
                                                    <label class="form-check-label small">Correct</label>
                                                </div>
                                                <button class="btn btn-primary btn-sm flex-shrink-0">Save</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="p-3 border-top" style="background: var(--surface);">
                            <form action="{{ route('teacher.options.store', $question) }}" method="POST" class="d-flex gap-2 align-items-center">
                                @csrf
                                <input type="text" class="form-control form-control-sm" name="option_text" placeholder="New option text..." required>
                                <div class="form-check form-switch flex-shrink-0">
                                    <input class="form-check-input" type="checkbox" name="is_correct" value="1">
                                    <label class="form-check-label small">Correct</label>
                                </div>
                                <button class="btn btn-primary btn-sm flex-shrink-0">Add</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-lg-4">
            <div class="card" style="position: sticky; top: 80px;">
                <div class="card-header"><i class="bi bi-plus-circle me-1"></i> Add New Question</div>
                <div class="card-body">
                    <form action="{{ route('teacher.questions.store', $quiz) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('question_text') is-invalid @enderror" id="question_text" name="question_text" rows="3" required>{{ old('question_text') }}</textarea>
                            @error('question_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Question</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
