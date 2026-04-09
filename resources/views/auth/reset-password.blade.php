@extends('layouts.auth')

@section('title', 'Reset Password — Muraqib')

@section('content')
    <h5 class="text-center mb-1 fw-bold">Set new password</h5>
    <p class="text-muted text-center small mb-4">Choose a strong password for your account.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email', $email ?? '') }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" placeholder="Min. 8 characters" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repeat your password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Reset Password <i class="bi bi-check-lg ms-1"></i>
        </button>
    </form>
@endsection
