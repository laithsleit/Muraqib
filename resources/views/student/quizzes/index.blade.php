@extends('layouts.app')
@section('title', 'Quizzes — Muraqib')

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('student.subjects.index') }}">My Subjects</a></li>
                <li class="breadcrumb-item active">{{ $subject->name }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">Quizzes</h4>
    </div>

    @if($quizzes->isEmpty())
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No quizzes available for this subject yet.</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Quiz Title</th>
                                <th>Duration</th>
                                <th>Questions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quizzes as $quiz)
                                @php
                                    $quizAttempts = $attempts[$quiz->id] ?? collect();
                                    $inProgress = $quizAttempts->first(fn($a) => $a->started_at && !$a->submitted_at);
                                    $submitted = $quizAttempts->first(fn($a) => $a->submitted_at !== null);
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $quiz->title }}</td>
                                    <td>{{ $quiz->duration_minutes }} min</td>
                                    <td>{{ $quiz->questions_count }}</td>
                                    <td>
                                        @if($inProgress)
                                            <span class="badge bg-warning text-dark">In Progress</span>
                                        @elseif($submitted && $submitted->is_flagged)
                                            <span class="badge-flagged">Completed (Flagged)</span>
                                        @elseif($submitted)
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-secondary">Not Started</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($inProgress)
                                                <a href="{{ route('student.attempts.take', $inProgress) }}" class="btn btn-warning btn-sm">Resume Quiz</a>
                                            @elseif($submitted)
                                                <a href="{{ route('student.attempts.results', $submitted) }}" class="btn btn-outline-primary btn-sm">View Results</a>
                                            @else
                                                <a href="{{ route('student.quizzes.check', $quiz) }}" class="btn btn-primary btn-sm">Start Quiz</a>
                                            @endif
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
