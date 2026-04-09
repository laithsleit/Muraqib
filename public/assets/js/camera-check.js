// Pre-quiz camera verification with MediaPipe FaceMesh overlay and landmark capture

class CameraCheck {
    constructor({ videoEl, canvasEl, placeholderEl, statusEl, startBtn }) {
        this.video = videoEl;
        this.canvas = canvasEl;
        this.placeholder = placeholderEl;
        this.status = statusEl;
        this.startBtn = startBtn;
        this.stream = null;
        this.faceMesh = null;
        this.camera = null;
    }

    async init() {
        this.updateStatus('loading', 'Requesting camera access...');

        try {
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

            await new Promise(resolve => requestAnimationFrame(resolve));
            this.canvas.width = this.video.clientWidth;
            this.canvas.height = this.video.clientHeight;
        } catch (err) {
            this.updateStatus('no-camera', 'Camera Access Required — please allow camera access and reload.');
            return;
        }

        this.updateStatus('loading', 'Initialising face detection...');

        try {
            await this.initFaceMesh();
        } catch (err) {
            this.updateStatus('no-camera', 'Failed to load face detection. Please reload.');
            return;
        }
    }

    async initFaceMesh() {
        this.faceMesh = new FaceMesh({
            locateFile: (file) => {
                return '/assets/mediapipe/face_mesh/' + file;
            },
        });

        this.faceMesh.setOptions({
            maxNumFaces: 1,
            refineLandmarks: true,
            minDetectionConfidence: 0.7,
            minTrackingConfidence: 0.7,
        });

        this.faceMesh.onResults((results) => this.onResults(results));

        this.camera = new Camera(this.video, {
            onFrame: async () => {
                await this.faceMesh.send({ image: this.video });
            },
            width: this.video.videoWidth,
            height: this.video.videoHeight,
        });

        await this.camera.start();
    }

    onResults(results) {
        const ctx = this.canvas.getContext('2d');
        const w = this.canvas.width;
        const h = this.canvas.height;
        ctx.clearRect(0, 0, w, h);

        if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
            for (const landmarks of results.multiFaceLandmarks) {
                // Draw face mesh tessellation
                drawConnectors(ctx, landmarks, FACEMESH_TESSELATION, {
                    color: 'rgba(79, 70, 229, 0.3)',
                    lineWidth: 0.5,
                });

                // Draw face contours
                drawConnectors(ctx, landmarks, FACEMESH_FACE_OVAL, {
                    color: '#4f46e5',
                    lineWidth: 1.5,
                });

                // Draw iris contours in cyan
                drawConnectors(ctx, landmarks, FACEMESH_RIGHT_IRIS, {
                    color: '#06b6d4',
                    lineWidth: 1.5,
                });
                drawConnectors(ctx, landmarks, FACEMESH_LEFT_IRIS, {
                    color: '#06b6d4',
                    lineWidth: 1.5,
                });
            }

            if (results.multiFaceLandmarks.length === 1) {
                window.muraqibReferenceLandmarks = results.multiFaceLandmarks[0];
                this.updateStatus('ok', 'Camera check passed — face registered');
                this.enableStartButton(true);
            } else {
                this.updateStatus('multiple', 'Multiple faces detected — only one person allowed');
                this.enableStartButton(false);
            }
        } else {
            this.updateStatus('no-face', 'No face detected — position yourself in front of the camera');
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
