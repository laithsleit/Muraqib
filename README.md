# Muraqib

A web-based quiz platform with real-time anti-cheat monitoring.

Muraqib lets teachers create quizzes, manage subjects, and review attempt results with full visibility into suspicious student behaviour. Before a quiz begins, students must pass a camera check confirming one face is visible. During the quiz the system monitors for things like looking away, tab switching, and multiple faces — each event adds to a score, and if that score crosses the threshold the teacher configured, the attempt gets flagged for review. This is a demo-focused project built with Laravel and Bootstrap.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Demo Accounts](#demo-accounts)
- [How Anti-Cheat Works](#how-anti-cheat-works)
- [Project Structure](#project-structure)
- [Known Limitations](#known-limitations)

## Features

Teachers can manage subjects, enrol students, build multiple-choice quizzes, configure anti-cheat thresholds per quiz, and review flagged attempts with a full event timeline and screenshots.

Students see only their enrolled subjects, must pass a camera check before starting any quiz, and receive their score immediately after submitting. If their attempt was flagged they are informed but the final decision stays with the teacher.

Super admins can create teacher and student accounts, deactivate users, and get a high-level view of activity across the platform.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Blade, Bootstrap 5.3, Vanilla JS |
| Database | MySQL 8 |
| Face Detection | face-api.js (CDN) |
| Roles & Permissions | Laratrust |

## Getting Started

```bash
git clone https://github.com/laithsleit/Muraqib.git
cd Muraqib
cp .env.example .env
# fill in DB_DATABASE, DB_USERNAME, DB_PASSWORD
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Camera access requires a secure context. `localhost` works out of the box. If you deploy to a server, HTTPS is required — browsers block `getUserMedia` on plain HTTP.

Screenshots are stored privately under `storage/app/anticheat/`. Make sure this directory is writable. It is excluded from version control so the folder structure is created by the seeder on first run.

## Demo Accounts

| Role | Email | Password |
|---|---|---|
| Super Admin | admin@quiz.com | password |
| Teacher | teacher@quiz.com | password |
| Student 1 | student1@quiz.com | password |
| Student 2 | student2@quiz.com | password |

## How Anti-Cheat Works

Before a quiz starts, the student's camera is checked for a single visible face. The quiz cannot begin until this check passes — no face, covered camera, or multiple faces all block the start button.

During the quiz, the camera keeps running in a small preview window. Every few seconds the system checks what it sees. Certain behaviours add points to the student's anti-cheat score for that attempt.

| Event | Points |
|---|---|
| Face Not Detected | 10 |
| Multiple Faces | 20 |
| Looking Away | 5 |
| Phone Detected | 25 |
| Tab / Window Switch | 15 |

Each quiz has a score threshold set by the teacher at creation time. If the student's total reaches or exceeds that threshold, the attempt is flagged. Flagging does not automatically penalise the student — it surfaces the attempt in the teacher's reports page for manual review, with a full event timeline and screenshots.

## Project Structure

```
app/
  Actions/
  Http/
    Controllers/
    Requests/
  Models/
config/
  anticheat.php
public/
  assets/
    css/app.css
    js/
      app.js
      anticheat-monitor.js
      camera-check.js
      quiz-timer.js
resources/
  views/
    layouts/
    teacher/
    student/
    admin/
    auth/
routes/
  web.php
database/
  migrations/
  seeders/
storage/
  app/
    anticheat/        <-- screenshots stored here, excluded from git
```

## Known Limitations

- Phone detection (`phone_detected`) is defined in the config but not yet implemented in the client-side monitor. It requires a dedicated object detection model.
- PDF export on the attempt review page is a placeholder.
- There is no email delivery configured by default — password reset emails go to the log file.
- The platform does not support real-time WebSocket notifications. Anti-cheat events are processed via standard HTTP requests.
