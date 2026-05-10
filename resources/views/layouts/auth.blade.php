<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'Muraqib — AI-proctored online exams with live face, gaze and phone detection for teachers and students.')">
    <title>@yield('title', 'Muraqib')</title>
    <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('assets/vendor/inter/inter-latin.woff2') }}" crossorigin>
    <link href="{{ asset('assets/vendor/inter/inter.css') }}" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/logo.svg') }}">
</head>
<body>
    <main class="auth-wrapper">
        <a href="/" class="auth-back-link"><i class="bi bi-arrow-left"></i> Back to Home</a>
        <div class="auth-card">
            <a href="/" class="auth-logo text-decoration-none">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="" width="32" height="32">
                <span>Muraqib</span>
            </a>
            <p class="text-muted text-center small mb-4">Smart Quiz Monitoring Platform</p>

            @yield('content')

            <p class="text-center text-muted mt-4 mb-0" style="font-size: 0.75rem;">&copy; {{ date('Y') }} Muraqib</p>
        </div>
    </main>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
