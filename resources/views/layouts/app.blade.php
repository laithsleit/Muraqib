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
<body class="app-body">
    {{-- Top Navbar --}}
    <nav class="app-navbar">
        <div class="app-navbar-inner">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <a class="navbar-brand mb-0" href="/">
                    <img src="{{ asset('assets/img/logo.svg') }}" alt="Muraqib">
                    Muraqib
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge rounded-pill d-none d-sm-inline-block" style="background: var(--primary-light); color: var(--primary); font-weight: 600; font-size: 0.7rem; padding: 0.35em 0.8em;">
                    {{ ucfirst(str_replace('_', ' ', auth()->user()->roles->first()?->name ?? 'user')) }}
                </span>

                <div class="dropdown">
                    <a class="d-flex align-items-center gap-2 text-decoration-none" href="#" role="button" data-bs-toggle="dropdown">
                        <span class="avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->name, ' ') ?: '', 1, 1)) }}</span>
                        <div class="d-none d-md-block text-start" style="line-height: 1.2;">
                            <span class="d-block fw-semibold" style="font-size: 0.85rem; color: var(--text-main);">{{ auth()->user()->name }}</span>
                            <span class="d-block" style="font-size: 0.7rem; color: var(--text-muted);">{{ auth()->user()->email }}</span>
                        </div>
                        <i class="bi bi-chevron-down" style="font-size: 0.7rem; color: var(--text-muted);"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-2">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    {{-- Sidebar Overlay (mobile) --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Sidebar --}}
    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-header d-lg-none">
            <a class="navbar-brand mb-0" href="/">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Muraqib">
                Muraqib
            </a>
            <button class="sidebar-close" id="sidebarClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Navigation</div>

            @if(auth()->user()->hasRole('super_admin'))
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Manage Users
                </a>
            @elseif(auth()->user()->hasRole('teacher'))
                <a href="{{ route('teacher.dashboard') }}" class="sidebar-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
                <a href="{{ route('teacher.subjects.index') }}" class="sidebar-link {{ request()->routeIs('teacher.subjects.*') ? 'active' : '' }}">
                    <i class="bi bi-book"></i> Subjects
                </a>

                <div class="sidebar-section-label mt-3">Tools</div>
                <a href="{{ route('teacher.anticheat-guide') }}" class="sidebar-link {{ request()->routeIs('teacher.anticheat-guide') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> Anti-Cheat Guide
                </a>
            @elseif(auth()->user()->hasRole('student'))
                <a href="{{ route('student.dashboard') }}" class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
                <a href="{{ route('student.subjects.index') }}" class="sidebar-link {{ request()->routeIs('student.subjects.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> My Subjects
                </a>
            @endif
        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="app-main">
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        const toggle = document.getElementById('sidebarToggle');
        const close = document.getElementById('sidebarClose');
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        }
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        }

        toggle.addEventListener('click', openSidebar);
        close.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    </script>
    @stack('scripts')
</body>
</html>
