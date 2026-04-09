// Real-time anti-cheat monitoring with MediaPipe FaceMesh and COCO-SSD phone detection

class AntiCheatMonitor {
    constructor() {
        this.attemptId = null;
        this.videoElement = null;
        this.canvasElement = null;
        this.faceMesh = null;
        this.cocoModel = null;
        this._rafId = null;
        this._destroyed = false;
        this.baselineGaze = null;
        this.baselineHead = null;
        // How far iris can deviate from baseline before counting as "eyes looking away"
        this.irisToleranceH = 0.15;
        // How far head (nose) can rotate from baseline before counting as "head turned"
        this.headToleranceH = 0.12;
        this.headToleranceV = 0.10;
        // Must be looking away for 1s before firing
        this._gazeAwayStart = null;
        this._gazeSustainMs = 1000;
        this.reportEndpoint = '';
        this.csrfToken = '';
        this.submitEndpoint = '';
        this.resultsEndpoint = '';
        this.screenshotQuality = 0.5;
        this.lastTabSwitchAt = 0;
        this.tabSwitchDebounce = 2000;
        this.phoneDetectionInterval = null;
        this.stream = null;

        this.lastEventTimes = {};
        this.eventDebounce = {
            face_missing: 10000,
            multiple_faces: 5000,
            looking_away: 10000,
            phone_detected: 5000,
        };
        this._phoneBoxes = [];
    }

    init(config) {
        this.attemptId = config.attemptId;
        this.videoElement = config.videoElement;
        this.canvasElement = config.canvasElement;
        this.reportEndpoint = config.reportEndpoint;
        this.csrfToken = config.csrfToken;
        this.submitEndpoint = config.submitEndpoint;
        this.resultsEndpoint = config.resultsEndpoint;
        if (config.screenshotQuality) this.screenshotQuality = config.screenshotQuality;

        this.loadReference();
        this._startDetection();
        this.initCocoSsd();
        this.bindTabSwitch();
    }

    loadReference() {
        try {
            const gaze = JSON.parse(sessionStorage.getItem('muraqib_ref_gaze'));
            if (gaze) {
                this.baselineGaze = gaze;
                console.log('[Muraqib] Baseline gaze loaded:', gaze);
            }
        } catch (e) {}

        try {
            const head = JSON.parse(sessionStorage.getItem('muraqib_ref_head'));
            if (head) {
                this.baselineHead = head;
                console.log('[Muraqib] Baseline head loaded:', head);
            }
        } catch (e) {}
    }

    async _startDetection() {
        try {
            this.faceMesh = new FaceMesh({
                locateFile: (file) => {
                    return 'https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/' + file;
                },
            });

            this.faceMesh.setOptions({
                maxNumFaces: 2,
                refineLandmarks: true,
                minDetectionConfidence: 0.7,
                minTrackingConfidence: 0.7,
            });

            this.faceMesh.onResults((results) => this.onFaceResults(results));

            await this._waitForVideo();
            await this.faceMesh.send({ image: this.videoElement });
            this._sendFrame();
        } catch (err) {
            console.warn('[Muraqib] FaceMesh init failed:', err);
        }
    }

