@extends('layouts.app')
@section('title', 'Teacher Dashboard — Muraqib')

@section('content')
    <h4 class="fw-bold mb-1">Welcome back, {{ auth()->user()->name }}</h4>
    <p class="text-muted mb-4">Here's an overview of your subjects and quizzes.</p>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Total Subjects</div>
                <div class="fs-4 fw-bold">{{ $totalSubjects }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Total Quizzes</div>
                <div class="fs-4 fw-bold">{{ $totalQuizzes }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Published Quizzes</div>
                <div class="fs-4 fw-bold text-success">{{ $publishedQuizzes }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Flagged Attempts</div>
                <div class="fs-4 fw-bold {{ $flaggedAttempts > 0 ? 'text-danger' : '' }}">{{ $flaggedAttempts }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Your Subjects</span>
            <a href="{{ route('teacher.subjects.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New Subject</a>
        </div>
        <div class="card-body p-0">
            @if($subjects->isEmpty())
                <div class="p-4 text-center text-muted">No subjects yet. <a href="{{ route('teacher.subjects.create') }}">Create your first one.</a></div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Subject Name</th>
                                <th>Students Enrolled</th>
                                <th>Quizzes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                                <tr>
                                    <td class="fw-semibold">{{ $subject->name }}</td>
                                    <td>{{ $subject->students_count }}</td>
                                    <td>{{ $subject->quizzes_count }}</td>
                                    <td>
                                        <a href="{{ route('teacher.subjects.students', $subject) }}" class="btn btn-outline-secondary btn-sm me-1">Students</a>
                                        <a href="{{ route('teacher.quizzes.index', $subject) }}" class="btn btn-outline-primary btn-sm">Quizzes</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
