<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\OptionController;
use App\Http\Controllers\Teacher\QuestionController;
use App\Http\Controllers\Teacher\QuizController;
use App\Http\Controllers\Teacher\SubjectController;
use App\Http\Controllers\Teacher\SubjectStudentController;
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
        Route::get('/dashboard', TeacherDashboardController::class)->name('teacher.dashboard');

        // Subjects
        Route::get('/subjects', [SubjectController::class, 'index'])->name('teacher.subjects.index');
        Route::get('/subjects/create', [SubjectController::class, 'create'])->name('teacher.subjects.create');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('teacher.subjects.store');
        Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('teacher.subjects.edit');
        Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('teacher.subjects.update');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('teacher.subjects.destroy');

        // Subject Students
        Route::get('/subjects/{subject}/students', [SubjectStudentController::class, 'index'])->name('teacher.subjects.students');
        Route::post('/subjects/{subject}/students/{student}/enroll', [SubjectStudentController::class, 'enroll'])->name('teacher.subjects.students.enroll');
        Route::post('/subjects/{subject}/students/{student}/unenroll', [SubjectStudentController::class, 'unenroll'])->name('teacher.subjects.students.unenroll');

        // Quizzes
        Route::get('/subjects/{subject}/quizzes', [QuizController::class, 'index'])->name('teacher.quizzes.index');
        Route::get('/subjects/{subject}/quizzes/create', [QuizController::class, 'create'])->name('teacher.quizzes.create');
        Route::post('/subjects/{subject}/quizzes', [QuizController::class, 'store'])->name('teacher.quizzes.store');
        Route::get('/subjects/{subject}/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('teacher.quizzes.edit');
        Route::put('/subjects/{subject}/quizzes/{quiz}', [QuizController::class, 'update'])->name('teacher.quizzes.update');
        Route::post('/subjects/{subject}/quizzes/{quiz}/toggle-publish', [QuizController::class, 'togglePublish'])->name('teacher.quizzes.togglePublish');
        Route::delete('/subjects/{subject}/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('teacher.quizzes.destroy');

        // Questions
        Route::get('/quizzes/{quiz}/questions', [QuestionController::class, 'index'])->name('teacher.questions.index');
        Route::post('/quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('teacher.questions.store');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('teacher.questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('teacher.questions.destroy');
        Route::post('/questions/{question}/move-up', [QuestionController::class, 'moveUp'])->name('teacher.questions.moveUp');
        Route::post('/questions/{question}/move-down', [QuestionController::class, 'moveDown'])->name('teacher.questions.moveDown');

        // Options
        Route::post('/questions/{question}/options', [OptionController::class, 'store'])->name('teacher.options.store');
        Route::put('/options/{option}', [OptionController::class, 'update'])->name('teacher.options.update');
        Route::delete('/options/{option}', [OptionController::class, 'destroy'])->name('teacher.options.destroy');

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
