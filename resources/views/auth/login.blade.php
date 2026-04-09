@extends('layouts.auth')

@section('title', 'Login — Muraqib')

@section('content')
    <h5 class="text-center mb-1 fw-bold">Welcome back</h5>
    <p class="text-center text-muted small mb-4">Sign in to your account</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">Password</label>
                <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size: 0.78rem;">Forgot password?</a>
            </div>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" placeholder="Enter your password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label small" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Sign In <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <hr class="my-4" style="border-color: var(--border);">
    <p class="text-center text-muted small mb-2">Quick login as demo account</p>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill demo-login-btn" data-email="admin@quiz.com" data-password="password">Admin</button>
        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill demo-login-btn" data-email="teacher@quiz.com" data-password="password">Teacher</button>
        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill demo-login-btn" data-email="student1@quiz.com" data-password="password">Student 1</button>
        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill demo-login-btn" data-email="student2@quiz.com" data-password="password">Student 2</button>
    </div>

    <script>
        document.querySelectorAll('.demo-login-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('email').value = this.dataset.email;
                document.getElementById('password').value = this.dataset.password;
            });
        });
    </script>
@endsection
