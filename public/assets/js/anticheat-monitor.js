class AntiCheatMonitor {
    constructor() {
        this.attemptId = null;
        this.videoElement = null;
        this.detectionInterval = 3000;
        this.tabSwitchDebounce = 2000;
        this.screenshotQuality = 0.5;
        this.reportEndpoint = '';
        this.csrfToken = '';
        this.lastTabSwitchAt = 0;
        this.intervalHandle = null;
        this.stream = null;
    }

    init(config) {
        this.attemptId = config.attemptId;
        this.videoElement = config.videoElement;
        this.reportEndpoint = config.reportEndpoint;
        this.csrfToken = config.csrfToken;

        if (config.detectionInterval) this.detectionInterval = config.detectionInterval;
        if (config.tabSwitchDebounce) this.tabSwitchDebounce = config.tabSwitchDebounce;
        if (config.screenshotQuality) this.screenshotQuality = config.screenshotQuality;

        this.startCamera();
        this.startDetection();
        this.bindTabSwitch();
    }

    async startCamera() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
            if (this.videoElement) {
                this.videoElement.srcObject = this.stream;
            }
        } catch (err) {
        }
    }

    startDetection() {
        this.intervalHandle = setInterval(() => this.detect(), this.detectionInterval);
    }

    async detect() {
        if (!this.videoElement || !this.videoElement.videoWidth) return;
        if (typeof faceapi === 'undefined') return;

        try {
            const detections = await faceapi.detectAllFaces(
                this.videoElement,
                new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
            );

            if (detections.length === 0) {
                this.reportEvent('face_missing');
            } else if (detections.length > 1) {
                this.reportEvent('multiple_faces');
            } else {
                const box = detections[0].box;
                const centerX = box.x + box.width / 2;
                const centerY = box.y + box.height / 2;
                const videoW = this.videoElement.videoWidth;
                const videoH = this.videoElement.videoHeight;

                const xRatio = centerX / videoW;
                const yRatio = centerY / videoH;

                if (xRatio < 0.2 || xRatio > 0.8 || yRatio < 0.1 || yRatio > 0.9) {
                    this.reportEvent('looking_away');
                }
            }
        } catch (err) {
        }
    }

    captureScreenshot() {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = this.videoElement.videoWidth;
            canvas.height = this.videoElement.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(this.videoElement, 0, 0);
            return canvas.toDataURL('image/jpeg', this.screenshotQuality);
        } catch (err) {
            return null;
        }
    }

    async reportEvent(eventType) {
        try {
            const screenshot = this.captureScreenshot();
            const response = await fetch(this.reportEndpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    event_type: eventType,
                    screenshot: screenshot,
                    occurred_at: new Date().toISOString(),
                }),
            });

            if (response.ok) {
                const data = await response.json();
                if (data.flagged) {
                    this.showFlaggedToast();
                }
            }
        } catch (err) {
        }
    }

    bindTabSwitch() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                const now = Date.now();
                if (now - this.lastTabSwitchAt >= this.tabSwitchDebounce) {
                    this.lastTabSwitchAt = now;
                    this.reportEvent('tab_switch');
                }
            }
        });
    }

    showFlaggedToast() {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-warning border-0 show';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Your attempt has been flagged for review.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
        container.appendChild(toast);
        new bootstrap.Toast(toast, { delay: 5000 }).show();
    }

    destroy() {
        if (this.intervalHandle) {
            clearInterval(this.intervalHandle);
            this.intervalHandle = null;
        }
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
    }
}
