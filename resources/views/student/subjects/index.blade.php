@extends('layouts.app')
@section('title', 'My Subjects — Muraqib')

@section('content')
    <h4 class="fw-bold mb-4">My Subjects</h4>

    @if($subjects->isEmpty())
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>You are not enrolled in any subjects yet. Contact your teacher.</div>
    @else
        <div class="row g-3">
            @foreach($subjects as $subject)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="fw-bold mb-1">{{ $subject->name }}</h6>
                            <p class="text-muted small mb-2">{{ $subject->teacher->name }}</p>
                            @if($subject->description)
                                <p class="text-muted small mb-0">{{ Str::limit($subject->description, 80) }}</p>
                            @endif
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
