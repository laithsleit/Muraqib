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
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto gap-1">
                    @if(auth()->user()->hasRole('super_admin'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-grid-1x2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people me-1"></i> Manage Users
                            </a>
                        </li>
                    @elseif(auth()->user()->hasRole('teacher'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
                                <i class="bi bi-grid-1x2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('teacher.subjects.*') ? 'active' : '' }}" href="{{ route('teacher.subjects.index') }}">
                                <i class="bi bi-book me-1"></i> Subjects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('teacher.anticheat-guide') ? 'active' : '' }}" href="{{ route('teacher.anticheat-guide') }}">
                                <i class="bi bi-shield-check me-1"></i> Anti-Cheat Guide
                            </a>
                        </li>
                    @elseif(auth()->user()->hasRole('student'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                                <i class="bi bi-grid-1x2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.subjects.*') ? 'active' : '' }}" href="{{ route('student.subjects.index') }}">
                                <i class="bi bi-journal-text me-1"></i> My Subjects
                            </a>
                        </li>
                    @endif
                </ul>
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <span class="badge rounded-pill" style="background: var(--primary-light); color: var(--primary); font-weight: 600; font-size: 0.7rem; padding: 0.35em 0.8em;">
                            {{ ucfirst(str_replace('_', ' ', auth()->user()->roles->first()?->name ?? 'user')) }}
                        </span>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-0" href="#" role="button" data-bs-toggle="dropdown">
                            <span class="avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->name, ' ') ?: '', 1, 1)) }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end mt-2">
                            <li>
                                <span class="dropdown-item-text">
                                    <span class="fw-semibold d-block" style="font-size: 0.85rem;">{{ auth()->user()->name }}</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ auth()->user()->email }}</span>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="site-footer py-3 mt-auto border-top">
        <div class="container text-center">
            <span>&copy; {{ date('Y') }} Muraqib</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
