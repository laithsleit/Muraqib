@extends('layouts.app')
@section('title', 'Student Dashboard — Muraqib')

@section('content')
    <h4 class="fw-bold mb-1">Welcome back, {{ auth()->user()->name }}</h4>
    <p class="text-muted mb-4">Here's a quick overview of your quizzes and subjects.</p>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Enrolled Subjects</div>
                <div class="fs-4 fw-bold">{{ $enrolledSubjects->count() }}</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Available Quizzes</div>
                <div class="fs-4 fw-bold text-primary">{{ $availableQuizzes }}</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Completed Quizzes</div>
                <div class="fs-4 fw-bold text-success">{{ $completedQuizzes }}</div>
            </div>
        </div>
    </div>

    {{-- Enrolled Subjects --}}
    <h5 class="fw-bold mb-3">Your Subjects</h5>

    @if($enrolledSubjects->isEmpty())
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>You are not enrolled in any subjects yet. Contact your teacher.</div>
    @else
        <div class="row g-3">
            @foreach($enrolledSubjects as $subject)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-1">{{ $subject->name }}</h6>
                            <p class="text-muted small mb-3">{{ $subject->teacher->name }}</p>
                            <span class="badge rounded-pill" style="background: var(--primary-light); color: var(--primary); font-size: 0.75rem;">
                                {{ $subject->available_quizzes_count }} quiz{{ $subject->available_quizzes_count !== 1 ? 'zes' : '' }} available
                            </span>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <a href="{{ route('student.quizzes.index', $subject) }}" class="btn btn-primary btn-sm w-100">View Quizzes</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
