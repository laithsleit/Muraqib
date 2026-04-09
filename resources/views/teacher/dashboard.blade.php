@extends('layouts.app')
@section('title', 'Teacher Dashboard — Muraqib')
@section('content')
    <h4 class="fw-bold mb-4">Teacher Dashboard</h4>
    <p class="text-muted">Welcome back, {{ auth()->user()->name }}.</p>
@endsection
