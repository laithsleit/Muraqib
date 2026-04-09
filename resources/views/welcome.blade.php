@extends('layouts.guest')

@section('title', 'Muraqib — Smart Quiz Monitoring Platform')

@section('content')
    <section class="hero-section text-white">
        <div class="container text-center py-5">
            <div class="py-4">
                <div class="hero-badge">
                    <i class="bi bi-shield-check"></i> Smart Anti-Cheat Monitoring
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

    <section class="py-5" id="features">
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

    <section class="py-5" id="how-it-works" style="background: var(--card-bg);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="letter-spacing: -0.02em;">How it works</h2>
                <p class="text-muted mx-auto" style="max-width: 480px;">Three simple steps to secure, monitored assessments.</p>
            </div>
            <div class="row g-4 align-items-start">
                <div class="col-md-4 text-center">
                    <div class="step-number mx-auto mb-3">1</div>
                    <h5 class="fw-bold mb-2">Create a Quiz</h5>
                    <p class="text-muted" style="font-size: 0.9rem;">Teachers build quizzes, set time limits, and configure anti-cheat sensitivity per assessment.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="step-number mx-auto mb-3">2</div>
                    <h5 class="fw-bold mb-2">Students Take It</h5>
                    <p class="text-muted" style="font-size: 0.9rem;">Students launch the quiz. The monitoring system runs silently in the background, tracking suspicious events.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="step-number mx-auto mb-3">3</div>
                    <h5 class="fw-bold mb-2">Review Results</h5>
                    <p class="text-muted" style="font-size: 0.9rem;">Teachers see scores and flagged attempts instantly. Drill into event timelines for full transparency.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge rounded-pill mb-3" style="background: var(--primary-light); color: var(--primary); font-weight: 600; font-size: 0.75rem; padding: 0.4em 1em;">About Muraqib</span>
                    <h2 class="fw-bold mb-3" style="letter-spacing: -0.02em;">Built for academic integrity</h2>
                    <p class="text-muted" style="font-size: 0.95rem; line-height: 1.7;">
                        Muraqib (Arabic for "observer") is a quiz monitoring platform designed to help educators maintain fair assessments in online and hybrid learning environments. We believe every student deserves a level playing field.
                    </p>
                    <p class="text-muted" style="font-size: 0.95rem; line-height: 1.7;">
                        Our platform detects common cheating patterns — tab switching, face disappearance, multiple faces, and phone usage — and gives teachers the tools to review flagged attempts with full context, not just a score.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-card p-3 text-center">
                                <div class="fs-2 fw-bold text-gradient">5+</div>
                                <div class="text-muted small">Detection Events</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 text-center">
                                <div class="fs-2 fw-bold text-gradient">3</div>
                                <div class="text-muted small">User Roles</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 text-center">
                                <div class="fs-2 fw-bold text-gradient">100%</div>
                                <div class="text-muted small">Open & Transparent</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 text-center">
                                <div class="fs-2 fw-bold text-gradient">Free</div>
                                <div class="text-muted small">No Hidden Costs</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="faq" style="background: var(--card-bg);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="letter-spacing: -0.02em;">Frequently asked questions</h2>
                <p class="text-muted mx-auto" style="max-width: 480px;">Got questions? We've got answers.</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 mb-3" style="border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" style="font-size: 0.95rem;">
                                    What does Muraqib monitor during a quiz?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted" style="font-size: 0.9rem;">
                                    Muraqib tracks several types of suspicious behaviour: tab or window switching, face not being detected, multiple faces in frame, the student looking away from the screen, and phone detection. Each event type carries a configurable point value, and if a student's total exceeds the threshold set by the teacher, the attempt is automatically flagged for review.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3" style="border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="font-size: 0.95rem;">
                                    Is Muraqib free to use?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted" style="font-size: 0.9rem;">
                                    Yes. Muraqib is completely free. There are no hidden costs, premium tiers, or usage limits. We believe academic integrity tools should be accessible to every educator and institution regardless of budget.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3" style="border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" style="font-size: 0.95rem;">
                                    Can teachers customise the anti-cheat sensitivity?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted" style="font-size: 0.9rem;">
                                    Yes. Each quiz has a configurable score threshold. Teachers can decide how many suspicious event points trigger a flag. This allows flexibility — a low-stakes practice quiz might have a higher threshold, while a final exam could be more strict.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3" style="border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" style="font-size: 0.95rem;">
                                    What roles are available in the platform?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted" style="font-size: 0.9rem;">
                                    Muraqib supports three roles: <strong>Super Admin</strong> for platform-wide user management, <strong>Teacher</strong> for creating subjects, quizzes, and reviewing results, and <strong>Student</strong> for enrolling in subjects and taking quizzes.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0" style="border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" style="font-size: 0.95rem;">
                                    Does Muraqib record students?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted" style="font-size: 0.9rem;">
                                    Muraqib may capture screenshots at the moment a suspicious event is detected for review purposes. It does not continuously record video or audio. Transparency and student privacy are core to the platform's design.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="contact">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold" style="letter-spacing: -0.02em;">Get in touch</h2>
                        <p class="text-muted mx-auto" style="max-width: 480px;">Have a question, suggestion, or want to report an issue? We'd love to hear from you.</p>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="feature-icon icon-primary mx-auto">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Email</h6>
                                <p class="text-muted small mb-0">support@muraqib.com</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="feature-icon icon-accent mx-auto">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Location</h6>
                                <p class="text-muted small mb-0">Amman, Jordan</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="feature-icon icon-success mx-auto">
                                    <i class="bi bi-github"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Open Source</h6>
                                <p class="text-muted small mb-0">GitHub Repository</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" style="background: var(--primary-light);">
        <div class="container text-center py-3">
            <h3 class="fw-bold mb-2" style="letter-spacing: -0.02em;">Ready to get started?</h3>
            <p class="text-muted mb-4">Create your first quiz in minutes. No credit card required.</p>
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-5">
                Sign In <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </section>
@endsection
