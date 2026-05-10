<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'Muraqib — AI-proctored online exams with live face, gaze and phone detection for teachers and students.')">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <title>@yield('title', 'Muraqib')</title>
    <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('assets/vendor/inter/inter-latin.woff2') }}" crossorigin>
    <link href="{{ asset('assets/vendor/inter/inter.css') }}" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/logo.svg') }}">
    @stack('head')
</head>
<body style="background: var(--surface);">
    <nav class="navbar border-bottom" style="background: var(--card-bg);">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="" width="32" height="32">
                Muraqib
            </a>
            @yield('nav-right')
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>

    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
