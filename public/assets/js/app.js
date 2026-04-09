// Muraqib — global Bootstrap initialization

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    document.querySelectorAll('.toast').forEach(function (el) {
        new bootstrap.Toast(el).show();
    });
});