    _waitForVideo() {
        return new Promise((resolve) => {
            const check = () => {
                if (this.videoElement && this.videoElement.videoWidth > 0 && !this.videoElement.paused) {
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
            await this.faceMesh.send({ image: this.videoElement });
        } catch (err) {}
        this._rafId = requestAnimationFrame(() => this._sendFrame());
    }

    async initCocoSsd() {
        try {
            this.cocoModel = await cocoSsd.load({
                modelUrl: '/assets/models/coco-ssd/model.json',
            });
            this.phoneDetectionInterval = setInterval(() => this.detectPhone(), 3000);
        } catch (err) {
            try {
                this.cocoModel = await cocoSsd.load();
                this.phoneDetectionInterval = setInterval(() => this.detectPhone(), 3000);
            } catch (e) {}
        }
    }

    onFaceResults(results) {
        const ctx = this.canvasElement.getContext('2d');
        const vw = this.videoElement.clientWidth;
        const vh = this.videoElement.clientHeight;
        if (this.canvasElement.width !== vw) this.canvasElement.width = vw;
        if (this.canvasElement.height !== vh) this.canvasElement.height = vh;
        ctx.clearRect(0, 0, vw, vh);

        const faces = results.multiFaceLandmarks || [];

        for (const lm of faces) {
            this.drawFaceMesh(ctx, lm);
        }
        this.drawPhoneBoxes(ctx);

        if (faces.length === 0) {
            this._gazeAwayStart = null;
            this.reportEvent('face_missing');
            return;
        }

        if (faces.length > 1) {
            this._gazeAwayStart = null;
            this.reportEvent('multiple_faces');
            return;
        }

        const landmarks = faces[0];

        if (landmarks.length >= 478) {
            this.checkLookingAway(landmarks);
        }
    }

    drawFaceMesh(ctx, landmarks) {
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

    checkLookingAway(landmarks) {
        let reason = null;

        // --- HEAD ROTATION (face turning) ---
        const nose = landmarks[1];
        const leftCheek = landmarks[234];
        const rightCheek = landmarks[454];
        const forehead = landmarks[10];
        const chin = landmarks[152];

        const faceCenterX = (leftCheek.x + rightCheek.x) / 2;
        const faceW = Math.abs(rightCheek.x - leftCheek.x);
        const faceH = Math.abs(chin.y - forehead.y);

        if (faceW > 0.01 && faceH > 0.01) {
            const headX = (nose.x - faceCenterX) / faceW;
            const headY = (nose.y - forehead.y) / faceH;

            if (this.baselineHead) {
                const dX = Math.abs(headX - this.baselineHead.x);
                const dY = Math.abs(headY - this.baselineHead.y);
                if (dX > this.headToleranceH) reason = 'head-H';
                if (dY > this.headToleranceV) reason = 'head-V';
            } else {
                // No baseline — use absolute: nose should be roughly centered
                if (Math.abs(headX) > 0.15) reason = 'head-H';
            }
        }

        // --- IRIS GAZE (eyes moving while face is still) ---
        if (!reason) {
            const leftIris = landmarks[468], leftInner = landmarks[33], leftOuter = landmarks[133];
            const rightIris = landmarks[473], rightInner = landmarks[362], rightOuter = landmarks[263];

            const eyeW_L = Math.abs(leftOuter.x - leftInner.x);
            const eyeW_R = Math.abs(rightInner.x - rightOuter.x);

            if (eyeW_L > 0.005 && eyeW_R > 0.005) {
                const lx = (leftIris.x - leftInner.x) / eyeW_L;
                const rx = (rightIris.x - rightOuter.x) / eyeW_R;

                if (this.baselineGaze) {
                    const dLx = Math.abs(lx - this.baselineGaze.lx);
                    const dRx = Math.abs(rx - this.baselineGaze.rx);
                    if (dLx > this.irisToleranceH && dRx > this.irisToleranceH) {
                        reason = 'iris-H';
                    }
                }
            }
        }

        // --- SUSTAINED CHECK ---
        const now = Date.now();
        if (reason) {
            if (!this._gazeAwayStart) {
                this._gazeAwayStart = now;
            }
            if (now - this._gazeAwayStart >= this._gazeSustainMs) {
                console.log(`[Muraqib] LOOKING AWAY: ${reason} | sustained ${now - this._gazeAwayStart}ms`);
                this.reportEvent('looking_away');
                this._gazeAwayStart = null;
            }
        } else {
            this._gazeAwayStart = null;
        }
    }

    async detectPhone() {
        if (!this.cocoModel || !this.videoElement || !this.videoElement.videoWidth) return;

        try {
            const predictions = await this.cocoModel.detect(this.videoElement);
            const phones = predictions.filter(
                p => (p.class === 'cell phone' || p.class === 'remote') && p.score > 0.4
            );

            const vw = this.videoElement.clientWidth;
            const vh = this.videoElement.clientHeight;
            const vidW = this.videoElement.videoWidth;
            const vidH = this.videoElement.videoHeight;
            this._phoneBoxes = phones.map(p => ({
                x: p.bbox[0] * (vw / vidW),
                y: p.bbox[1] * (vh / vidH),
                w: p.bbox[2] * (vw / vidW),
                h: p.bbox[3] * (vh / vidH),
                score: p.score,
                label: p.class,
            }));
            this._phoneBoxesNative = phones.map(p => ({
                x: p.bbox[0],
                y: p.bbox[1],
                w: p.bbox[2],
                h: p.bbox[3],
                score: p.score,
                label: p.class,
            }));

            if (phones.length > 0) {
                const ctx = this.canvasElement.getContext('2d');
                this.drawPhoneBoxes(ctx);
                this.reportEvent('phone_detected');
                setTimeout(() => { this._phoneBoxes = []; }, 3000);
            } else {
                this._phoneBoxes = [];
            }
        } catch (err) {}
    }

    drawPhoneBoxes(ctx) {
        for (const box of this._phoneBoxes) {
            ctx.strokeStyle = '#ef4444';
            ctx.lineWidth = 2;
            ctx.setLineDash([6, 3]);
            ctx.strokeRect(box.x, box.y, box.w, box.h);
            ctx.setLineDash([]);

            const label = (box.label === 'remote' ? 'Phone/Remote' : 'Phone')
                + ' ' + Math.round(box.score * 100) + '%';
            ctx.fillStyle = 'rgba(239, 68, 68, 0.7)';
            ctx.font = 'bold 11px sans-serif';
            const tw = ctx.measureText(label).width;
            ctx.fillRect(box.x, box.y - 16, tw + 8, 16);
            ctx.fillStyle = '#fff';
            ctx.fillText(label, box.x + 4, box.y - 4);
        }
    }

    captureScreenshot() {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = this.videoElement.videoWidth;
            canvas.height = this.videoElement.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(this.videoElement, 0, 0);
            ctx.drawImage(this.canvasElement, 0, 0, canvas.width, canvas.height);
            if (this._phoneBoxesNative && this._phoneBoxesNative.length > 0) {
                for (const box of this._phoneBoxesNative) {
                    ctx.strokeStyle = '#ef4444';
                    ctx.lineWidth = 3;
                    ctx.setLineDash([8, 4]);
                    ctx.strokeRect(box.x, box.y, box.w, box.h);
                    ctx.setLineDash([]);

                    ctx.fillStyle = 'rgba(239, 68, 68, 0.8)';
                    ctx.font = 'bold 16px sans-serif';
                    const label = (box.label === 'remote' ? 'Phone/Remote' : 'Phone')
                        + ' ' + Math.round(box.score * 100) + '%';
                    const tw = ctx.measureText(label).width;
                    ctx.fillRect(box.x, box.y - 22, tw + 10, 22);
                    ctx.fillStyle = '#fff';
                    ctx.fillText(label, box.x + 5, box.y - 5);
                }
            }
            return canvas.toDataURL('image/jpeg', this.screenshotQuality);
        } catch (err) {
            return null;
        }
    }

    async reportEvent(eventType) {
        const now = Date.now();
        const debounce = this.eventDebounce[eventType];
        if (debounce && this.lastEventTimes[eventType] && (now - this.lastEventTimes[eventType] < debounce)) {
            return;
        }
        this.lastEventTimes[eventType] = now;

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
                this.showEventAlert(eventType, data.flagged);
            }
        } catch (err) {}
    }

    showEventAlert(eventType, flagged) {
        const labels = {
            'face_missing': 'Face Not Detected',
            'multiple_faces': 'Multiple Faces Detected',
            'looking_away': 'Looking Away',
            'phone_detected': 'Phone Detected',
            'tab_switch': 'Tab Switch Detected',
        };

        const icons = {
            'face_missing': 'warning',
            'multiple_faces': 'error',
            'looking_away': 'info',
            'phone_detected': 'error',
            'tab_switch': 'warning',
        };

        if (typeof Swal !== 'undefined') {
            const message = flagged
                ? 'Your attempt has been flagged.'
                : 'This has been recorded. Stay focused.';

            Swal.fire({
                icon: icons[eventType] || 'warning',
                title: labels[eventType] || eventType,
                text: message,
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: eventType === 'phone_detected' ? 6000 : 4000,
                timerProgressBar: true,
            });
        }
    }

    bindTabSwitch() {
        this._tabSwitchHandler = () => {
            if (document.hidden) {
                const now = Date.now();
                if (now - this.lastTabSwitchAt >= this.tabSwitchDebounce) {
                    this.lastTabSwitchAt = now;
                    this.reportEvent('tab_switch');
                }
            }
        };
        document.addEventListener('visibilitychange', this._tabSwitchHandler);
    }

    destroy() {
        this._destroyed = true;
        if (this._rafId) {
            cancelAnimationFrame(this._rafId);
            this._rafId = null;
        }
        if (this.phoneDetectionInterval) {
            clearInterval(this.phoneDetectionInterval);
            this.phoneDetectionInterval = null;
        }
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this._tabSwitchHandler) {
            document.removeEventListener('visibilitychange', this._tabSwitchHandler);
        }
    }
}
