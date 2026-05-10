// Pre-quiz camera verification with MediaPipe FaceMesh overlay and landmark capture

const CALIBRATION_SAMPLES = 40;

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
        this._headSamples = [];
        this._gazeSamples = [];
        this._calibrated = false;
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

        await this._waitForVideo();
        await this.faceMesh.send({ image: this.video });
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
        } catch (err) {}
        this._rafId = requestAnimationFrame(() => this._sendFrame());
    }

    onResults(results) {
        const ctx = this.canvas.getContext('2d');
        const w = this.video.clientWidth;
        const h = this.video.clientHeight;
        if (this.canvas.width !== w) this.canvas.width = w;
        if (this.canvas.height !== h) this.canvas.height = h;
        ctx.clearRect(0, 0, w, h);

        if (!results.multiFaceLandmarks || results.multiFaceLandmarks.length === 0) {
            this._headSamples = [];
            this._gazeSamples = [];
            this.updateStatus('no-face', 'No face detected — position yourself in front of the camera');
            this.enableStartButton(false);
            return;
        }

        if (results.multiFaceLandmarks.length > 1) {
            this._headSamples = [];
            this._gazeSamples = [];
            this.updateStatus('multiple', 'Multiple faces detected — only one person allowed');
            this.enableStartButton(false);
            return;
        }

        const lm = results.multiFaceLandmarks[0];

        for (const landmarks of results.multiFaceLandmarks) {
            drawConnectors(ctx, landmarks, FACEMESH_TESSELATION, {
                color: 'rgba(79, 70, 229, 0.3)',
                lineWidth: 0.5,
            });
            drawConnectors(ctx, landmarks, FACEMESH_FACE_OVAL, {
                color: '#4f46e5',
                lineWidth: 1.5,
            });
            drawConnectors(ctx, landmarks, FACEMESH_RIGHT_IRIS, {
                color: '#06b6d4',
                lineWidth: 1.5,
            });
            drawConnectors(ctx, landmarks, FACEMESH_LEFT_IRIS, {
                color: '#06b6d4',
                lineWidth: 1.5,
            });
        }

        const head = this._extractHead(lm);
        const gaze = this._extractGaze(lm);
        if (!head) return;

        this._headSamples.push(head);
        if (gaze) this._gazeSamples.push(gaze);

        if (this._headSamples.length < CALIBRATION_SAMPLES) {
            const pct = Math.round((this._headSamples.length / CALIBRATION_SAMPLES) * 100);
            this.updateStatus('loading', `Calibrating... ${pct}% — keep looking at the screen`);
            this.enableStartButton(false);
            return;
        }

        if (!this._calibrated) {
            this._saveCalibration();
            this._calibrated = true;
        }

        this.updateStatus('ok', 'Camera check passed — face registered');
        this.enableStartButton(true);
    }

    _extractHead(lm) {
        const nose = lm[1], leftCheek = lm[234], rightCheek = lm[454];
        const forehead = lm[10], chin = lm[152];
        const faceW = Math.abs(rightCheek.x - leftCheek.x);
        const faceH = Math.abs(chin.y - forehead.y);
        if (faceW <= 0.01 || faceH <= 0.01) return null;
        const faceCenterX = (leftCheek.x + rightCheek.x) / 2;
        return {
            x: (nose.x - faceCenterX) / faceW,
            y: (nose.y - forehead.y) / faceH,
        };
    }

    _extractGaze(lm) {
        if (lm.length < 478) return null;
        const leftIris = lm[468], leftInner = lm[33], leftOuter = lm[133];
        const rightIris = lm[473], rightOuter = lm[263], rightInner = lm[362];
        const eyeW_L = Math.abs(leftOuter.x - leftInner.x);
        const eyeW_R = Math.abs(rightInner.x - rightOuter.x);
        if (eyeW_L <= 0.005 || eyeW_R <= 0.005) return null;
        return {
            lx: (leftIris.x - leftInner.x) / eyeW_L,
            rx: (rightIris.x - rightOuter.x) / eyeW_R,
        };
    }

    _saveCalibration() {
        const headStats = this._stats(this._headSamples, ['x', 'y']);
        const head = {
            x: headStats.mean.x,
            y: headStats.mean.y,
            stdX: headStats.std.x,
            stdY: headStats.std.y,
        };
        sessionStorage.setItem('muraqib_ref_head', JSON.stringify(head));

        if (this._gazeSamples.length >= CALIBRATION_SAMPLES / 2) {
            const gazeStats = this._stats(this._gazeSamples, ['lx', 'rx']);
            const gaze = {
                lx: gazeStats.mean.lx,
                rx: gazeStats.mean.rx,
                stdLx: gazeStats.std.lx,
                stdRx: gazeStats.std.rx,
            };
            sessionStorage.setItem('muraqib_ref_gaze', JSON.stringify(gaze));
        }

        const env = {
            viewportW: window.innerWidth,
            viewportH: window.innerHeight,
            screenW: window.screen ? window.screen.width : 0,
            screenH: window.screen ? window.screen.height : 0,
            dpr: window.devicePixelRatio || 1,
            videoW: this.video.videoWidth,
            videoH: this.video.videoHeight,
            isMobile: this._isMobile(),
            timestamp: Date.now(),
        };
        sessionStorage.setItem('muraqib_ref_env', JSON.stringify(env));

        console.log('[Muraqib] Calibration saved:', { head, env });
    }

    _stats(samples, keys) {
        const mean = {}, std = {};
        for (const k of keys) {
            const vals = samples.map(s => s[k]);
            const m = vals.reduce((a, b) => a + b, 0) / vals.length;
            const variance = vals.reduce((a, b) => a + (b - m) ** 2, 0) / vals.length;
            mean[k] = m;
            std[k] = Math.sqrt(variance);
        }
        return { mean, std };
    }

    _isMobile() {
        if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) return true;
        if (window.innerWidth <= 820) return true;
        return /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);
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
