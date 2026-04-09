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
        this.referenceNoseRatio = null;
        this.referenceLeftRatio = null;
        this.referenceRightRatio = null;
        this.baselineGaze = null;
        // Tolerance from baseline before "looking away" counts
        this.gazeTolH = 0.24;
        this.gazeTolV = 0.42;
        // Must look away for this many ms before firing event
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
            face_changed: 10000,
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
        this.bindAutoSubmitOnLeave();
    }

    loadReference() {
        // Load face ratios from sessionStorage (persists across page navigation)
        try {
            const face = JSON.parse(sessionStorage.getItem('muraqib_ref_face'));
            if (face) {
                this.referenceLeftRatio = face.leftRatio;
                this.referenceRightRatio = face.rightRatio;
                this.referenceNoseRatio = face.noseRatio;
            }
        } catch (e) {}

        // Load baseline gaze from sessionStorage
        try {
            const gaze = JSON.parse(sessionStorage.getItem('muraqib_ref_gaze'));
            if (gaze) {
                this.baselineGaze = gaze;
                console.log('[Muraqib] Baseline gaze loaded:', gaze);
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

        // Draw everything BEFORE reporting so screenshots include overlays
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
            this.checkGaze(landmarks);
        }

        if (this.referenceNoseRatio !== null) {
            this.checkFaceChanged(landmarks);
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

    checkGaze(landmarks) {
        // Horizontal iris offsets
        const leftIris = landmarks[468], leftInner = landmarks[33], leftOuter = landmarks[133];
        const rightIris = landmarks[473], rightInner = landmarks[362], rightOuter = landmarks[263];
        const lx = (leftIris.x - leftInner.x) / (leftOuter.x - leftInner.x);
        const rx = (rightIris.x - rightOuter.x) / (rightInner.x - rightOuter.x);

        // Vertical iris offsets — skip if eye height is too small (blink/squint)
        const leftTop = landmarks[159], leftBottom = landmarks[145];
        const rightTop = landmarks[386], rightBottom = landmarks[374];
        const leftEyeH = Math.abs(leftBottom.y - leftTop.y);
        const rightEyeH = Math.abs(rightBottom.y - rightTop.y);
        const minEyeH = 0.008;
        const eyesOpen = leftEyeH > minEyeH && rightEyeH > minEyeH;

        let ly = 0.5, ry = 0.5;
        if (eyesOpen) {
            ly = Math.max(0, Math.min(1, (leftIris.y - leftTop.y) / leftEyeH));
            ry = Math.max(0, Math.min(1, (rightIris.y - rightTop.y) / rightEyeH));
        }

        let isAway = false;

        if (this.baselineGaze) {
            const dLx = Math.abs(lx - this.baselineGaze.lx);
            const dRx = Math.abs(rx - this.baselineGaze.rx);
            const awayH = dLx > this.gazeTolH && dRx > this.gazeTolH;

            let awayV = false;
            if (eyesOpen) {
                const dLy = Math.abs(ly - this.baselineGaze.ly);
                const dRy = Math.abs(ry - this.baselineGaze.ry);
                awayV = dLy > this.gazeTolV && dRy > this.gazeTolV;
            }

            isAway = awayH || awayV;
        } else {
            const awayH = (lx < 0.20 || lx > 0.80) && (rx < 0.20 || rx > 0.80);
            let awayV = false;
            if (eyesOpen) {
                awayV = (ly < 0.10 || ly > 0.90) && (ry < 0.10 || ry > 0.90);
            }
            isAway = awayH || awayV;
        }

        // Sustained check — must look away for 1 second before firing
        const now = Date.now();
        if (isAway) {
            if (!this._gazeAwayStart) {
                this._gazeAwayStart = now;
            }
            if (now - this._gazeAwayStart >= this._gazeSustainMs) {
                if (this.baselineGaze) {
                    const dLx = Math.abs(lx - this.baselineGaze.lx);
                    const dRx = Math.abs(rx - this.baselineGaze.rx);
                    const dLy = Math.abs(ly - this.baselineGaze.ly);
                    const dRy = Math.abs(ry - this.baselineGaze.ry);
                    console.log(`[Muraqib Gaze] FIRED | H: dL=${dLx.toFixed(3)} dR=${dRx.toFixed(3)} (tol=${this.gazeTolH}) | V: dL=${dLy.toFixed(3)} dR=${dRy.toFixed(3)} (tol=${this.gazeTolV}) | sustained ${now - this._gazeAwayStart}ms`);
                } else {
                    console.log(`[Muraqib Gaze] FIRED (no baseline) | lx=${lx.toFixed(3)} rx=${rx.toFixed(3)} ly=${ly.toFixed(3)} ry=${ry.toFixed(3)} | sustained ${now - this._gazeAwayStart}ms`);
                }
                this.reportEvent('looking_away');
                this._gazeAwayStart = null;
            }
        } else {
            this._gazeAwayStart = null;
        }
    }

    checkFaceChanged(landmarks) {
        const nose = landmarks[1];
        const leftCheek = landmarks[234];
        const rightCheek = landmarks[454];
        const faceWidth = Math.hypot(rightCheek.x - leftCheek.x, rightCheek.y - leftCheek.y);
        if (faceWidth < 0.001) return;

        const noseToLeft = Math.hypot(nose.x - leftCheek.x, nose.y - leftCheek.y);
        const noseToRight = Math.hypot(nose.x - rightCheek.x, nose.y - rightCheek.y);
        const currentLeftRatio = noseToLeft / faceWidth;
        const currentRightRatio = noseToRight / faceWidth;

        const leftDiff = Math.abs(currentLeftRatio - this.referenceLeftRatio);
        const rightDiff = Math.abs(currentRightRatio - this.referenceRightRatio);

        if (leftDiff > 0.12 || rightDiff > 0.12) {
            this.reportEvent('face_changed');
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

            const secondary = predictions.filter(
                p => (p.class === 'laptop' || p.class === 'book') && p.score > 0.6
            );
            if (secondary.length > 0) {
                console.log('[Muraqib] Secondary objects detected:', secondary.map(p => p.class));
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
            'face_changed': 'Different Face Detected',
            'phone_detected': 'Phone Detected',
            'tab_switch': 'Tab Switch Detected',
        };

        const icons = {
            'face_missing': 'warning',
            'multiple_faces': 'error',
            'looking_away': 'info',
            'face_changed': 'error',
            'phone_detected': 'error',
            'tab_switch': 'warning',
        };

        const longTimerTypes = ['face_changed', 'phone_detected'];

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
                timer: longTimerTypes.includes(eventType) ? 6000 : 4000,
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

    bindAutoSubmitOnLeave() {
        this._beforeUnloadHandler = () => {
            if (!this.submitEndpoint) return;
            const formData = new FormData(document.getElementById('quizForm'));
            navigator.sendBeacon(this.submitEndpoint, formData);
        };
        window.addEventListener('unload', this._beforeUnloadHandler);
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
        if (this._beforeUnloadHandler) {
            window.removeEventListener('unload', this._beforeUnloadHandler);
        }
    }
}
