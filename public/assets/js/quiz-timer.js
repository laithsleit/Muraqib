class QuizTimer {
    constructor({ endTimestamp, displayEl, formEl }) {
        this.endTime = endTimestamp;
        this.display = displayEl;
        this.form = formEl;
        this.interval = null;
        this.expired = false;
    }

    init() {
        this.tick();
        this.interval = setInterval(() => this.tick(), 1000);
    }

    tick() {
        const now = Math.floor(Date.now() / 1000);
        const remaining = this.endTime - now;

        if (remaining <= 0) {
            this.onExpire();
            return;
        }

        this.display.textContent = this.formatTime(remaining);

        if (remaining <= 60) {
            this.display.classList.add('text-danger');
            this.display.classList.remove('text-primary');
        } else {
            this.display.classList.add('text-primary');
            this.display.classList.remove('text-danger');
        }
    }

    formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    onExpire() {
        if (this.expired) return;
        this.expired = true;

        clearInterval(this.interval);
        this.display.textContent = '00:00';
        this.display.classList.add('text-danger');

        this.form.submit();
    }
}
