@extends('layouts.app')
@section('title', 'Review Attempt — Muraqib')

@section('content')
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-1">
                <li class="breadcrumb-item"><a href="{{ route('teacher.subjects.index') }}">Subjects</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.quizzes.index', $quiz->subject) }}">{{ $quiz->subject->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('teacher.attempts.index', $quiz) }}">{{ $quiz->title }}</a></li>
                <li class="breadcrumb-item active">Review</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">Attempt Review</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Student Information</h6>
                    <div class="mb-2"><span class="text-muted small">Name:</span> <strong>{{ $attempt->student->name }}</strong></div>
                    <div class="mb-2"><span class="text-muted small">Email:</span> {{ $attempt->student->email }}</div>
                    <div class="mb-2"><span class="text-muted small">Quiz:</span> {{ $quiz->title }}</div>
                    <div class="mb-2"><span class="text-muted small">Started:</span> {{ $attempt->started_at?->format('M d, Y H:i:s') }}</div>
                    <div class="mb-2"><span class="text-muted small">Submitted:</span> {{ $attempt->submitted_at?->format('M d, Y H:i:s') ?? 'Not submitted' }}</div>
                    @if($attempt->started_at && $attempt->submitted_at)
                        <div><span class="text-muted small">Duration:</span> {{ $attempt->started_at->diffInMinutes($attempt->submitted_at) }} minutes</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Score</h6>
                    <div class="text-center mb-3">
                        <span class="fs-1 fw-bold {{ ($attempt->score ?? 0) >= 60 ? 'text-success' : 'text-danger' }}">
                            {{ $attempt->score !== null ? number_format($attempt->score, 1) . '%' : '—' }}
                        </span>
                    </div>
                    @if($attempt->score !== null)
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar {{ $attempt->score >= 60 ? 'bg-success' : 'bg-danger' }}" style="width: {{ $attempt->score }}%"></div>
                        </div>
                    @endif

                    <h6 class="fw-bold mb-2" style="font-size: 0.85rem;">Anti-Cheat Score</h6>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                            @php $acPercent = $quiz->score_threshold > 0 ? min(($attempt->anticheat_score / $quiz->score_threshold) * 100, 100) : 0; @endphp
                            <div class="progress-bar {{ $attempt->is_flagged ? 'bg-danger' : 'bg-warning' }}" style="width: {{ $acPercent }}%"></div>
                        </div>
                        <span class="small fw-semibold">{{ $attempt->anticheat_score }} / {{ $quiz->score_threshold }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($attempt->is_flagged)
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Flagged:</strong> {{ $attempt->flag_reason }}</div>
    @elseif($attempt->anticheat_score > 0)
        <div class="alert alert-warning"><i class="bi bi-exclamation-circle me-2"></i>Suspicious activity detected (score: {{ $attempt->anticheat_score }}/{{ $quiz->score_threshold }}) but threshold not reached.</div>
    @else
        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>No suspicious activity detected during this attempt.</div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-shield-exclamation me-1"></i> Suspicious Events</div>
        <div class="card-body p-0">
            @if($events->isEmpty())
                <div class="p-4 text-center">
                    <div class="alert alert-info mb-0"><i class="bi bi-info-circle me-1"></i> No suspicious events were recorded for this attempt.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Points</th>
                                <th>Occurred At</th>
                                <th>Screenshot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                                <tr>
                                    <td>{{ $event->event_label }}</td>
                                    <td><span class="fw-semibold">+{{ $event->points }}</span></td>
                                    <td class="small">{{ $event->occurred_at->format('H:i:s') }}</td>
                                    <td>
                                        @if($event->screenshot_path)
                                            <img src="{{ route('teacher.screenshots.show', $event) }}" alt="Screenshot"
                                                 style="max-width: 80px; border-radius: 4px; cursor: pointer;"
                                                 data-bs-toggle="modal" data-bs-target="#screenshotModal"
                                                 data-src="{{ route('teacher.screenshots.show', $event) }}">
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td>{{ $events->sum('points') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="screenshotModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold">Event Screenshot</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalScreenshot" src="" alt="Screenshot" style="max-width: 100%; border-radius: 8px;">
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Answer Review</h5>
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
                            $isCorrectOpt = $option->is_correct;
                            $bgClass = '';
                            if ($isSelected && $isCorrectOpt) $bgClass = 'list-group-item-success';
                            elseif ($isSelected && !$isCorrectOpt) $bgClass = 'list-group-item-danger';
                            elseif ($isCorrectOpt) $bgClass = 'list-group-item-success';
                        @endphp
                        <div class="list-group-item {{ $bgClass }} d-flex align-items-center gap-2 py-2">
                            @if($isCorrectOpt)
                                <i class="bi bi-check-circle-fill text-success"></i>
                            @elseif($isSelected)
                                <i class="bi bi-x-circle-fill text-danger"></i>
                            @else
                                <i class="bi bi-circle text-muted"></i>
                            @endif
                            <span style="font-size: 0.9rem;">{{ $option->option_text }}</span>
                            @if($isSelected)
                                <span class="badge bg-dark ms-auto" style="font-size: 0.65rem;">Student's answer</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex gap-2 mt-4">
        <a href="{{ route('teacher.attempts.index', $quiz) }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i> Back to Attempts</a>
        <button type="button" class="btn btn-outline-secondary" onclick="showExportToast()"><i class="bi bi-file-pdf me-1"></i> Export PDF</button>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <div class="toast align-items-center text-bg-info border-0" id="exportToast" role="alert">
            <div class="d-flex">
                <div class="toast-body"><i class="bi bi-info-circle me-1"></i> PDF export coming soon.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showExportToast() {
            new bootstrap.Toast(document.getElementById('exportToast')).show();
        }
        document.querySelectorAll('[data-bs-target="#screenshotModal"]').forEach(function (img) {
            img.addEventListener('click', function () {
                document.getElementById('modalScreenshot').src = img.dataset.src;
            });
        });
    </script>
@endpush
