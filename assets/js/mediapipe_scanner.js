/**
 * SmartFit AI - Enhanced MediaPipe Pose Detection & Distance-Invariant Anthropometric Scanner
 */

const SmartFitScanner = {
    videoElement: null,
    canvasElement: null,
    canvasCtx: null,
    pose: null,
    camera: null,
    isScanning: false,
    capturedLandmarks: null,
    capturedImageSrc: null,
    selectedGender: 'mens',
    userHeightCm: 173.0,
    userBuild: 'regular',      // 'slim', 'regular', 'athletic', 'plus'
    fitPreference: 'regular',  // 'snug', 'regular', 'oversized'
    stableFrameCount: 0,
    countdownTimer: null,
    countdownValue: 3,
    isCountingDown: false,

    init: function() {
        this.videoElement = document.getElementById('webcamVideo');
        this.canvasElement = document.getElementById('poseCanvas');
        if (this.canvasElement) {
            this.canvasCtx = this.canvasElement.getContext('2d');
        }

        // Read user height from input if exists
        const heightInput = document.getElementById('scannerUserHeight');
        if (heightInput) {
            this.userHeightCm = parseFloat(heightInput.value) || (this.selectedGender === 'womens' ? 162.0 : 173.0);
        }
    },

    setGender: function(gender) {
        this.selectedGender = gender;
        const btns = document.querySelectorAll('.scanner-gender-btn');
        btns.forEach(btn => {
            if (btn.dataset.gender === gender) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Set default height according to gender
        const heightInput = document.getElementById('scannerUserHeight');
        if (heightInput && !heightInput.dataset.userEdited) {
            this.userHeightCm = gender === 'womens' ? 162.0 : 173.0;
            heightInput.value = this.userHeightCm;
        }
    },

    setUserHeight: function(val) {
        this.userHeightCm = parseFloat(val) || 172.0;
        const heightInput = document.getElementById('scannerUserHeight');
        if (heightInput) heightInput.dataset.userEdited = "true";
    },

    setUserBuild: function(build) {
        this.userBuild = build;
        document.querySelectorAll('.scanner-build-btn').forEach(b => {
            if (b.dataset.build === build) b.classList.add('active');
            else b.classList.remove('active');
        });
    },

    setFitPreference: function(fit) {
        this.fitPreference = fit;
        document.querySelectorAll('.scanner-fit-btn').forEach(b => {
            if (b.dataset.fit === fit) b.classList.add('active');
            else b.classList.remove('active');
        });
    },

    startCamera: async function() {
        this.init();
        if (!this.videoElement || !this.canvasElement) return;

        this.isScanning = true;
        this.stableFrameCount = 0;
        this.isCountingDown = false;
        this.updateStatus('Initializing AI Pose Model...', 'info');

        try {
            if (typeof Pose !== 'undefined') {
                this.pose = new Pose({
                    locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/pose/${file}`
                });

                this.pose.setOptions({
                    modelComplexity: 1,
                    smoothLandmarks: true,
                    enableSegmentation: false,
                    smoothSegmentation: false,
                    minDetectionConfidence: 0.5,
                    minTrackingConfidence: 0.5
                });

                this.pose.onResults((results) => this.onPoseResults(results));
            }

            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                },
                audio: false
            });

            this.videoElement.srcObject = stream;
            await this.videoElement.play();

            this.canvasElement.width = this.videoElement.videoWidth || 640;
            this.canvasElement.height = this.videoElement.videoHeight || 480;

            if (typeof Camera !== 'undefined' && this.pose) {
                this.camera = new Camera(this.videoElement, {
                    onFrame: async () => {
                        if (this.isScanning && this.pose) {
                            await this.pose.send({ image: this.videoElement });
                        }
                    },
                    width: 640,
                    height: 480
                });
                this.camera.start();
            } else {
                this.requestFrameLoop();
            }

            this.updateStatus('Stand straight with shoulders & waist visible', 'warning');

        } catch (err) {
            console.warn('Camera error or unsupported:', err);
            this.updateStatus('Camera not available. Use Manual Input or Simulation Mode.', 'danger');
            const simBtn = document.getElementById('simulatedScanBtn');
            if (simBtn) simBtn.classList.remove('d-none');
        }
    },

    requestFrameLoop: async function() {
        if (!this.isScanning) return;
        if (this.pose && this.videoElement && this.videoElement.readyState >= 2) {
            await this.pose.send({ image: this.videoElement });
        }
        requestAnimationFrame(() => this.requestFrameLoop());
    },

    stopCamera: function() {
        this.isScanning = false;
        this.isCountingDown = false;
        if (this.countdownTimer) clearInterval(this.countdownTimer);

        if (this.videoElement && this.videoElement.srcObject) {
            const stream = this.videoElement.srcObject;
            const tracks = stream.getTracks();
            tracks.forEach(track => track.stop());
            this.videoElement.srcObject = null;
        }

        if (this.canvasCtx && this.canvasElement) {
            this.canvasCtx.clearRect(0, 0, this.canvasElement.width, this.canvasElement.height);
        }
    },

    onPoseResults: function(results) {
        if (!this.isScanning || !this.canvasCtx) return;

        this.canvasCtx.save();
        this.canvasCtx.clearRect(0, 0, this.canvasElement.width, this.canvasElement.height);

        if (results.poseLandmarks) {
            const landmarks = results.poseLandmarks;

            // Draw skeleton landmarks with glowing cyber style
            if (typeof drawConnectors !== 'undefined' && typeof POSE_CONNECTIONS !== 'undefined') {
                drawConnectors(this.canvasCtx, landmarks, POSE_CONNECTIONS, {
                    color: '#6366f1',
                    lineWidth: 3
                });
            }

            if (typeof drawLandmarks !== 'undefined') {
                drawLandmarks(this.canvasCtx, landmarks, {
                    color: '#06b6d4',
                    lineWidth: 1,
                    radius: 4
                });
            }

            // Key Landmarks: Shoulders (11, 12), Hips (23, 24), Nose (0)
            const leftSh = landmarks[11];
            const rightSh = landmarks[12];
            const leftHip = landmarks[23];
            const rightHip = landmarks[24];

            const isVisible = (lm) => lm && lm.visibility > 0.60;

            if (isVisible(leftSh) && isVisible(rightSh) && isVisible(leftHip) && isVisible(rightHip)) {
                // Orientation check: ensure user is facing the camera directly
                const zDiff = Math.abs(leftSh.z - rightSh.z);
                if (zDiff > 0.18) {
                    this.stableFrameCount = 0;
                    if (this.isCountingDown) this.cancelAutoCapture();
                    this.updateStatus('Please face camera directly (straight posture)', 'warning');
                    this.canvasCtx.restore();
                    return;
                }

                this.stableFrameCount++;

                // Draw shoulder measurement line in bright neon emerald
                const w = this.canvasElement.width;
                const h = this.canvasElement.height;
                this.canvasCtx.beginPath();
                this.canvasCtx.moveTo(leftSh.x * w, leftSh.y * h);
                this.canvasCtx.lineTo(rightSh.x * w, rightSh.y * h);
                this.canvasCtx.strokeStyle = '#10b981';
                this.canvasCtx.lineWidth = 4;
                this.canvasCtx.stroke();

                if (!this.isCountingDown && this.stableFrameCount > 12) {
                    this.startAutoCapture(landmarks);
                }
            } else {
                this.stableFrameCount = 0;
                if (this.isCountingDown) {
                    this.cancelAutoCapture();
                }
                this.updateStatus('Step back slightly: Keep shoulders & hips inside frame', 'warning');
            }
        }

        this.canvasCtx.restore();
    },

    startAutoCapture: function(landmarks) {
        this.isCountingDown = true;
        this.countdownValue = 3;
        this.updateCountdownDisplay(this.countdownValue);
        this.updateStatus('Pose locked! Stand steady...', 'success');

        this.countdownTimer = setInterval(() => {
            this.countdownValue--;
            if (this.countdownValue > 0) {
                this.updateCountdownDisplay(this.countdownValue);
            } else {
                clearInterval(this.countdownTimer);
                this.updateCountdownDisplay('');
                this.captureMeasurement(landmarks);
            }
        }, 1000);
    },

    cancelAutoCapture: function() {
        this.isCountingDown = false;
        if (this.countdownTimer) clearInterval(this.countdownTimer);
        this.updateCountdownDisplay('');
    },

    updateCountdownDisplay: function(val) {
        const el = document.getElementById('scanCountdown');
        if (el) {
            el.innerText = val ? val : '';
        }
    },

    updateStatus: function(text, type) {
        const pill = document.getElementById('scannerStatusPill');
        if (pill) {
            pill.innerText = text;
            pill.className = `scanner-status-pill text-${type}`;
        }
    },

    captureMeasurement: function(landmarks) {
        this.isScanning = false;
        this.capturedLandmarks = landmarks;

        // Take snapshot image from video for Virtual Try-On
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = this.videoElement.videoWidth || 640;
        tempCanvas.height = this.videoElement.videoHeight || 480;
        const tempCtx = tempCanvas.getContext('2d');
        tempCtx.translate(tempCanvas.width, 0);
        tempCtx.scale(-1, 1); // un-mirror for try-on photo
        tempCtx.drawImage(this.videoElement, 0, 0, tempCanvas.width, tempCanvas.height);
        this.capturedImageSrc = tempCanvas.toDataURL('image/jpeg', 0.85);

        // Compute anthropometrically calibrated measurements
        const measurements = this.computeMeasurements(landmarks);

        this.stopCamera();

        // Pass to Size Engine
        SmartFitSizeEngine.calculateSize(measurements);
    },

    /**
     * Highly Accurate Anthropometric Ratio Engine (Distance-Invariant & Landmark-Calibrated)
     */
    computeMeasurements: function(landmarks) {
        const leftSh = landmarks[11];
        const rightSh = landmarks[12];
        const leftHip = landmarks[23];
        const rightHip = landmarks[24];
        const nose = landmarks[0];

        // 1. Pixel Distance between shoulder joint pivots (Glenohumeral joints)
        const dxSh = (leftSh.x - rightSh.x);
        const dySh = (leftSh.y - rightSh.y);
        const shoulderPixelDist = Math.sqrt(dxSh * dxSh + dySh * dySh);

        // 2. Pixel Distance between hip pivots
        const dxHip = (leftHip.x - rightHip.x);
        const dyHip = (leftHip.y - rightHip.y);
        const hipPixelDist = Math.sqrt(dxHip * dxHip + dyHip * dyHip);

        // 3. Pixel Torso Length (Mid-shoulder to Mid-hip)
        const midShX = (leftSh.x + rightSh.x) / 2;
        const midShY = (leftSh.y + rightSh.y) / 2;
        const midHipX = (leftHip.x + rightHip.x) / 2;
        const midHipY = (leftHip.y + rightHip.y) / 2;
        const torsoPixelDist = Math.sqrt(Math.pow(midHipX - midShX, 2) + Math.pow(midHipY - midShY, 2));

        // 4. Distance-Invariant Anthropometric Scaling
        // Human torso (shoulder to hip) averages ~30% of total stature
        const userH = this.userHeightCm || (this.selectedGender === 'womens' ? 162.0 : 173.0);
        const estimatedTorsoCm = userH * 0.30;

        const shoulderToTorsoRatio = shoulderPixelDist / Math.max(0.1, torsoPixelDist);
        
        // Anatomical correction: MediaPipe landmarks 11 & 12 are internal joint sockets.
        // True garment biacromial/bideltoid shoulder breadth is ~1.22x - 1.24x wider.
        const jointToDeltoidExpansion = this.selectedGender === 'womens' ? 1.20 : 1.24;

        // Build Modifier adjustments
        let buildFactor = 1.0;
        if (this.userBuild === 'slim') buildFactor = 0.95;
        if (this.userBuild === 'athletic') buildFactor = 1.05;
        if (this.userBuild === 'plus') buildFactor = 1.12;

        // Calibrated Shoulder Width in CM
        let shoulderCm = (estimatedTorsoCm * shoulderToTorsoRatio * jointToDeltoidExpansion) * buildFactor;

        // Sanity bound to natural adult human ranges
        if (this.selectedGender === 'womens') {
            shoulderCm = Math.max(34.0, Math.min(48.0, shoulderCm));
        } else {
            shoulderCm = Math.max(38.5, Math.min(56.0, shoulderCm));
        }

        // Calibrated Chest Circumference in CM
        let chestMultiplier = this.selectedGender === 'womens' ? 2.25 : 2.22;
        if (this.userBuild === 'athletic') chestMultiplier += 0.08;
        if (this.userBuild === 'plus') chestMultiplier += 0.15;
        if (this.userBuild === 'slim') chestMultiplier -= 0.06;

        let chestCm = shoulderCm * chestMultiplier;

        // Calibrated Waist Circumference in CM using actual Hip-to-Shoulder ratio
        const hipToShoulderRatio = hipPixelDist / Math.max(0.1, shoulderPixelDist);
        let waistRatio = this.selectedGender === 'womens' ? 0.77 : 0.81;
        if (hipToShoulderRatio > 0.85) waistRatio += 0.03;
        if (hipToShoulderRatio < 0.70) waistRatio -= 0.03; // Strong V-taper
        if (this.userBuild === 'athletic') waistRatio -= 0.04;
        if (this.userBuild === 'plus') waistRatio += 0.09;
        if (this.userBuild === 'slim') waistRatio -= 0.04;

        let waistCm = chestCm * waistRatio;

        return {
            gender: this.selectedGender,
            shoulder_cm: parseFloat(shoulderCm.toFixed(1)),
            chest_cm: parseFloat(chestCm.toFixed(1)),
            waist_cm: parseFloat(waistCm.toFixed(1)),
            height_cm: parseFloat(userH.toFixed(1)),
            user_build: this.userBuild,
            fit_preference: this.fitPreference,
            capturedImageSrc: this.capturedImageSrc
        };
    },

    // Manual Direct Calculation (No Camera needed)
    calculateManual: function() {
        const heightVal = parseFloat(document.getElementById('manualHeightInput').value) || 173.0;
        const weightVal = parseFloat(document.getElementById('manualWeightInput').value) || 72.0;
        const gender = document.querySelector('input[name="manualGender"]:checked')?.value || 'mens';
        const build = document.getElementById('manualBuildSelect')?.value || 'regular';
        const fitPref = document.getElementById('manualFitPref')?.value || 'regular';

        // Anthropometric BMI & Height to Shoulder Estimation
        // Shoulder width ~ (Height * 0.25) adjusted for Weight & Build
        const bmi = weightVal / Math.pow(heightVal / 100, 2);
        let baseShoulder = heightVal * 0.255;

        if (gender === 'womens') {
            baseShoulder = heightVal * 0.240;
        }

        // Weight/BMI correction factor
        const bmiFactor = Math.pow(bmi / 22.5, 0.4);
        let shoulderCm = baseShoulder * bmiFactor;

        let chestCm = gender === 'womens' ? (shoulderCm * 2.25) : (shoulderCm * 2.22);
        if (build === 'athletic') chestCm += 3.0;
        if (build === 'plus') chestCm += 6.0;

        let waistCm = chestCm * (gender === 'womens' ? 0.77 : 0.82);
        if (build === 'athletic') waistCm -= 3.5;
        if (build === 'plus') waistCm += 8.0;

        const manualData = {
            gender: gender,
            shoulder_cm: parseFloat(shoulderCm.toFixed(1)),
            chest_cm: parseFloat(chestCm.toFixed(1)),
            waist_cm: parseFloat(waistCm.toFixed(1)),
            height_cm: parseFloat(heightVal.toFixed(1)),
            user_build: build,
            fit_preference: fitPref,
            capturedImageSrc: null
        };

        const scanModal = bootstrap.Modal.getInstance(document.getElementById('scannerModal'));
        if (scanModal) scanModal.hide();

        SmartFitSizeEngine.calculateSize(manualData);
    },

    simulateScan: function() {
        this.updateStatus('Running Calibrated AI Simulation...', 'info');
        setTimeout(() => {
            const isMen = this.selectedGender === 'mens';
            const sampleData = {
                gender: this.selectedGender,
                shoulder_cm: isMen ? 44.5 : 39.8,
                chest_cm: isMen ? 99.0 : 89.5,
                waist_cm: isMen ? 81.2 : 71.0,
                height_cm: isMen ? 174.0 : 163.0,
                user_build: this.userBuild,
                fit_preference: this.fitPreference,
                capturedImageSrc: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600'
            };
            this.stopCamera();
            SmartFitSizeEngine.calculateSize(sampleData);
        }, 600);
    }
};