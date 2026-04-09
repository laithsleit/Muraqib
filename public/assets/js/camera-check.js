class CameraCheck {
    constructor({ videoEl, placeholderEl, statusEl, startBtn }) {
        this.video = videoEl;
        this.placeholder = placeholderEl;
        this.status = statusEl;
        this.startBtn = startBtn;
        this.stream = null;
        this.interval = null;
        this.modelsLoaded = false;
    }

    async init() {
        this.updateStatus('loading', 'Requesting camera access...');

        try {
            await this.startStream();
        } catch (err) {
            console.error('Camera access failed:', err);
            this.updateStatus('no-camera', 'Camera Access Required — please allow camera access and reload the page.');
            return;
        }

        this.updateStatus('loading', 'Loading face detection models...');

        try {
            await this.loadModels();
        } catch (err) {
            console.error('Face model load failed:', err);
            this.updateStatus('no-camera', 'Failed to load face detection. Please check your connection and reload.');
            return;
        }

        this.interval = setInterval(() => this.detect(), 1000);
    }

    async startStream() {
        this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
        this.video.srcObject = this.stream;
        this.placeholder.style.display = 'none';
        this.video.style.display = 'block';
    }

    async loadModels() {
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights';
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        this.modelsLoaded = true;
    }

    async detect() {
        if (!this.modelsLoaded || !this.video.videoWidth) return;

        const canvas = document.createElement('canvas');
        canvas.width = this.video.videoWidth;
        canvas.height = this.video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(this.video, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const pixels = imageData.data;
        let totalBrightness = 0;
        for (let i = 0; i < pixels.length; i += 16) {
            totalBrightness += (pixels[i] + pixels[i + 1] + pixels[i + 2]) / 3;
        }
        const avgBrightness = totalBrightness / (pixels.length / 16);

        if (avgBrightness < 20) {
            this.updateStatus('covered', 'Camera appears covered or too dark');
            this.enableStartButton(false);
            return;
        }

        try {
            const detections = await faceapi.detectAllFaces(
                this.video,
                new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
            );

            if (detections.length === 0) {
                this.updateStatus('no-face', 'No face detected — please position yourself in front of the camera');
                this.enableStartButton(false);
            } else if (detections.length > 1) {
                this.updateStatus('multiple', 'Multiple faces detected — only one person is allowed');
                this.enableStartButton(false);
            } else {
                this.updateStatus('ok', 'Camera check passed');
                this.enableStartButton(true);
            }
        } catch (err) {
            console.error('Detection error:', err);
        }
    }

    updateStatus(state, message) {
        const badges = {
            'loading': 'secondary',
            'no-camera': 'danger',
            'covered': 'warning',
            'no-face': 'warning',
            'multiple': 'danger',
            'ok': 'success',
        };
        const icon = state === 'ok'
            ? '<i class="bi bi-check-circle me-1"></i>'
            : state === 'loading'
              ? '<span class="spinner-border spinner-border-sm me-1" role="status"></span>'
              : '<i class="bi bi-exclamation-circle me-1"></i>';
        this.status.innerHTML = `<span class="badge bg-${badges[state] || 'secondary'}">${icon}${message}</span>`;
    }

    enableStartButton(enabled) {
        this.startBtn.disabled = !enabled;
    }
}
