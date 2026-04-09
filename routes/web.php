<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', function () {
    return view('welcome');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Super Admin routes
    Route::prefix('admin')->middleware('role:super_admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/users', function () {
            return view('admin.users.index');
        })->name('admin.users.index');
    });

    // Teacher routes
    Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', function () {
            return view('teacher.dashboard');
        })->name('teacher.dashboard');

        Route::get('/subjects', function () {
            return view('teacher.subjects.index');
        })->name('teacher.subjects.index');

        Route::get('/anticheat-guide', function () {
            return view('teacher.anticheat-guide');
        })->name('teacher.anticheat-guide');
    });

    // Student routes
    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/dashboard', function () {
            return view('student.dashboard');
        })->name('student.dashboard');

        Route::get('/subjects', function () {
            return view('student.subjects.index');
        })->name('student.subjects.index');
    });
});
