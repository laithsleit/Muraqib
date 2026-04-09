@extends('layouts.auth')

@section('title', 'Forgot Password — Muraqib')

@section('content')
    <h5 class="text-center mb-1 fw-bold">Reset your password</h5>
    <p class="text-muted text-center small mb-4">We'll send you a link to reset your password.</p>

    @if(session('status'))
        <div class="alert alert-success small"><i class="bi bi-check-circle me-1"></i> {{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            Send Reset Link <i class="bi bi-envelope ms-1"></i>
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i> Back to Login</a>
        </div>
    </form>
@endsection
