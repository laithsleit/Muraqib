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
        this.calibEnv = null;

        // Tolerances are computed dynamically from device class + per-user noise.
        // These are placeholders; recomputed in _computeTolerances().
        this.headTolH = 0.22;
        this.headTolUp = 0.12;
        this.headTolDown = 0.22;
        this.irisTolH = 0.18;

        // Sustain windows: how long the user must be looking away before flagging.
        this._gazeAwayStart = null;
        this._gazeSustainMs = 1200;

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

        // Live recalibration if baseline is missing or device changed.
        this._liveCalibSamples = [];
        this._liveCalibTarget = 25;
        this._needsLiveCalib = false;

        // Pause detection briefly after viewport changes (rotation/resize).
        this._pauseUntil = 0;
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
        this._computeTolerances();
        this._bindViewportChange();
        this._startDetection();
        this.initCocoSsd();
        this.bindTabSwitch();
    }

    loadReference() {
        try {
            const gaze = JSON.parse(sessionStorage.getItem('muraqib_ref_gaze'));
            if (gaze) this.baselineGaze = gaze;
        } catch (e) {}

        try {
            const head = JSON.parse(sessionStorage.getItem('muraqib_ref_head'));
            if (head) this.baselineHead = head;
        } catch (e) {}

        try {
            const env = JSON.parse(sessionStorage.getItem('muraqib_ref_env'));
            if (env) this.calibEnv = env;
        } catch (e) {}

        if (!this.baselineHead) {
            this._needsLiveCalib = true;
        } else if (this.calibEnv && this._viewportShifted()) {
            console.log('[Muraqib] Viewport changed since calibration — recalibrating in-place.');
            this._needsLiveCalib = true;
            this.baselineHead = null;
            this.baselineGaze = null;
        }
    }

    _viewportShifted() {
        if (!this.calibEnv) return false;
        const w = window.innerWidth, h = window.innerHeight;
        const dw = Math.abs(w - this.calibEnv.viewportW) / Math.max(1, this.calibEnv.viewportW);
        const dh = Math.abs(h - this.calibEnv.viewportH) / Math.max(1, this.calibEnv.viewportH);
        return dw > 0.20 || dh > 0.20;
    }

    _isMobile() {
        if (this.calibEnv && typeof this.calibEnv.isMobile === 'boolean') return this.calibEnv.isMobile;
        if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) return true;
        if (window.innerWidth <= 820) return true;
        return /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);
    }

    _computeTolerances() {
        const mobile = this._isMobile();

        // Base tolerances per device class. Phones have wider FOV front cameras
        // and the user's head sits closer/at varying angles, so they need more slack.
        const base = mobile
            ? { headH: 0.30, headUp: 0.18, irisH: 0.25 }
            : { headH: 0.22, headUp: 0.13, irisH: 0.18 };

        // Per-user noise: how much the user naturally fidgets while looking
        // at the screen. We scale tolerance to ~3 sigma so calm users get
        // tight detection and fidgety users avoid false positives.
        const headStdX = (this.baselineHead && this.baselineHead.stdX) || 0;
        const headStdY = (this.baselineHead && this.baselineHead.stdY) || 0;
        const irisStdL = (this.baselineGaze && this.baselineGaze.stdLx) || 0;
        const irisStdR = (this.baselineGaze && this.baselineGaze.stdRx) || 0;
        const irisStd = Math.max(irisStdL, irisStdR);

        this.headTolH = Math.max(base.headH, 3 * headStdX);
        this.headTolUp = Math.max(base.headUp, 3 * headStdY);

        // Looking DOWN is normal — the questions are below the camera. Be lenient.
        // The taller the page content vs the viewport, the more downward gaze we expect.
        const docH = Math.max(document.documentElement.scrollHeight, window.innerHeight);
        const vpH = window.innerHeight;
        const contentRatio = Math.min(2.5, docH / Math.max(1, vpH));
        const downBoost = 1.6 + 0.3 * (contentRatio - 1); // 1.6x baseline, more for long pages
        this.headTolDown = this.headTolUp * Math.min(2.5, downBoost);

        this.irisTolH = Math.max(base.irisH, 3 * irisStd);

        console.log('[Muraqib] Tolerances:', {
            mobile, contentRatio: contentRatio.toFixed(2),
            headTolH: this.headTolH.toFixed(3),
            headTolUp: this.headTolUp.toFixed(3),
            headTolDown: this.headTolDown.toFixed(3),
            irisTolH: this.irisTolH.toFixed(3),
            stdX: headStdX.toFixed(3), stdY: headStdY.toFixed(3),
        });
    }

    _bindViewportChange() {
        this._viewportHandler = () => {
            this._pauseUntil = Date.now() + 1500;
            this._gazeAwayStart = null;
            this._computeTolerances();
        };
        window.addEventListener('resize', this._viewportHandler);
        window.addEventListener('orientationchange', this._viewportHandler);
    }

    async _startDetection() {
        try {
            this.faceMesh = new FaceMesh({
                locateFile: (file) => '/assets/mediapipe/face_mesh/' + file,
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

        if (landmarks.length < 478) return;

        if (this._needsLiveCalib) {
            this._collectLiveCalibration(landmarks);
            return;
        }

        if (Date.now() < this._pauseUntil) return;

        this.checkLookingAway(landmarks);
    }

    _collectLiveCalibration(lm) {
        const head = this._extractHead(lm);
        const gaze = this._extractGaze(lm);
        if (!head) return;

        this._liveCalibSamples.push({ head, gaze });
        if (this._liveCalibSamples.length < this._liveCalibTarget) return;

        const headSamples = this._liveCalibSamples.map(s => s.head);
        const gazeSamples = this._liveCalibSamples.map(s => s.gaze).filter(Boolean);

        const headStats = this._stats(headSamples, ['x', 'y']);
        this.baselineHead = {
            x: headStats.mean.x,
            y: headStats.mean.y,
            stdX: headStats.std.x,
            stdY: headStats.std.y,
        };
        if (gazeSamples.length >= this._liveCalibTarget / 2) {
            const gs = this._stats(gazeSamples, ['lx', 'rx']);
            this.baselineGaze = {
                lx: gs.mean.lx, rx: gs.mean.rx,
                stdLx: gs.std.lx, stdRx: gs.std.rx,
            };
        }
        this._needsLiveCalib = false;
        this._liveCalibSamples = [];
        this._computeTolerances();
        console.log('[Muraqib] Live calibration complete', this.baselineHead);
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
        let debug = '';

        const head = this._extractHead(landmarks);
        if (head && this.baselineHead) {
            const dX = head.x - this.baselineHead.x;
            const dY = head.y - this.baselineHead.y;

            if (Math.abs(dX) > this.headTolH) {
                reason = 'head-H';
                debug = `dX=${dX.toFixed(3)} tol=${this.headTolH.toFixed(3)}`;
            } else if (dY < -this.headTolUp) {
                // Negative dY = nose moved up (head tilted up). Strict.
                reason = 'head-up';
                debug = `dY=${dY.toFixed(3)} tol=${this.headTolUp.toFixed(3)}`;
            } else if (dY > this.headTolDown) {
                // Positive dY = head tilted down. Lenient (reading questions).
                reason = 'head-down';
                debug = `dY=${dY.toFixed(3)} tol=${this.headTolDown.toFixed(3)}`;
            }
        }

        if (!reason) {
            const gaze = this._extractGaze(landmarks);
            if (gaze && this.baselineGaze) {
                const dLx = Math.abs(gaze.lx - this.baselineGaze.lx);
                const dRx = Math.abs(gaze.rx - this.baselineGaze.rx);
                // Both eyes must agree to flag — single-eye drift is usually noise.
                if (dLx > this.irisTolH && dRx > this.irisTolH) {
                    reason = 'iris-H';
                    debug = `dL=${dLx.toFixed(3)} dR=${dRx.toFixed(3)} tol=${this.irisTolH.toFixed(3)}`;
                }
            }
        }

        const now = Date.now();
        if (reason) {
            if (!this._gazeAwayStart) {
                this._gazeAwayStart = now;
            }
            if (now - this._gazeAwayStart >= this._gazeSustainMs) {
                console.log(`[Muraqib] LOOKING AWAY: ${reason} | ${debug} | sustained ${now - this._gazeAwayStart}ms`);
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
        if (this._viewportHandler) {
            window.removeEventListener('resize', this._viewportHandler);
            window.removeEventListener('orientationchange', this._viewportHandler);
        }
    }
}
