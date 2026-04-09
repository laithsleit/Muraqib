@extends('layouts.app')
@section('title', 'Admin Dashboard — Muraqib')
@section('content')
    <h4 class="fw-bold mb-4">Admin Dashboard</h4>
    <p class="text-muted">Welcome back, {{ auth()->user()->name }}.</p>
@endsection
