@extends('layouts.app')
@section('title', 'Attempts — Muraqib')

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.quizzes.index', $subject) }}">{{ $subject->name }}</a></li>
                <li class="breadcrumb-item active">{{ $quiz->title }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">Attempts</h4>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Total Attempts</div>
                <div class="fs-4 fw-bold">{{ $totalAttempts }}</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Average Score</div>
                <div class="fs-4 fw-bold">{{ $averageScore !== null ? number_format($averageScore, 1) . '%' : '—' }}</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Flagged Attempts</div>
                <div class="fs-4 fw-bold {{ $flaggedCount > 0 ? 'text-danger' : '' }}">{{ $flaggedCount }}</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('teacher.attempts.index', $quiz) }}" class="btn btn-sm {{ !request('filter') ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
        <a href="{{ route('teacher.attempts.index', [$quiz, 'filter' => 'flagged']) }}" class="btn btn-sm {{ request('filter') === 'flagged' ? 'btn-danger' : 'btn-outline-danger' }}">Flagged Only</a>
        <a href="{{ route('teacher.attempts.index', [$quiz, 'filter' => 'in_progress']) }}" class="btn btn-sm {{ request('filter') === 'in_progress' ? 'btn-warning' : 'btn-outline-warning' }}">In Progress</a>
    </div>

    @if($attempts->isEmpty())
        <div class="card"><div class="card-body text-center text-muted py-4">No attempts found.</div></div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Started</th>
                                <th>Submitted</th>
                                <th>Score</th>
                                <th>AC Score</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attempts as $attempt)
                                <tr>
                                    <td class="fw-semibold">{{ $attempt->student->name }}</td>
                                    <td class="small">{{ $attempt->started_at?->format('M d, H:i') }}</td>
                                    <td class="small">{{ $attempt->submitted_at?->format('M d, H:i') ?? 'In Progress' }}</td>
                                    <td>{{ $attempt->score !== null ? number_format($attempt->score, 1) . '%' : '—' }}</td>
                                    <td>{{ $attempt->anticheat_score }}</td>
                                    <td>
                                        @if($attempt->is_flagged)
                                            <span class="badge-flagged">Flagged</span>
                                        @elseif($attempt->submitted_at)
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-warning text-dark">In Progress</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('teacher.attempts.review', $attempt) }}" class="btn btn-outline-primary btn-sm">Review</a>
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
