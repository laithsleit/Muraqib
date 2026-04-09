@extends('layouts.minimal')
@section('title', '{{ $quiz->title }} — Muraqib')

@section('nav-right')
    <span class="text-muted small">{{ auth()->user()->name }}</span>
@endsection

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
    <div id="cameraBlockOverlay" class="camera-block-overlay" style="display: none;">
        <div class="text-center">
            <i class="bi bi-camera-video-off" style="font-size: 3rem; color: var(--danger);"></i>
            <h4 class="fw-bold mt-3">Camera Access Required</h4>
            <p class="text-muted mb-3" style="max-width: 400px;">Your browser blocked camera access. This quiz requires an active camera for monitoring.</p>
            <a href="{{ route('student.quizzes.check', $quiz) }}" class="btn btn-primary">Back to Camera Check</a>
        </div>
    </div>

    <div id="quizContent" class="row g-4">
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
                        <div style="position: relative; display: inline-block;">
                            <video id="monitorVideo" autoplay muted playsinline style="width: 200px; border-radius: 8px; display: block;"></video>
                            <canvas id="monitorCanvas" style="position: absolute; top: 0; left: 0; width: 200px; pointer-events: none;"></canvas>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-1 mt-2">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/quiz-timer.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
    <script src="{{ asset('assets/js/anticheat-monitor.js') }}?t={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            window._muraqibSubmitting = false;

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                const video = document.getElementById('monitorVideo');
                video.srcObject = stream;

                await new Promise(resolve => { video.onloadedmetadata = resolve; });
                await new Promise(resolve => requestAnimationFrame(resolve));
                const canvas = document.getElementById('monitorCanvas');
                canvas.width = video.clientWidth;
                canvas.height = video.clientHeight;
            } catch (err) {
                document.getElementById('cameraBlockOverlay').style.display = 'flex';
                document.getElementById('quizContent').style.display = 'none';
                return;
            }

            const timer = new QuizTimer({
                endTimestamp: {{ $endTime->timestamp }},
                displayEl: document.getElementById('timerDisplay'),
                formEl: document.getElementById('quizForm'),
            });
            timer.init();

            const monitor = new AntiCheatMonitor();
            monitor.init({
                attemptId: {{ $attempt->id }},
                videoElement: document.getElementById('monitorVideo'),
                canvasElement: document.getElementById('monitorCanvas'),
                reportEndpoint: '{{ route("student.attempts.event", $attempt) }}',
                submitEndpoint: '{{ route("student.attempts.submit", $attempt) }}',
                csrfToken: '{{ csrf_token() }}',
            });

            document.getElementById('quizForm').addEventListener('submit', function () {
                window._muraqibSubmitting = true;
                monitor.destroy();
            });

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
                if (window._muraqibSubmitting) return;
                e.preventDefault();
                e.returnValue = 'Your quiz is still in progress. Leaving will submit your current answers.';
            });
        });
    </script>
@endpush
