@extends('layouts.app')
@section('title', 'Student Dashboard — Muraqib')
@section('content')
    <h4 class="fw-bold mb-4">Student Dashboard</h4>
    <p class="text-muted">Welcome back, {{ auth()->user()->name }}.</p>
@endsection
