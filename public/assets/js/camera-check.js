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
        this._rafId = null;
        this._destroyed = false;
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
            console.error('[Muraqib] Camera error:', err);
            this.updateStatus('no-camera', 'Camera Access Required — please allow camera access and reload.');
            return;
        }

        this.updateStatus('loading', 'Initialising face detection...');

        try {
            await this.initFaceMesh();
        } catch (err) {
            console.error('[Muraqib] FaceMesh init error:', err);
            this.updateStatus('no-camera', 'FaceMesh error: ' + (err.message || err));
            return;
        }
    }

    async initFaceMesh() {
        const faceMesh = new FaceMesh({
            locateFile: (file) => {
                return 'https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/' + file;
            },
        });

        faceMesh.setOptions({
            maxNumFaces: 1,
            refineLandmarks: true,
            minDetectionConfidence: 0.7,
            minTrackingConfidence: 0.7,
        });

        faceMesh.onResults((results) => this.onResults(results));

        this.faceMesh = faceMesh;

        // Wait for video to actually be playing
        await this._waitForVideo();

        // First send() forces WASM init — await it to catch load errors
        await this.faceMesh.send({ image: this.video });

        // WASM is ready, start continuous loop
        this._sendFrame();
    }

    _waitForVideo() {
        return new Promise((resolve) => {
            const check = () => {
                if (this.video && this.video.videoWidth > 0 && !this.video.paused) {
                    resolve();
                } else {
                    setTimeout(check, 200);
                }
            };
            check();
        });
    }

    async _sendFrame() {
        if (this._destroyed) return;
        try {
            await this.faceMesh.send({ image: this.video });
        } catch (err) {
            // skip frame
        }
        this._rafId = requestAnimationFrame(() => this._sendFrame());
    }

    onResults(results) {
        const ctx = this.canvas.getContext('2d');
        const w = this.video.clientWidth;
        const h = this.video.clientHeight;
        if (this.canvas.width !== w) this.canvas.width = w;
        if (this.canvas.height !== h) this.canvas.height = h;
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
                const lm = results.multiFaceLandmarks[0];
                window.muraqibReferenceLandmarks = lm;

                // Compute and persist reference face ratios for identity check
                const nose = lm[1], leftCheek = lm[234], rightCheek = lm[454];
                const fw = Math.hypot(rightCheek.x - leftCheek.x, rightCheek.y - leftCheek.y);
                if (fw > 0.001) {
                    sessionStorage.setItem('muraqib_ref_face', JSON.stringify({
                        leftRatio: Math.hypot(nose.x - leftCheek.x, nose.y - leftCheek.y) / fw,
                        rightRatio: Math.hypot(nose.x - rightCheek.x, nose.y - rightCheek.y) / fw,
                        noseRatio: (nose.x - leftCheek.x) / (rightCheek.x - leftCheek.x),
                    }));
                }

                // Capture and persist baseline gaze
                if (lm.length >= 478) {
                    const leftIris = lm[468], leftInner = lm[33], leftOuter = lm[133];
                    const rightIris = lm[473], rightInner = lm[362], rightOuter = lm[263];
                    const leftTop = lm[159], leftBottom = lm[145];
                    const rightTop = lm[386], rightBottom = lm[374];

                    const lx = (leftIris.x - leftInner.x) / (leftOuter.x - leftInner.x);
                    const rx = (rightIris.x - rightOuter.x) / (rightInner.x - rightOuter.x);
                    const leftEyeH = Math.abs(leftBottom.y - leftTop.y);
                    const rightEyeH = Math.abs(rightBottom.y - rightTop.y);
                    const ly = leftEyeH > 0.005 ? (leftIris.y - leftTop.y) / leftEyeH : 0.5;
                    const ry = rightEyeH > 0.005 ? (rightIris.y - rightTop.y) / rightEyeH : 0.5;

                    const gaze = { lx, rx, ly, ry };
                    window.muraqibReferenceGaze = gaze;
                    sessionStorage.setItem('muraqib_ref_gaze', JSON.stringify(gaze));
                }

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
