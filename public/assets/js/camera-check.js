// Pre-quiz camera verification with face landmark overlay and descriptor capture

class CameraCheck {
    constructor({ videoEl, canvasEl, placeholderEl, statusEl, startBtn, modelUrl }) {
        this.video = videoEl;
        this.canvas = canvasEl;
        this.placeholder = placeholderEl;
        this.status = statusEl;
        this.startBtn = startBtn;
        this.modelUrl = modelUrl || '/assets/models';
        this.stream = null;
        this.interval = null;
        this.modelsLoaded = false;
        this.displaySize = null;
    }

    async init() {
        this.updateStatus('loading', 'Requesting camera access...');

        try {
            await this.startStream();
        } catch (err) {
            this.updateStatus('no-camera', 'Camera Access Required — please allow camera access and reload.');
            return;
        }

        this.updateStatus('loading', 'Loading face detection models...');

        try {
            await this.loadModels();
        } catch (err) {
            this.updateStatus('no-camera', 'Failed to load face detection. Please reload.');
            return;
        }

        this.interval = setInterval(() => this.detect(), 800);
    }

    async startStream() {
        this.stream = await navigator.mediaDevices.getUserMedia({ video: true });
        this.video.srcObject = this.stream;
        this.placeholder.style.display = 'none';
        this.video.style.display = 'block';

        await new Promise(resolve => {
            this.video.onloadedmetadata = () => {
                this.video.play();
                resolve();
            };
        });

        // Wait a frame for the video element to have display dimensions
        await new Promise(resolve => requestAnimationFrame(resolve));

        this.displaySize = { width: this.video.clientWidth, height: this.video.clientHeight };
        this.canvas.width = this.displaySize.width;
        this.canvas.height = this.displaySize.height;
    }

    async loadModels() {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(this.modelUrl),
            faceapi.nets.faceLandmark68Net.loadFromUri(this.modelUrl),
            faceapi.nets.faceRecognitionNet.loadFromUri(this.modelUrl),
        ]);
        this.modelsLoaded = true;
    }

    async detect() {
        if (!this.modelsLoaded || !this.video.videoWidth) return;

        const ctx = this.canvas.getContext('2d');
        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        try {
            const detections = await faceapi
                .detectAllFaces(this.video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resized = faceapi.resizeResults(detections, this.displaySize);

            resized.forEach(det => {
                const box = det.detection.box;
                ctx.strokeStyle = '#4f46e5';
                ctx.lineWidth = 2;
                ctx.strokeRect(box.x, box.y, box.width, box.height);

                det.landmarks.positions.forEach(pt => {
                    ctx.beginPath();
                    ctx.arc(pt.x, pt.y, 1.5, 0, 2 * Math.PI);
                    ctx.fillStyle = '#06b6d4';
                    ctx.fill();
                });
            });

            if (detections.length === 0) {
                this.updateStatus('no-face', 'No face detected — position yourself in front of the camera');
                this.enableStartButton(false);
            } else if (detections.length > 1) {
                this.updateStatus('multiple', 'Multiple faces detected — only one person allowed');
                this.enableStartButton(false);
            } else {
                localStorage.setItem('muraqib_face_descriptor', JSON.stringify(Array.from(detections[0].descriptor)));
                this.updateStatus('ok', 'Camera check passed — face registered');
                this.enableStartButton(true);
            }
        } catch (err) {
            this.updateStatus('no-camera', 'Detection error. Please reload.');
            this.enableStartButton(false);
        }
    }

    updateStatus(state, message) {
        const badges = {
            'loading': 'secondary',
            'no-camera': 'danger',
            'no-face': 'warning',
            'multiple': 'danger',
            'ok': 'success',
        };
        const icon = state === 'ok'
            ? '<i class="bi bi-check-circle me-1"></i>'
            : state === 'loading'
              ? '<span class="spinner-border spinner-border-sm me-1"></span>'
              : '<i class="bi bi-exclamation-circle me-1"></i>';
        this.status.innerHTML = `<span class="badge bg-${badges[state] || 'secondary'}">${icon}${message}</span>`;
    }

    enableStartButton(enabled) {
        this.startBtn.disabled = !enabled;
    }
}
