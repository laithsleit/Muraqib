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
            face_missing: 5000,
            multiple_faces: 5000,
            looking_away: 8000,
            face_changed: 10000,
            phone_detected: 15000,
        };
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
        this.initFaceMesh();
        this.initCocoSsd();
        this.bindTabSwitch();
        this.bindAutoSubmitOnLeave();
    }

    loadReference() {
        const ref = window.muraqibReferenceLandmarks;
        if (!ref || ref.length < 468) return;

        const nose = ref[1];
        const leftCheek = ref[234];
        const rightCheek = ref[454];
        const faceWidth = Math.hypot(rightCheek.x - leftCheek.x, rightCheek.y - leftCheek.y);
        if (faceWidth < 0.001) return;

        const noseToLeft = Math.hypot(nose.x - leftCheek.x, nose.y - leftCheek.y);
        const noseToRight = Math.hypot(nose.x - rightCheek.x, nose.y - rightCheek.y);
        this.referenceLeftRatio = noseToLeft / faceWidth;
        this.referenceRightRatio = noseToRight / faceWidth;
        this.referenceNoseRatio = (nose.x - leftCheek.x) / (rightCheek.x - leftCheek.x);
    }

    async initFaceMesh() {
        try {
            this.faceMesh = new FaceMesh({
                locateFile: (file) => {
                    return '/assets/mediapipe/face_mesh/' + file;
                },
            });

            this.faceMesh.setOptions({
                maxNumFaces: 2,
                refineLandmarks: true,
                minDetectionConfidence: 0.7,
                minTrackingConfidence: 0.7,
            });

            this.faceMesh.onResults((results) => this.onFaceResults(results));

            // Use requestAnimationFrame loop instead of Camera helper
            // because the video stream is already running from the page script
            this._sendFrame();
        } catch (err) {
            // Face detection runs best-effort
        }
    }

    async _sendFrame() {
        if (this._destroyed) return;
        if (this.faceMesh && this.videoElement && this.videoElement.videoWidth > 0) {
            try {
                await this.faceMesh.send({ image: this.videoElement });
            } catch (err) {
                // Skip frame on error
            }
        }
        this._rafId = requestAnimationFrame(() => this._sendFrame());
    }

    async initCocoSsd() {
        try {
            this.cocoModel = await cocoSsd.load({
                modelUrl: '/assets/models/coco-ssd/model.json',
            });
            this.phoneDetectionInterval = setInterval(() => this.detectPhone(), 5000);
        } catch (err) {
            // Phone detection runs best-effort; try CDN fallback
            try {
                this.cocoModel = await cocoSsd.load();
                this.phoneDetectionInterval = setInterval(() => this.detectPhone(), 5000);
            } catch (e) {
                // No phone detection available
            }
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

        if (faces.length === 0) {
            this.reportEvent('face_missing');
            return;
        }

        if (faces.length > 1) {
            this.reportEvent('multiple_faces');
            // Still draw all faces
            for (const landmarks of faces) {
                this.drawFaceMesh(ctx, landmarks);
            }
            return;
        }

        // Exactly one face
        const landmarks = faces[0];
        this.drawFaceMesh(ctx, landmarks);

        // Looking away — iris-based gaze estimation
        if (landmarks.length >= 478) {
            this.checkGaze(landmarks);
        }

        // Face changed — structural comparison
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
        // Left eye: iris center 468, corners 33 (inner) and 133 (outer)
        const leftIris = landmarks[468];
        const leftInner = landmarks[33];
        const leftOuter = landmarks[133];
        const leftOffsetX = (leftIris.x - leftInner.x) / (leftOuter.x - leftInner.x);

        // Right eye: iris center 473, corners 362 (inner) and 263 (outer)
        const rightIris = landmarks[473];
        const rightInner = landmarks[362];
        const rightOuter = landmarks[263];
        const rightOffsetX = (rightIris.x - rightOuter.x) / (rightInner.x - rightOuter.x);

        const lookingAwayH = (leftOffsetX < 0.35 || leftOffsetX > 0.65) &&
                             (rightOffsetX < 0.35 || rightOffsetX > 0.65);

        // Vertical check — iris Y relative to eye top/bottom
        const leftTop = landmarks[159];
        const leftBottom = landmarks[145];
        const leftEyeH = Math.abs(leftBottom.y - leftTop.y);
        const leftOffsetY = leftEyeH > 0.001
            ? (leftIris.y - leftTop.y) / leftEyeH
            : 0.5;

        const rightTop = landmarks[386];
        const rightBottom = landmarks[374];
        const rightEyeH = Math.abs(rightBottom.y - rightTop.y);
        const rightOffsetY = rightEyeH > 0.001
            ? (rightIris.y - rightTop.y) / rightEyeH
            : 0.5;

        const lookingAwayV = (leftOffsetY < 0.2 || leftOffsetY > 0.8) &&
                             (rightOffsetY < 0.2 || rightOffsetY > 0.8);

        if (lookingAwayH || lookingAwayV) {
            this.reportEvent('looking_away');
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
            const phones = predictions.filter(p => p.class === 'cell phone' && p.score > 0.6);

            if (phones.length > 0) {
                this.reportEvent('phone_detected');
            }

            // Log secondary suspicious objects to console only
            const secondary = predictions.filter(
                p => (p.class === 'laptop' || p.class === 'book') && p.score > 0.6
            );
            if (secondary.length > 0) {
                console.log('[Muraqib] Secondary objects detected:', secondary.map(p => p.class));
            }
        } catch (err) {
            // Never crash the quiz
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
        } catch (err) {
            // Fail silently
        }
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
