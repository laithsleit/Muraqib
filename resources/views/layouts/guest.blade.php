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
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </a>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="site-footer py-4 text-center border-top mt-5">
        <div class="container">
            <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Muraqib" height="20">
                <span class="fw-semibold" style="color: var(--text-main);">Muraqib</span>
            </div>
            <p class="mb-0">&copy; {{ date('Y') }} Muraqib. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
