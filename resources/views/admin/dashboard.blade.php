@extends('layouts.app')
@section('title', 'Admin Dashboard — Muraqib')

@section('content')
    <h4 class="fw-bold mb-1">Admin Dashboard</h4>
    <p class="text-muted mb-4">Platform overview and quick links.</p>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Teachers</div>
                <div class="fs-4 fw-bold">{{ $totalTeachers }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Students</div>
                <div class="fs-4 fw-bold">{{ $totalStudents }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Subjects</div>
                <div class="fs-4 fw-bold">{{ $totalSubjects }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Quizzes</div>
                <div class="fs-4 fw-bold">{{ $totalQuizzes }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg">
            <div class="stat-card p-3">
                <div class="text-muted small mb-1">Flagged Attempts</div>
                <div class="fs-4 fw-bold {{ $totalFlagged > 0 ? 'text-danger' : '' }}">{{ $totalFlagged }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center py-4">
                    <i class="bi bi-person-workspace fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="fw-bold">Manage Teachers</h6>
                    <a href="{{ route('admin.users.index', ['filter' => 'teachers']) }}" class="btn btn-outline-primary btn-sm mt-2">View Teachers</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center py-4">
                    <i class="bi bi-mortarboard fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="fw-bold">Manage Students</h6>
                    <a href="{{ route('admin.users.index', ['filter' => 'students']) }}" class="btn btn-outline-primary btn-sm mt-2">View Students</a>
                </div>
            </div>
        </div>
    </div>
@endsection
