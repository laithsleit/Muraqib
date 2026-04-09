<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, LoginAction $action)
    {
        $result = $action->execute($request);

        if ($result === '') {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
        }

        if ($result === 'deactivated') {
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
        }

        return redirect($result);
    }

    public function logout(Request $request, LogoutAction $action)
    {
        $action->execute($request);
        return redirect('/');
    }
}
