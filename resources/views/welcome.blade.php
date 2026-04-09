@extends('layouts.guest')

@section('title', 'Muraqib — Smart Quiz Monitoring Platform')

@section('content')
    <section class="hero-section text-white">
        <div class="container text-center py-5">
            <div class="py-4">
                <div class="hero-badge">
                    <i class="bi bi-shield-check"></i> AI-Powered Anti-Cheat
                </div>
                <h1 class="display-3 fw-bold mb-3" style="letter-spacing: -0.03em;">
                    Smarter Exams,<br>Fairer Results
                </h1>
                <p class="lead mb-4 mx-auto" style="max-width: 540px; opacity: 0.9;">
                    Monitor quizzes in real-time with intelligent proctoring. Detect suspicious activity before it becomes a problem.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('login') }}" class="btn btn-light btn-lg px-5 fw-semibold">
                        Get Started <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="letter-spacing: -0.02em;">Everything you need</h2>
                <p class="text-muted mx-auto" style="max-width: 480px;">A complete platform for creating, delivering, and monitoring online assessments with integrity.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card h-100 p-4">
                        <div class="feature-icon icon-primary">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Smart Quizzes</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Create quizzes with time limits, retake policies, and automatic grading. Flexible enough for any course.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card h-100 p-4">
                        <div class="feature-icon icon-accent">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Anti-Cheat Monitoring</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Real-time detection of tab switches, face anomalies, and phone usage. Automatic flagging keeps assessments fair.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card h-100 p-4">
                        <div class="feature-icon icon-success">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Instant Reports</h5>
                        <p class="text-muted mb-0" style="font-size: 0.9rem;">Detailed analytics and integrity reports at a glance. Know exactly which attempts need review.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" style="background: var(--primary-light);">
        <div class="container text-center py-3">
            <h3 class="fw-bold mb-2" style="letter-spacing: -0.02em;">Ready to get started?</h3>
            <p class="text-muted mb-4">Create your first quiz in minutes.</p>
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5">
                Sign In <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </section>
@endsection
