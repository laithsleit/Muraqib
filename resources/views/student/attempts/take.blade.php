@extends('layouts.minimal')
@section('title', $quiz->title . ' — Muraqib')

@section('nav-right')
    <span class="text-muted small">{{ auth()->user()->name }}</span>
@endsection

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .muraqib-loader {
            position: fixed; inset: 0; z-index: 9999;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #06b6d4 100%);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
        }
        .muraqib-loader__card {
            width: min(440px, 90vw);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
            padding: 32px 28px;
            text-align: center;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
        }
        .muraqib-loader__icon {
            font-size: 2.4rem; margin-bottom: 12px; opacity: 0.95;
        }
        .muraqib-loader__title {
            font-weight: 700; letter-spacing: -0.02em; margin-bottom: 4px;
        }
        .muraqib-loader__sub {
            font-size: 0.875rem; opacity: 0.85; margin-bottom: 22px;
        }
        .muraqib-loader__bar {
            height: 8px; width: 100%;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 999px; overflow: hidden;
        }
        .muraqib-loader__fill {
            height: 100%; width: 0%;
            background: #ffffff;
            border-radius: 999px;
            transition: width 240ms ease-out;
        }
        .muraqib-loader__meta {
            display: flex; justify-content: space-between;
            font-variant-numeric: tabular-nums;
            font-size: 0.85rem; margin-top: 10px; opacity: 0.92;
        }
        .muraqib-loader__error {
            margin-top: 18px; font-size: 0.85rem;
            background: rgba(239, 68, 68, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px; padding: 10px 12px; display: none;
        }
        .muraqib-loader__error a { color: #fff; text-decoration: underline; }
        #quizFieldset[disabled] { opacity: 0.4; pointer-events: none; }
    </style>
@endpush

@section('content')
    <div id="muraqibLoader" class="muraqib-loader" role="dialog" aria-live="polite">
        <div class="muraqib-loader__card">
            <div class="muraqib-loader__icon"><i class="bi bi-shield-check"></i></div>
            <div class="muraqib-loader__title">Preparing secure exam environment</div>
            <div class="muraqib-loader__sub" id="loaderStatus">Loading proctoring components…</div>
            <div class="muraqib-loader__bar">
                <div class="muraqib-loader__fill" id="loaderFill"></div>
            </div>
            <div class="muraqib-loader__meta">
                <span id="loaderStage">Initializing</span>
                <span id="loaderPercent">0%</span>
            </div>
            <div class="muraqib-loader__error" id="loaderError"></div>
        </div>
    </div>

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
                <fieldset id="quizFieldset" disabled>

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
                    <button type="button" id="openSubmitModalBtn" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#submitModal" disabled>
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
                </fieldset>
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
    <script>
        (function () {
            window._muraqibSubmitting = false;

            const fillEl = document.getElementById('loaderFill');
            const pctEl = document.getElementById('loaderPercent');
            const stageEl = document.getElementById('loaderStage');
            const statusEl = document.getElementById('loaderStatus');
            const errorEl = document.getElementById('loaderError');
            const loaderEl = document.getElementById('muraqibLoader');

            // Weighted stages summing to 100.
            const STAGES = [
                { id: 'sweetalert',   label: 'UI library',           src: 'https://cdn.jsdelivr.net/npm/sweetalert2@11',                       weight: 5,  kind: 'script', check: () => typeof Swal !== 'undefined' },
                { id: 'timer',        label: 'Timer',                src: '{{ asset('assets/js/quiz-timer.js') }}',                            weight: 5,  kind: 'script', check: () => typeof QuizTimer !== 'undefined' },
                { id: 'facemesh',     label: 'Face mesh engine',     src: '{{ asset('assets/mediapipe/face_mesh/face_mesh.js') }}',            weight: 10, kind: 'script', check: () => typeof FaceMesh !== 'undefined' },
                { id: 'drawutils',    label: 'Drawing utilities',    src: 'https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils',             weight: 5,  kind: 'script', check: () => typeof drawConnectors !== 'undefined' },
                { id: 'tfjs',         label: 'TensorFlow runtime',   src: 'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs',                     weight: 10, kind: 'script', check: () => typeof tf !== 'undefined' },
                { id: 'cocossd',      label: 'Object detector',      src: 'https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd',          weight: 5,  kind: 'script', check: () => typeof cocoSsd !== 'undefined' },
                { id: 'monitor',      label: 'Anti-cheat monitor',   src: '{{ asset('assets/js/anticheat-monitor.js') }}?t={{ time() }}',      weight: 5,  kind: 'script', check: () => typeof AntiCheatMonitor !== 'undefined' },
                { id: 'camera',       label: 'Camera',               weight: 10, kind: 'task' },
                { id: 'cocomodel',    label: 'Object detection model', weight: 30, kind: 'task' },
                { id: 'monitorStart', label: 'Monitor warm-up',      weight: 15, kind: 'task' },
            ];

            let earned = 0;
            function award(weight, stageLabel, statusText) {
                earned = Math.min(100, earned + weight);
                fillEl.style.width = earned + '%';
                pctEl.textContent = Math.round(earned) + '%';
                if (stageLabel) stageEl.textContent = stageLabel;
                if (statusText) statusEl.textContent = statusText;
            }

            function showError(message, recoverUrl) {
                errorEl.style.display = 'block';
                errorEl.innerHTML = message +
                    (recoverUrl ? ' <a href="' + recoverUrl + '">Back to camera check</a>' : '');
            }

            function loadScript(src) {
                return new Promise(function (resolve, reject) {
                    const s = document.createElement('script');
                    s.src = src;
                    s.async = false;
                    if (/^https?:\/\//.test(src) && !src.startsWith(window.location.origin)) {
                        s.crossOrigin = 'anonymous';
                    }
                    s.onload = () => resolve();
                    s.onerror = () => reject(new Error('Failed to load ' + src));
                    document.head.appendChild(s);
                });
            }

            async function bootstrap() {
                // 1. Load all scripts in order, tallying weight as each finishes.
                for (const stage of STAGES.filter(s => s.kind === 'script')) {
                    stageEl.textContent = stage.label;
                    statusEl.textContent = 'Downloading ' + stage.label.toLowerCase() + '…';
                    try {
                        await loadScript(stage.src);
                    } catch (err) {
                        showError('Could not load required components. Check your internet connection and refresh.');
                        throw err;
                    }
                    if (!stage.check()) {
                        showError('A required component did not initialise. Please refresh.');
                        throw new Error('Check failed: ' + stage.id);
                    }
                    award(stage.weight, stage.label, stage.label + ' ready');
                }

                // 2. Camera.
                stageEl.textContent = 'Camera';
                statusEl.textContent = 'Requesting camera access…';
                const video = document.getElementById('monitorVideo');
                const canvas = document.getElementById('monitorCanvas');
                let stream;
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true });
                } catch (err) {
                    showError('Camera access was blocked.', '{{ route('student.quizzes.check', $quiz) }}');
                    document.getElementById('cameraBlockOverlay').style.display = 'flex';
                    document.getElementById('quizContent').style.display = 'none';
                    throw err;
                }
                video.srcObject = stream;
                await new Promise(resolve => { video.onloadedmetadata = resolve; });
                await new Promise(resolve => requestAnimationFrame(resolve));
                canvas.width = video.clientWidth;
                canvas.height = video.clientHeight;
                award(10, 'Camera', 'Camera ready');

                // 3. COCO-SSD model — heaviest network step, no native progress.
                // Animate the bar smoothly across its weight while load() runs.
                stageEl.textContent = 'Object detection model';
                statusEl.textContent = 'Downloading detection model (this can take a moment)…';
                const cocoWeight = 30;
                const cocoStart = earned;
                const cocoTarget = earned + cocoWeight - 2; // leave 2% for completion
                let cocoFrac = 0;
                const cocoTimer = setInterval(() => {
                    // Asymptotic ease toward target.
                    cocoFrac = cocoFrac + (1 - cocoFrac) * 0.04;
                    const pos = cocoStart + (cocoTarget - cocoStart) * cocoFrac;
                    fillEl.style.width = pos.toFixed(1) + '%';
                    pctEl.textContent = Math.round(pos) + '%';
                }, 250);

                let cocoModel;
                try {
                    cocoModel = await cocoSsd.load({ base: 'lite_mobilenet_v2' });
                } catch (e1) {
                    try {
                        cocoModel = await cocoSsd.load();
                    } catch (e2) {
                        clearInterval(cocoTimer);
                        showError('Could not load the detection model. Please check your connection and refresh.');
                        throw e2;
                    }
                }
                clearInterval(cocoTimer);
                earned = cocoStart + cocoWeight;
                award(0, 'Object detection model', 'Detection model ready');

                // 4. Start the monitor (it initialises FaceMesh internally).
                stageEl.textContent = 'Monitor warm-up';
                statusEl.textContent = 'Starting real-time monitoring…';
                const monitor = new AntiCheatMonitor();
                // Inject the already-loaded model so we don't double-load.
                monitor._preloadedCocoModel = cocoModel;
                await monitor.init({
                    attemptId: {{ $attempt->id }},
                    videoElement: video,
                    canvasElement: canvas,
                    reportEndpoint: '{{ route("student.attempts.event", $attempt) }}',
                    submitEndpoint: '{{ route("student.attempts.submit", $attempt) }}',
                    csrfToken: '{{ csrf_token() }}',
                });
                award(15, 'Ready', 'Monitoring active');

                // 5. Tell the server we're ready, get the authoritative end timestamp.
                let endTimestamp = {{ $endTime->timestamp }};
                try {
                    const res = await fetch('{{ route('student.attempts.ready', $attempt) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.end_timestamp) endTimestamp = data.end_timestamp;
                    }
                } catch (e) {
                    // Fall back to server-rendered endTime; loss is at most the loading window.
                }

                // 6. Unlock the quiz and start the timer.
                const fieldset = document.getElementById('quizFieldset');
                fieldset.disabled = false;
                document.getElementById('openSubmitModalBtn').disabled = false;

                const timer = new QuizTimer({
                    endTimestamp: endTimestamp,
                    displayEl: document.getElementById('timerDisplay'),
                    formEl: document.getElementById('quizForm'),
                });
                timer.init();

                // Fade out the loader.
                loaderEl.style.transition = 'opacity 320ms ease';
                loaderEl.style.opacity = '0';
                setTimeout(() => loaderEl.remove(), 360);

                return monitor;
            }

            document.addEventListener('DOMContentLoaded', async function () {
                let monitor;
                try {
                    monitor = await bootstrap();
                } catch (err) {
                    console.error('[Muraqib] bootstrap failed', err);
                    return;
                }

            document.getElementById('quizForm').addEventListener('submit', function () {
                window._muraqibSubmitting = true;
                monitor.destroy();
            });

            const totalQuestions = {{ $questions->count() }};
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            const saveAnswerUrl = '{{ route("student.attempts.saveAnswer", $attempt) }}';
            const csrfToken = '{{ csrf_token() }}';

            document.querySelectorAll('.question-radio').forEach(radio => {
                radio.addEventListener('change', () => {
                    // Update progress bar
                    const answered = new Set();
                    document.querySelectorAll('.question-radio:checked').forEach(r => {
                        answered.add(r.dataset.question);
                    });
                    const count = answered.size;
                    progressBar.style.width = ((count / totalQuestions) * 100) + '%';
                    progressText.textContent = count + '/' + totalQuestions;

                    // Auto-save this answer
                    fetch(saveAnswerUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            question_id: radio.dataset.question,
                            option_id: radio.value,
                        }),
                    }).catch(() => {});
                });
            });

            window.addEventListener('beforeunload', function (e) {
                if (window._muraqibSubmitting) return;
                e.preventDefault();
                e.returnValue = 'Your quiz is still in progress. Leaving will submit your current answers.';
            });

            // Submit on tab close / navigate away, but NOT on reload
            window.addEventListener('pagehide', function () {
                if (window._muraqibSubmitting) return;
                const nav = performance.getEntriesByType('navigation')[0];
                if (nav && nav.type === 'reload') return;
                const fd = new FormData(document.getElementById('quizForm'));
                navigator.sendBeacon('{{ route("student.attempts.submit", $attempt) }}', fd);
            });
            });
        })();
    </script>
@endpush
