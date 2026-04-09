@extends('layouts.minimal')
@section('title', '{{ $quiz->title }} — Muraqib')

@section('nav-right')
    <span class="text-muted small">{{ auth()->user()->name }}</span>
@endsection

@push('head')
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
@endpush

@section('content')
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;"></div>

    <div class="row g-4">
        <div class="col-lg-8">
            <h5 class="fw-bold mb-1">{{ $quiz->title }}</h5>
            @php $answeredCount = $existingAnswers->filter(fn($v) => $v !== null)->count(); @endphp
            <div class="d-flex align-items-center gap-2 mb-4">
                <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $questions->count() > 0 ? ($answeredCount / $questions->count()) * 100 : 0 }}%; background: var(--primary);"
                         id="progressBar"></div>
                </div>
                <span class="text-muted small" id="progressText">{{ $answeredCount }}/{{ $questions->count() }}</span>
            </div>

            <form id="quizForm" action="{{ route('student.attempts.submit', $attempt) }}" method="POST">
                @csrf

                @foreach($questions as $index => $question)
                    <div class="card mb-3 question-card">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <span class="badge bg-primary me-1">{{ $index + 1 }}</span>
                                {{ $question->question_text }}
                            </h6>

                            @foreach($question->options as $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input question-radio"
                                           type="radio"
                                           name="answers[{{ $question->id }}]"
                                           id="opt_{{ $option->id }}"
                                           value="{{ $option->id }}"
                                           data-question="{{ $question->id }}"
                                           {{ ($existingAnswers[$question->id] ?? null) == $option->id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="opt_{{ $option->id }}">
                                        {{ $option->option_text }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="text-end">
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#submitModal">
                        <i class="bi bi-check2-all me-1"></i> Submit Quiz
                    </button>
                </div>

                <div class="modal fade" id="submitModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h6 class="modal-title fw-bold">Submit Quiz?</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted mb-0">Are you sure you want to submit? You cannot change your answers after submitting.</p>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Yes, Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div style="position: sticky; top: 80px;">
                <div class="card mb-3 text-center">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Time Remaining</div>
                        <div id="timerDisplay" class="fw-bold text-primary" style="font-size: 2.5rem; letter-spacing: 0.05em; font-variant-numeric: tabular-nums;">
                            --:--
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body p-2 text-center">
                        <video id="monitorVideo" autoplay muted playsinline style="width: 100%; max-width: 200px; border-radius: 8px; display: block; margin: 0 auto;"></video>
                        <div class="d-flex align-items-center justify-content-center gap-1 mt-2" id="monitorStatus">
                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background: var(--success);"></span>
                            <span class="text-muted small">Monitoring Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/quiz-timer.js') }}"></script>
    <script src="{{ asset('assets/js/anticheat-monitor.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const endTime = {{ $endTime->timestamp }};
            const timer = new QuizTimer({
                endTimestamp: endTime,
                displayEl: document.getElementById('timerDisplay'),
                formEl: document.getElementById('quizForm'),
            });
            timer.init();

            const monitor = new AntiCheatMonitor();
            monitor.init({
                attemptId: {{ $attempt->id }},
                videoElement: document.getElementById('monitorVideo'),
                reportEndpoint: '{{ route("student.attempts.event", $attempt) }}',
                csrfToken: '{{ csrf_token() }}',
            });

            document.getElementById('quizForm').addEventListener('submit', () => monitor.destroy());

            const totalQuestions = {{ $questions->count() }};
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            document.querySelectorAll('.question-radio').forEach(radio => {
                radio.addEventListener('change', () => {
                    const answered = new Set();
                    document.querySelectorAll('.question-radio:checked').forEach(r => {
                        answered.add(r.dataset.question);
                    });
                    const count = answered.size;
                    progressBar.style.width = ((count / totalQuestions) * 100) + '%';
                    progressText.textContent = count + '/' + totalQuestions;
                });
            });

            window.addEventListener('beforeunload', function (e) {
                e.preventDefault();
                e.returnValue = '';
            });
        });
    </script>
@endpush
