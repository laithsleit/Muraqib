<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Muraqib')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/logo.svg') }}">
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Muraqib">
                Muraqib
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#guestNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="guestNav">
                <ul class="navbar-nav mx-auto gap-1">
                    <li class="nav-item"><a class="nav-link" href="/#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#contact">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="site-footer py-5 border-top">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="{{ asset('assets/img/logo.svg') }}" alt="Muraqib" height="24">
                        <span class="fw-bold" style="color: var(--text-main); font-size: 1.05rem;">Muraqib</span>
                    </div>
                    <p class="mb-0" style="font-size: 0.85rem; max-width: 280px;">Smart quiz monitoring platform built for academic integrity. Free and open for all educators.</p>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold mb-3" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-main);">Platform</h6>
                    <ul class="list-unstyled" style="font-size: 0.85rem;">
                        <li class="mb-2"><a href="/#features">Features</a></li>
                        <li class="mb-2"><a href="/#how-it-works">How It Works</a></li>
                        <li><a href="/#faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold mb-3" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-main);">Company</h6>
                    <ul class="list-unstyled" style="font-size: 0.85rem;">
                        <li class="mb-2"><a href="/#about">About</a></li>
                        <li class="mb-2"><a href="/#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-bold mb-3" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-main);">Get Started</h6>
                    <p style="font-size: 0.85rem;" class="mb-3">Sign in to start creating secure assessments today.</p>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                </div>
            </div>
            <hr class="my-4" style="border-color: var(--border);">
            <p class="text-center mb-0">&copy; {{ date('Y') }} Muraqib. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
