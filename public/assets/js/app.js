// Muraqib application JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Bootstrap tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // Initialize Bootstrap toasts
    document.querySelectorAll('.toast').forEach(function (el) {
        new bootstrap.Toast(el).show();
    });
});
