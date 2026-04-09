@extends('layouts.app')
@section('title', 'Quiz Results — Muraqib')

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('student.subjects.index') }}">My Subjects</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.quizzes.index', $quiz->subject) }}">{{ $quiz->subject->name }}</a></li>
                <li class="breadcrumb-item active">Results</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">{{ $quiz->title }} — Results</h4>
    </div>

    <div class="card mb-4">
        <div class="card-body text-center py-4">
            <div class="mb-2">
                <span class="fs-1 fw-bold {{ $attempt->score >= 60 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($attempt->score, 1) }}%
                </span>
            </div>
            <div class="progress mx-auto mb-3" style="height: 10px; max-width: 400px;">
                <div class="progress-bar {{ $attempt->score >= 60 ? 'bg-success' : 'bg-danger' }}"
                     role="progressbar"
                     style="width: {{ $attempt->score }}%"></div>
            </div>
            <div>
                @if($attempt->score >= 60)
                    <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i> Passed</span>
                @else
                    <span class="badge bg-danger fs-6"><i class="bi bi-x-circle me-1"></i> Failed</span>
                @endif
            </div>
        </div>
    </div>

    @if($attempt->is_flagged)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>This attempt has been flagged for review by your teacher.</strong>
            @if($attempt->flag_reason)
                <br><span class="small text-muted">{{ $attempt->flag_reason }}</span>
            @endif
        </div>
    @endif

    <h5 class="fw-bold mb-3">Question Breakdown</h5>

    @foreach($questions as $index => $question)
        @php
            $selectedId = $answers[$question->id] ?? null;
            $correctOption = $question->options->firstWhere('is_correct', true);
            $isCorrect = $selectedId && $correctOption && $selectedId == $correctOption->id;
        @endphp
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <span class="badge {{ $isCorrect ? 'bg-success' : 'bg-danger' }} me-1">Q{{ $index + 1 }}</span>
                    {{ $question->question_text }}
                </span>
                @if($isCorrect)
                    <i class="bi bi-check-circle-fill text-success"></i>
                @else
                    <i class="bi bi-x-circle-fill text-danger"></i>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($question->options as $option)
                        @php
                            $isSelected = $selectedId == $option->id;
                            $isCorrectOption = $option->is_correct;
                            $bgClass = '';
                            if ($isSelected && $isCorrectOption) $bgClass = 'list-group-item-success';
                            elseif ($isSelected && !$isCorrectOption) $bgClass = 'list-group-item-danger';
                            elseif ($isCorrectOption) $bgClass = 'list-group-item-success';
                        @endphp
                        <div class="list-group-item {{ $bgClass }} d-flex align-items-center gap-2 py-2">
                            @if($isCorrectOption)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @elseif($isSelected)
                                <i class="bi bi-x-circle-fill text-danger"></i>
                            @else
                                <i class="bi bi-circle text-muted"></i>
                            @endif
                            <span style="font-size: 0.9rem;">{{ $option->option_text }}</span>
                            @if($isSelected)
                                <span class="badge bg-dark ms-auto" style="font-size: 0.65rem;">Your answer</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="mt-4">
        <a href="{{ route('student.quizzes.index', $quiz->subject) }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Subject
        </a>
    </div>
@endsection
