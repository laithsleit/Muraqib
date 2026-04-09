// Real-time anti-cheat monitoring with face recognition and landmark tracking

class AntiCheatMonitor {
    constructor() {
        this.attemptId = null;
        this.videoElement = null;
        this.canvasElement = null;
        this.detectionInterval = 3000;
        this.tabSwitchDebounce = 2000;
        this.screenshotQuality = 0.5;
        this.faceMatchThreshold = 0.55;
        this.reportEndpoint = '';
        this.csrfToken = '';
        this.modelUrl = '/assets/models';
        this.submitEndpoint = '';
        this.resultsEndpoint = '';
        this.lastTabSwitchAt = 0;
        this.intervalHandle = null;
        this.stream = null;
        this.referenceFace = null;
        this.modelsLoaded = false;
    }

    init(config) {
        this.attemptId = config.attemptId;
        this.videoElement = config.videoElement;
        this.canvasElement = config.canvasElement;
        this.reportEndpoint = config.reportEndpoint;
        this.csrfToken = config.csrfToken;
        this.submitEndpoint = config.submitEndpoint;
        this.resultsEndpoint = config.resultsEndpoint;

        if (config.detectionInterval) this.detectionInterval = config.detectionInterval;
        if (config.tabSwitchDebounce) this.tabSwitchDebounce = config.tabSwitchDebounce;
        if (config.screenshotQuality) this.screenshotQuality = config.screenshotQuality;
        if (config.faceMatchThreshold) this.faceMatchThreshold = config.faceMatchThreshold;
        if (config.modelUrl) this.modelUrl = config.modelUrl;

        this.loadReferenceFace();
        this.loadAndStartDetection();
        this.bindTabSwitch();
        this.bindAutoSubmitOnLeave();
    }

    loadReferenceFace() {
        try {
            const stored = localStorage.getItem('muraqib_face_descriptor');
            if (stored) {
                this.referenceFace = new Float32Array(JSON.parse(stored));
            }
        } catch (err) {
            this.referenceFace = null;
        }
    }

    async loadAndStartDetection() {
        try {
            if (typeof faceapi !== 'undefined') {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(this.modelUrl),
                    faceapi.nets.faceLandmark68Net.loadFromUri(this.modelUrl),
                    faceapi.nets.faceRecognitionNet.loadFromUri(this.modelUrl),
                ]);
                this.modelsLoaded = true;
            }
        } catch (err) {
            // Detection runs best-effort
        }
        this.intervalHandle = setInterval(() => this.detect(), this.detectionInterval);
    }

    async detect() {
        if (!this.videoElement || !this.videoElement.videoWidth || !this.modelsLoaded) return;

        try {
            const detections = await faceapi
                .detectAllFaces(this.videoElement, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            this.drawOverlay(detections);

            if (detections.length === 0) {
                this.reportEvent('face_missing');
                return;
            }

            if (detections.length > 1) {
                this.reportEvent('multiple_faces');
                return;
            }

            const detection = detections[0];

            // Face recognition — compare against reference
            if (this.referenceFace) {
                const distance = faceapi.euclideanDistance(detection.descriptor, this.referenceFace);
                if (distance > this.faceMatchThreshold) {
                    this.reportEvent('face_changed');
                    return;
                }
            }

            // Head direction from landmarks — check nose and eye positions
            const landmarks = detection.landmarks;
            const nose = landmarks.getNose();
            const leftEye = landmarks.getLeftEye();
            const rightEye = landmarks.getRightEye();

            const noseX = nose[3].x;
            const eyeCenterX = (leftEye[0].x + rightEye[3].x) / 2;
            const faceWidth = detection.detection.box.width;

            // Horizontal offset of nose from eye center relative to face width
            const horizontalRatio = (noseX - eyeCenterX) / faceWidth;
            // Vertical: if nose tip is too high or low relative to eye line
            const eyeY = (leftEye[0].y + rightEye[3].y) / 2;
            const noseY = nose[6].y;
            const verticalRatio = (noseY - eyeY) / detection.detection.box.height;

            if (Math.abs(horizontalRatio) > 0.15 || verticalRatio < 0.1 || verticalRatio > 0.45) {
                this.reportEvent('looking_away');
            }
        } catch (err) {
            // Never crash the quiz
        }
    }

    drawOverlay(detections) {
        if (!this.canvasElement) return;
        const ctx = this.canvasElement.getContext('2d');
        ctx.clearRect(0, 0, this.canvasElement.width, this.canvasElement.height);

        if (detections.length === 0) return;

        const dims = {
            width: this.canvasElement.width,
            height: this.canvasElement.height,
        };
        const resized = faceapi.resizeResults(detections, dims);
        faceapi.draw.drawFaceLandmarks(this.canvasElement, resized);
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

        if (typeof Swal !== 'undefined') {
            const message = flagged
                ? 'Your attempt has been flagged for review.'
                : 'This activity has been recorded. Please stay focused on your quiz.';

            Swal.fire({
                icon: icons[eventType] || 'warning',
                title: labels[eventType] || eventType,
                text: message,
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 4000,
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

            // Use sendBeacon to submit with whatever answers exist
            const formData = new FormData(document.getElementById('quizForm'));
            navigator.sendBeacon(this.submitEndpoint, formData);
        };

        // Auto-submit when page is actually unloaded (not just hidden)
        window.addEventListener('unload', this._beforeUnloadHandler);
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
        if (this._tabSwitchHandler) {
            document.removeEventListener('visibilitychange', this._tabSwitchHandler);
        }
        if (this._beforeUnloadHandler) {
            window.removeEventListener('unload', this._beforeUnloadHandler);
        }
    }
}
