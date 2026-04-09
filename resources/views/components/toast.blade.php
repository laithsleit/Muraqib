@props(['message' => '', 'type' => 'success'])

@php
    $bgClass = match($type) {
        'danger' => 'text-bg-danger',
        'warning' => 'text-bg-warning',
        default => 'text-bg-success',
    };
    $icon = match($type) {
        'danger' => 'bi-exclamation-circle',
        'warning' => 'bi-exclamation-triangle',
        default => 'bi-check-circle',
    };
@endphp

<div class="toast align-items-center {{ $bgClass }} border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
        <div class="toast-body">
            <i class="bi {{ $icon }} me-1"></i> {{ $message }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
</div>
