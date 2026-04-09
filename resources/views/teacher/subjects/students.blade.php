@extends('layouts.app')
@section('title', 'Manage Students — Muraqib')

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item active">{{ $subject->name }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold">Manage Students</h4>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-people me-1"></i> Enrolled Students
                    <span class="badge rounded-pill bg-primary ms-1">{{ $enrolled->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($enrolled->isEmpty())
                        <div class="p-4 text-center text-muted">No students enrolled.</div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($enrolled as $student)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold" style="font-size: 0.9rem;">{{ $student->name }}</div>
                                        <div class="text-muted small">{{ $student->email }}</div>
                                    </div>
                                    <form action="{{ route('teacher.subjects.students.unenroll', [$subject, $student]) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-outline-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-person-plus me-1"></i> Add Students
                    <span class="badge rounded-pill bg-secondary ms-1">{{ $available->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($available->isEmpty())
                        <div class="p-4 text-center text-muted">All students are enrolled.</div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($available as $student)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold" style="font-size: 0.9rem;">{{ $student->name }}</div>
                                        <div class="text-muted small">{{ $student->email }}</div>
                                    </div>
                                    <form action="{{ route('teacher.subjects.students.enroll', [$subject, $student]) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-primary btn-sm">Add</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
