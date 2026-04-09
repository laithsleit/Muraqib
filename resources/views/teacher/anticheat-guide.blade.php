@extends('layouts.app')
@section('title', 'Anti-Cheat Guide — Muraqib')

@section('content')
    <h4 class="fw-bold mb-4">Anti-Cheat Guide</h4>

    {{-- 1. How Monitoring Works --}}
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-camera-video me-1"></i> How Monitoring Works</div>
        <div class="card-body">
            <p class="mb-2">Muraqib uses the student's webcam during quizzes to detect suspicious behaviours in real time. The system runs silently in the background while the student takes the quiz.</p>
            <p class="mb-0">Each detected event adds points to the student's anti-cheat score. Once the score exceeds the threshold you set for that quiz, the attempt is automatically flagged for your review. You can then examine the event timeline, including screenshots captured at the moment of detection.</p>
        </div>
    </div>

    {{-- 2. Event Types & Point Values --}}
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-list-check me-1"></i> Event Types & Point Values</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Description</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(config('anticheat.event_points') as $key => $points)
                            <tr>
                                <td class="fw-semibold">{{ config('anticheat.event_labels')[$key] ?? $key }}</td>
                                <td class="text-muted">{{ config('anticheat.event_descriptions')[$key] ?? '' }}</td>
                                <td><span class="fw-bold">{{ $points }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 3. How Flagging Works --}}
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-flag me-1"></i> How Flagging Works</div>
        <div class="card-body">
            <p class="mb-2">Each quiz has a configurable <strong>score threshold</strong> that you set when creating or editing a quiz. During a quiz, every detected suspicious event adds its point value to the student's running anti-cheat score.</p>
            <p class="mb-3"><strong>Formula:</strong> Total Anti-Cheat Score &ge; Score Threshold &rarr; Attempt Flagged</p>
            <p class="mb-3"><strong>Example:</strong> If you set a threshold of 60, a student who looks away 3 times (3 &times; 5 = 15) and switches tabs twice (2 &times; 15 = 30) would have a score of 45 — not flagged. If they also have their face undetected once (10 more points), their score becomes 55 — still not flagged. One more looking-away event pushes them to 60 — flagged.</p>
            <div class="alert alert-info mb-0"><i class="bi bi-info-circle me-1"></i> Flagging does not automatically penalize the student. It only marks the attempt for your review.</div>
        </div>
    </div>

    {{-- 4. What Happens Before the Quiz --}}
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-webcam me-1"></i> What Happens Before the Quiz</div>
        <div class="card-body">
            <p class="mb-2">Before the quiz begins, each student goes through a <strong>pre-quiz camera check</strong>:</p>
            <p class="mb-2">The student must grant camera access. The system then uses face detection to verify that exactly one face is visible and properly positioned. The quiz cannot start until this check passes.</p>
            <p class="mb-0">This ensures the camera is working correctly and the student is present before any questions are shown.</p>
        </div>
    </div>

    {{-- 5. Tips for Setting Thresholds --}}
    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-sliders me-1"></i> Tips for Setting Thresholds</div>
        <div class="card-body">
            <ul class="mb-2">
                <li class="mb-2"><strong>High-stakes quizzes:</strong> Set a lower threshold (e.g., 30–40) to catch even minor suspicious activity.</li>
                <li class="mb-2"><strong>Casual assessments:</strong> A higher threshold (e.g., 80–100) allows minor disruptions like brief glances away without flagging.</li>
                <li class="mb-2"><strong>Default suggestion:</strong> The platform default is <strong>{{ config('anticheat.default_threshold') }}</strong> points, which works well for most standard assessments.</li>
                <li>Consider the quiz duration — longer quizzes naturally accumulate more minor events, so you may want a slightly higher threshold.</li>
            </ul>
        </div>
    </div>
@endsection
