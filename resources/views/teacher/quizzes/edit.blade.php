@extends('layouts.app')
@section('title', 'Edit Quiz — Muraqib')

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.quizzes.index', $subject) }}">{{ $subject->name }}</a></li>
                <li class="breadcrumb-item active">Edit Quiz</li>
            </ol>
        </nav>
        <h4 class="fw-bold">Edit Quiz</h4>
    </div>

    <form action="{{ route('teacher.quizzes.update', [$subject, $quiz]) }}" method="POST">
        @csrf @method('PUT')

        <div class="card mb-4">
            <div class="card-header">Quiz Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $quiz->title) }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $quiz->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="duration_minutes" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $quiz->duration_minutes) }}" min="1" max="300" required>
                        @error('duration_minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $quiz->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Published</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" style="background: var(--primary-light); border-color: var(--primary);">
            <div class="card-header" style="background: var(--primary-light);">
                <i class="bi bi-shield-check me-1"></i> Anti-Cheat Configuration
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Configure how suspicious behaviour is scored and when an attempt gets flagged.</p>

                <div class="mb-4">
                    <label for="score_threshold" class="form-label">Score Threshold <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('score_threshold') is-invalid @enderror" id="score_threshold" name="score_threshold" value="{{ old('score_threshold', $quiz->score_threshold) }}" placeholder="{{ config('anticheat.default_threshold') }}" min="1" required style="max-width: 200px;">
                    <div class="form-text">An attempt will be flagged for review once a student's total suspicious behaviour score reaches this number.</div>
                    @error('score_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <h6 class="fw-semibold mb-2" style="font-size: 0.85rem;">Event Reference</h6>
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="background: var(--card-bg); border-radius: var(--radius-sm); overflow: hidden;">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(config('anticheat.event_points') as $key => $points)
                                <tr>
                                    <td>{{ config('anticheat.event_labels')[$key] ?? $key }}</td>
                                    <td><span class="fw-semibold">{{ $points }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update Quiz</button>
            <a href="{{ route('teacher.quizzes.index', $subject) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection
