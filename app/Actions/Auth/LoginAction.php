<?php

namespace App\Actions\Auth;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginAction
{
    public function execute(LoginRequest $request): string
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return '';
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return 'deactivated';
        }

        $request->session()->regenerate();

        return $this->redirectPath($user);
    }

    private function redirectPath($user): string
    {
        if ($user->hasRole('super_admin')) {
            return '/admin/dashboard';
        }
        if ($user->hasRole('teacher')) {
            return '/teacher/dashboard';
        }
        return '/student/dashboard';
    }
}
