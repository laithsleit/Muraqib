@extends('layouts.minimal')
@section('title', 'Camera Check — Muraqib')

@section('nav-right')
    <a href="{{ route('student.quizzes.index', $quiz->subject) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <h4 class="fw-bold mb-1">{{ $quiz->title }}</h4>
            <p class="text-muted mb-4">Duration: {{ $quiz->duration_minutes }} minutes</p>

            <div class="camera-preview-box mx-auto mb-3" style="position: relative;">
                <video id="cameraVideo" autoplay muted playsinline></video>
                <canvas id="cameraCanvas" style="position: absolute; top: 0; left: 0;"></canvas>
                <div id="cameraPlaceholder" class="camera-placeholder">
                    <i class="bi bi-camera-video fs-1 text-muted"></i>
                    <p class="text-muted small mt-2 mb-0">Initializing camera...</p>
                </div>
            </div>

            <div id="cameraStatus" class="mb-4">
                <span class="badge bg-secondary">Checking camera...</span>
            </div>

            <form action="{{ route('student.quizzes.start', $quiz) }}" method="POST">
                @csrf
                <button type="submit" id="startQuizBtn" class="btn btn-primary btn-lg px-5" disabled>
                    <i class="bi bi-play-fill me-1"></i> Start Quiz
                </button>
            </form>

            <p class="text-muted small mt-3" style="max-width: 400px; margin: 0 auto;">
                <i class="bi bi-info-circle me-1"></i>
                Your camera will remain active during the quiz to monitor for suspicious behaviour.
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/camera-check.js') }}?timestamp={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checker = new CameraCheck({
                videoEl: document.getElementById('cameraVideo'),
                canvasEl: document.getElementById('cameraCanvas'),
                placeholderEl: document.getElementById('cameraPlaceholder'),
                statusEl: document.getElementById('cameraStatus'),
                startBtn: document.getElementById('startQuizBtn'),
            });
            checker.init();
        });
    </script>
@endpush
