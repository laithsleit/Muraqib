<?php

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if ($user?->hasRole('super_admin')) {
                return '/admin/dashboard';
            }

            if ($user?->hasRole('teacher')) {
                return '/teacher/dashboard';
            }

            return '/student/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
