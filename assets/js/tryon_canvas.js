/**
 * SmartFit AI - Advanced Landmark-Aligned & Real-Time Live AR Virtual Try-On Engine
 */

const SmartFitTryOn = {
    canvas: null,
    ctx: null,
    userImage: null,
    clothImage: null,
    currentItem: null,
    userImageSrc: null,
    userLandmarks: null,
    
    // Live AR State
    isLiveAR: false,
    isWebcamCaptured: false,
    arVideo: null,
    arStream: null,
    arPose: null,
    arAnimFrameId: null,
    isProcessingPose: false,
    hasDetectedLandmarks: false,
    
    // Current Cloth Transform State
    clothState: {
        x: 120,
        y: 110,
        width: 260,
        height: 325,
        rotation: 0,
        opacity: 0.95,
        blendMode: 'source-over',
        baseScale: 1.0
    },

    isDragging: false,
    dragStartX: 0,
    dragStartY: 0,

    init: function() {
        this.canvas = document.getElementById('tryonCanvas');
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');

        // Setup mouse drag
        this.canvas.onmousedown = (e) => this.onDragStart(e);
        window.onmousemove = (e) => this.onDragMove(e);
        window.onmouseup = () => this.onDragEnd();

        // Setup touch drag
        this.canvas.ontouchstart = (e) => this.onTouchStart(e);
        window.ontouchmove = (e) => this.onTouchMove(e);
        window.ontouchend = () => this.onDragEnd();

        // Default Model Photo if none captured yet
        if (!this.userImageSrc) {
            this.userImageSrc = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800';
        }
        this.loadUserImage(this.userImageSrc);
        this.renderCatalogThumbnails();

        if (!this.currentItem && window.SmartFitApp && window.SmartFitApp.clothesList && window.SmartFitApp.clothesList.length > 0) {
            this.selectCarouselItem(window.SmartFitApp.clothesList[0].id);
        }

        // Bind modal hidden event to stop live AR
        const tryonModalEl = document.getElementById('tryonModal');
        if (tryonModalEl) {
            tryonModalEl.addEventListener('hidden.bs.modal', () => {
                this.stopLiveAR();
            });
        }
    },

    setUserPhoto: function(imageSrc, landmarks = null) {
        this.userImageSrc = imageSrc;
        this.userLandmarks = landmarks;
        this.loadUserImage(imageSrc);
    },

    loadUserImage: function(src) {
        if (!this.canvas) this.init();
        this.userImage = new Image();
        this.userImage.crossOrigin = 'anonymous';
        this.userImage.onload = () => {
            if (this.userLandmarks) {
                this.autoFitToLandmarks(this.userLandmarks);
            }
            this.render();
        };
        this.userImage.src = src;
    },

    loadClothImage: function(src) {
        if (!this.canvas) this.init();
        this.clothImage = new Image();
        this.clothImage.crossOrigin = 'anonymous';
        this.clothImage.onload = () => {
            if (this.userLandmarks) {
                this.autoFitToLandmarks(this.userLandmarks);
            } else if (this.canvas) {
                this.setDefaultClothPosition();
            }
            this.render();
        };
        this.clothImage.src = src;
    },

    setDefaultClothPosition: function() {
        if (!this.canvas) return;
        const cw = this.canvas.width;
        const ch = this.canvas.height;
        let aspect = 1.16;
        if (this.clothImage && this.clothImage.naturalWidth && this.clothImage.naturalHeight) {
            aspect = this.clothImage.naturalHeight / this.clothImage.naturalWidth;
        }
        const width = cw * 0.65 * (this.clothState.baseScale || 1.0);
        const height = width * aspect;
        this.clothState.width = width;
        this.clothState.height = height;
        this.clothState.x = (cw - width) / 2;
        this.clothState.y = ch * 0.16;
        this.clothState.rotation = 0;
    },

    /**
     * Precision Anatomical Landmark Alignment & Realistic Body Draping
     * Automatically scales shoulders, collar, and torso length to user landmarks
     */
    autoFitToLandmarks: function(landmarks) {
        if (!this.canvas || !landmarks) return;

        const leftSh = landmarks[11];   // Anatomical Left Shoulder
        const rightSh = landmarks[12];  // Anatomical Right Shoulder
        const leftHip = landmarks[23];  // Anatomical Left Hip
        const rightHip = landmarks[24]; // Anatomical Right Hip
        const nose = landmarks[0];

        if (!leftSh || !rightSh) return;

        const cw = this.canvas.width;
        const ch = this.canvas.height;

        let screenLeftX, screenLeftY, screenRightX, screenRightY;

        // In Live AR (video mirrored) OR webcam scanner capture (which was mirrored by tempCtx.scale(-1, 1)):
        // Left shoulder appears on screen-left, Right shoulder appears on screen-right.
        if (this.isLiveAR || this.isWebcamCaptured) {
            screenLeftX = (1 - leftSh.x) * cw;
            screenLeftY = leftSh.y * ch;
            screenRightX = (1 - rightSh.x) * cw;
            screenRightY = rightSh.y * ch;
        } else {
            // For standard forward-facing uploaded unmirrored photo:
            screenLeftX = Math.min(leftSh.x, rightSh.x) * cw;
            screenLeftY = (leftSh.x < rightSh.x ? leftSh.y : rightSh.y) * ch;
            screenRightX = Math.max(leftSh.x, rightSh.x) * cw;
            screenRightY = (leftSh.x < rightSh.x ? rightSh.y : leftSh.y) * ch;
        }

        // Vector from Screen-Left Shoulder to Screen-Right Shoulder
        const dx = screenRightX - screenLeftX;
        const dy = screenRightY - screenLeftY;
        const shDist = Math.hypot(dx, dy);

        // Clamped tilt angle (between -25 and +25 deg)
        let angleDeg = (Math.atan2(dy, dx) * 180) / Math.PI;
        angleDeg = Math.max(-25, Math.min(25, angleDeg));

        const midShX = (screenLeftX + screenRightX) / 2;
        const midShY = (screenLeftY + screenRightY) / 2;

        // Anatomical apparel sizing:
        // In our SVG cutouts, the shoulder seams occupy ~48-52% of the bounding box (rest are sleeves).
        // Therefore, to reach across both deltoid shoulders and allow sleeves to drape over arms,
        // the total garment bounding width must be ~2.30x - 2.45x of joint-to-joint shDist.
        let widthMultiplier = 2.35;
        let collarOffsetRatio = 0.12;

        const itemName = (this.currentItem && this.currentItem.name) ? this.currentItem.name.toLowerCase() : '';
        const itemCode = (this.currentItem && this.currentItem.item_code) ? this.currentItem.item_code.toLowerCase() : '';

        if (itemName.includes('hoodie') || itemCode.includes('hod')) {
            widthMultiplier = 2.40;
            collarOffsetRatio = 0.165; // Hood sits higher above neckline
        } else if (itemName.includes('oversized') || itemCode.includes('ovr')) {
            widthMultiplier = 2.48;
            collarOffsetRatio = 0.125;
        } else if (itemName.includes('crop') || itemCode.includes('crp')) {
            widthMultiplier = 2.15;
            collarOffsetRatio = 0.13;
        } else if (itemName.includes('polo')) {
            widthMultiplier = 2.28;
            collarOffsetRatio = 0.115;
        }

        let aspect = 1.16; // default 580/500
        if (this.clothImage && this.clothImage.naturalWidth && this.clothImage.naturalHeight) {
            aspect = this.clothImage.naturalHeight / this.clothImage.naturalWidth;
        }

        const gWidth = shDist * widthMultiplier * (this.clothState.baseScale || 1.0);
        let gHeight = gWidth * aspect;

        // Dynamic Torso Length: If hips are detected, adapt garment length to human torso
        if (leftHip && rightHip && leftHip.visibility > 0.35 && rightHip.visibility > 0.35) {
            const midHipY = (leftHip.y + rightHip.y) / 2 * ch;
            const detectedTorsoH = midHipY - midShY;
            if (detectedTorsoH > shDist * 0.7) {
                let targetHem = midHipY + (detectedTorsoH * 0.12);
                if (itemName.includes('crop') || itemCode.includes('crp')) {
                    targetHem = midHipY - (detectedTorsoH * 0.28);
                } else if (itemName.includes('hoodie') || itemCode.includes('hod')) {
                    targetHem = midHipY + (detectedTorsoH * 0.22);
                }
                const calibratedH = targetHem - (midShY - shDist * 0.16);
                if (calibratedH > gHeight * 0.8 && calibratedH < gHeight * 1.35) {
                    gHeight = calibratedH;
                }
            }
        }

        // Base of the neck sits above the shoulder joint line
        const neckBaseY = midShY - (shDist * 0.16);

        // Position garment so collar sits precisely at the base of the neck
        const targetX = midShX - (gWidth / 2);
        const targetY = neckBaseY - (gHeight * collarOffsetRatio);

        if (this.isLiveAR) {
            // Smooth Interpolation (Lerp) for AR camera tracking
            const lerpFactor = 0.35;
            this.clothState.width += (gWidth - this.clothState.width) * lerpFactor;
            this.clothState.height += (gHeight - this.clothState.height) * lerpFactor;
            this.clothState.x += (targetX - this.clothState.x) * lerpFactor;
            this.clothState.y += (targetY - this.clothState.y) * lerpFactor;
            this.clothState.rotation += (angleDeg - this.clothState.rotation) * lerpFactor;
            this.hasDetectedLandmarks = true;
        } else {
            this.clothState.width = gWidth;
            this.clothState.height = gHeight;
            this.clothState.x = targetX;
            this.clothState.y = targetY;
            this.clothState.rotation = angleDeg;
            this.hasDetectedLandmarks = true;
        }
    },

    tryOnItem: function(item) {
        this.currentItem = item;
        const tryonModalEl = document.getElementById('tryonModal');
        if (tryonModalEl) {
            const tryonModal = new bootstrap.Modal(tryonModalEl);
            tryonModal.show();
        }

        setTimeout(() => {
            if (!this.canvas) this.init();
            
            const titleEl = document.getElementById('tryonItemTitle');
            if (titleEl) titleEl.innerText = item.name;

            const priceEl = document.getElementById('tryonItemPrice');
            if (priceEl) priceEl.innerText = 'Rs. ' + parseFloat(item.price).toLocaleString();

            const brandEl = document.getElementById('tryonItemBrand');
            if (brandEl) brandEl.innerText = `${item.brand} • ${item.color} • ${item.gender.toUpperCase()}`;

            if (SmartFitScanner && SmartFitScanner.capturedLandmarks) {
                this.userLandmarks = SmartFitScanner.capturedLandmarks;
                this.isWebcamCaptured = true;
            }
            if (SmartFitScanner && SmartFitScanner.capturedImageSrc) {
                this.userImageSrc = SmartFitScanner.capturedImageSrc;
                this.loadUserImage(this.userImageSrc);
                this.isWebcamCaptured = true;
            }

            const garmentSrc = item.overlay_image_url || item.image_url;
            this.loadClothImage(garmentSrc);
            this.highlightActiveThumbnail(item.id);

            const tryonWaBtn = document.getElementById('tryonWhatsAppBtn');
            if (tryonWaBtn && item) {
                const curSize = (window.SmartFitApp && window.SmartFitApp.currentSize) ? window.SmartFitApp.currentSize : 'M';
                const msg = encodeURIComponent(`Hi SmartFit AI, I tried on the "${item.name}" (Code: ${item.item_code}) in the Virtual Atelier and want to order it in Size ${curSize}! (Price: Rs. ${parseFloat(item.price).toLocaleString()})`);
                tryonWaBtn.href = `https://wa.me/94771234567?text=${msg}`;
            }
        }, 200);
    },

    render: function() {
        if (!this.ctx || !this.canvas) return;

        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // 1. Draw User Background (Live AR Video or Static Photo)
        if (this.isLiveAR && this.arVideo && this.arVideo.readyState >= 2) {
            this.ctx.save();
            this.ctx.translate(this.canvas.width, 0);
            this.ctx.scale(-1, 1); // mirror effect for natural mirror experience
            this.ctx.drawImage(this.arVideo, 0, 0, this.canvas.width, this.canvas.height);
            this.ctx.restore();
        } else if (this.userImage && this.userImage.complete) {
            this.ctx.drawImage(this.userImage, 0, 0, this.canvas.width, this.canvas.height);
        } else {
            // Dark cyber gradient backdrop
            const bgGrad = this.ctx.createLinearGradient(0, 0, 0, this.canvas.height);
            bgGrad.addColorStop(0, '#0f172a');
            bgGrad.addColorStop(1, '#020617');
            this.ctx.fillStyle = bgGrad;
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        }

        // 2. Draw Garment Layer
        if (this.clothImage && this.clothImage.complete) {
            this.ctx.save();
            this.ctx.globalAlpha = this.clothState.opacity;
            this.ctx.globalCompositeOperation = this.clothState.blendMode || 'source-over';

            const cx = this.clothState.x + this.clothState.width / 2;
            const cy = this.clothState.y + this.clothState.height / 2;

            this.ctx.translate(cx, cy);
            this.ctx.rotate((this.clothState.rotation * Math.PI) / 180);

            // Realistic 3D Soft Drop Shadow
            this.ctx.shadowColor = 'rgba(0, 0, 0, 0.50)';
            this.ctx.shadowBlur = 22;
            this.ctx.shadowOffsetY = 8;

            this.ctx.drawImage(
                this.clothImage,
                -this.clothState.width / 2,
                -this.clothState.height / 2,
                this.clothState.width,
                this.clothState.height
            );

            // Subtle curved torso lighting overlay to eliminate flat sticker look
            this.ctx.shadowColor = 'transparent';
            const depthGrad = this.ctx.createLinearGradient(
                -this.clothState.width / 2, 0,
                this.clothState.width / 2, 0
            );
            depthGrad.addColorStop(0, 'rgba(0, 0, 0, 0.18)');
            depthGrad.addColorStop(0.25, 'rgba(255, 255, 255, 0.05)');
            depthGrad.addColorStop(0.5, 'rgba(255, 255, 255, 0.0)');
            depthGrad.addColorStop(0.75, 'rgba(255, 255, 255, 0.05)');
            depthGrad.addColorStop(1, 'rgba(0, 0, 0, 0.20)');

            this.ctx.globalCompositeOperation = 'multiply';
            this.ctx.fillStyle = depthGrad;
            this.ctx.fillRect(
                -this.clothState.width / 2,
                -this.clothState.height / 2,
                this.clothState.width,
                this.clothState.height
            );

            this.ctx.restore();
        }

        // 3. Live AR Status Watermark Badge on Canvas
        if (this.isLiveAR) {
            this.ctx.save();
            this.ctx.fillStyle = 'rgba(15, 23, 42, 0.75)';
            this.ctx.beginPath();
            this.ctx.roundRect(14, 14, 150, 32, 8);
            this.ctx.fill();
            this.ctx.strokeStyle = 'rgba(99, 102, 241, 0.5)';
            this.ctx.stroke();

            this.ctx.fillStyle = '#ef4444';
            this.ctx.beginPath();
            this.ctx.arc(28, 30, 5, 0, Math.PI * 2);
            this.ctx.fill();

            this.ctx.fillStyle = '#f8fafc';
            this.ctx.font = 'bold 12px Inter, sans-serif';
            this.ctx.fillText('LIVE AR MIRROR', 40, 34);
            this.ctx.restore();
        }
    },

    /**
     * Start / Toggle Real-time AR Camera Mirror
     */
    toggleLiveAR: async function() {
        if (this.isLiveAR) {
            this.stopLiveAR();
        } else {
            await this.startLiveAR();
        }
    },

    startLiveAR: async function() {
        const btn = document.getElementById('tryonLiveARBtn');
        if (btn) {
            btn.className = 'btn btn-danger btn-sm flex-fill';
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Starting AR...';
        }

        try {
            this.arVideo = document.createElement('video');
            this.arVideo.setAttribute('autoplay', '');
            this.arVideo.setAttribute('muted', '');
            this.arVideo.setAttribute('playsinline', '');
            this.arVideo.muted = true;
            this.arVideo.style.display = 'none';
            document.body.appendChild(this.arVideo);

            this.arStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
                audio: false
            });

            this.arVideo.srcObject = this.arStream;
            await this.arVideo.play();

            this.isLiveAR = true;

            // Initialize Pose for AR
            if (typeof Pose !== 'undefined') {
                this.arPose = new Pose({
                    locateFile: (f) => `https://cdn.jsdelivr.net/npm/@mediapipe/pose/${f}`
                });

                this.arPose.setOptions({
                    modelComplexity: 0,
                    smoothLandmarks: true,
                    minDetectionConfidence: 0.5,
                    minTrackingConfidence: 0.5
                });

                this.arPose.onResults((res) => {
                    this.isProcessingPose = false;
                    if (res.poseLandmarks && this.isLiveAR) {
                        this.autoFitToLandmarks(res.poseLandmarks);
                    }
                });
            }

            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-video-slash me-1"></i> Stop Live AR';
            }

            // Start continuous High-Performance render loop
            this.startARRenderLoop();

        } catch (err) {
            console.error('AR Camera error:', err);
            alert('Camera access denied or unavailable for Live AR.');
            this.stopLiveAR();
        }
    },

    startARRenderLoop: function() {
        const loop = () => {
            if (!this.isLiveAR) return;

            // Render current frame
            this.render();

            // Send video frame to MediaPipe Pose detector if ready
            if (this.arPose && this.arVideo && this.arVideo.readyState >= 2 && !this.isProcessingPose) {
                this.isProcessingPose = true;
                this.arPose.send({ image: this.arVideo }).catch(() => {
                    this.isProcessingPose = false;
                });
            }

            this.arAnimFrameId = requestAnimationFrame(loop);
        };

        this.arAnimFrameId = requestAnimationFrame(loop);
    },

    stopLiveAR: function() {
        this.isLiveAR = false;
        if (this.arAnimFrameId) {
            cancelAnimationFrame(this.arAnimFrameId);
            this.arAnimFrameId = null;
        }

        if (this.arStream) {
            this.arStream.getTracks().forEach(t => t.stop());
            this.arStream = null;
        }

        if (this.arVideo) {
            if (this.arVideo.parentNode) this.arVideo.parentNode.removeChild(this.arVideo);
            this.arVideo = null;
        }

        const btn = document.getElementById('tryonLiveARBtn');
        if (btn) {
            btn.className = 'btn btn-outline-cyber btn-sm flex-fill';
            btn.innerHTML = '<i class="fa-solid fa-camera me-1"></i> Live AR Mirror';
        }

        this.render();
    },

    // Controls
    updateScale: function(scaleVal) {
        this.clothState.baseScale = parseFloat(scaleVal);
        if (this.userLandmarks) {
            this.autoFitToLandmarks(this.userLandmarks);
        } else {
            this.setDefaultClothPosition();
        }
        this.render();
    },

    updateOpacity: function(opVal) {
        this.clothState.opacity = parseFloat(opVal);
        this.render();
    },

    resetTransform: function() {
        this.clothState.baseScale = 1.0;
        this.clothState.opacity = 0.95;

        if (this.userLandmarks) {
            this.autoFitToLandmarks(this.userLandmarks);
        } else {
            this.setDefaultClothPosition();
        }

        const scaleSlider = document.getElementById('tryonScaleSlider');
        if (scaleSlider) scaleSlider.value = 1.0;
        const opSlider = document.getElementById('tryonOpacitySlider');
        if (opSlider) opSlider.value = 0.95;

        this.render();
    },

    downloadSnapshot: function() {
        if (!this.canvas) return;
        const link = document.createElement('a');
        link.download = `smartfit-virtual-tryon-${Date.now()}.jpg`;
        link.href = this.canvas.toDataURL('image/jpeg', 0.95);
        link.click();
    },

    handleUserPhotoUpload: function(fileInput) {
        if (!fileInput.files || !fileInput.files[0]) return;
        this.stopLiveAR(); // Stop AR if running
        this.isWebcamCaptured = false;

        const file = fileInput.files[0];
        const reader = new FileReader();

        reader.onload = (e) => {
            this.userImageSrc = e.target.result;
            const img = new Image();
            img.onload = () => {
                this.userImage = img;
                if (typeof Pose !== 'undefined') {
                    const pose = new Pose({ locateFile: (f) => `https://cdn.jsdelivr.net/npm/@mediapipe/pose/${f}` });
                    pose.setOptions({ modelComplexity: 1, minDetectionConfidence: 0.5 });
                    pose.onResults((res) => {
                        if (res.poseLandmarks) {
                            this.userLandmarks = res.poseLandmarks;
                            this.autoFitToLandmarks(res.poseLandmarks);
                        }
                        this.render();
                    });
                    pose.send({ image: img });
                } else {
                    this.render();
                }
            };
            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    },

    renderCatalogThumbnails: function() {
        const carousel = document.getElementById('tryonClothesCarousel');
        if (!carousel) return;

        if (window.SmartFitApp && window.SmartFitApp.clothesList && window.SmartFitApp.clothesList.length > 0) {
            let html = '';
            window.SmartFitApp.clothesList.forEach(item => {
                html += `
                    <div class="tryon-thumb-item me-2 text-center" style="cursor: pointer; display: inline-block; width: 64px;" onclick="SmartFitTryOn.selectCarouselItem(${item.id})">
                        <img src="${item.image_url}" id="tryon_thumb_${item.id}" class="rounded border border-secondary" style="width: 58px; height: 72px; object-fit: cover; transition: all 0.2s;" alt="${item.name}">
                        <div class="text-truncate text-muted small mt-1" style="font-size: 0.65rem;">${item.color}</div>
                    </div>
                `;
            });
            carousel.innerHTML = html;

            if (this.currentItem) {
                this.highlightActiveThumbnail(this.currentItem.id);
            } else {
                this.selectCarouselItem(window.SmartFitApp.clothesList[0].id);
            }
        }
    },

    selectCarouselItem: function(itemId) {
        if (!window.SmartFitApp || !window.SmartFitApp.clothesList) return;
        const item = window.SmartFitApp.clothesList.find(i => parseInt(i.id) === parseInt(itemId));
        if (item) {
            this.currentItem = item;
            document.getElementById('tryonItemTitle').innerText = item.name;
            document.getElementById('tryonItemPrice').innerText = 'Rs. ' + parseFloat(item.price).toLocaleString();
            document.getElementById('tryonItemBrand').innerText = `${item.brand} • ${item.color} • ${item.gender.toUpperCase()}`;
            
            const garmentSrc = item.overlay_image_url || item.image_url;
            this.loadClothImage(garmentSrc);
            this.highlightActiveThumbnail(itemId);

            const tryonWaBtn = document.getElementById('tryonWhatsAppBtn');
            if (tryonWaBtn && item) {
                const curSize = (window.SmartFitApp && window.SmartFitApp.currentSize) ? window.SmartFitApp.currentSize : 'M';
                const msg = encodeURIComponent(`Hi SmartFit AI, I tried on the "${item.name}" (Code: ${item.item_code}) in the Virtual Atelier and want to order it in Size ${curSize}! (Price: Rs. ${parseFloat(item.price).toLocaleString()})`);
                tryonWaBtn.href = `https://wa.me/94771234567?text=${msg}`;
            }
        }
    },

    highlightActiveThumbnail: function(itemId) {
        document.querySelectorAll('.tryon-thumb-item img').forEach(img => {
            img.style.borderColor = 'rgba(255,255,255,0.2)';
            img.style.boxShadow = 'none';
        });
        const activeImg = document.getElementById('tryon_thumb_' + itemId);
        if (activeImg) {
            activeImg.style.borderColor = '#6366f1';
            activeImg.style.boxShadow = '0 0 12px rgba(99, 102, 241, 0.7)';
        }
    },

    // Drag handlers
    onDragStart: function(e) {
        const rect = this.canvas.getBoundingClientRect();
        const mouseX = (e.clientX - rect.left) * (this.canvas.width / rect.width);
        const mouseY = (e.clientY - rect.top) * (this.canvas.height / rect.height);

        if (
            mouseX >= this.clothState.x &&
            mouseX <= this.clothState.x + this.clothState.width &&
            mouseY >= this.clothState.y &&
            mouseY <= this.clothState.y + this.clothState.height
        ) {
            this.isDragging = true;
            this.dragStartX = mouseX - this.clothState.x;
            this.dragStartY = mouseY - this.clothState.y;
        }
    },

    onDragMove: function(e) {
        if (!this.isDragging || !this.canvas) return;
        const rect = this.canvas.getBoundingClientRect();
        const mouseX = (e.clientX - rect.left) * (this.canvas.width / rect.width);
        const mouseY = (e.clientY - rect.top) * (this.canvas.height / rect.height);

        this.clothState.x = mouseX - this.dragStartX;
        this.clothState.y = mouseY - this.dragStartY;
        this.render();
    },

    onTouchStart: function(e) {
        if (e.touches.length === 1) {
            const touch = e.touches[0];
            const rect = this.canvas.getBoundingClientRect();
            const touchX = (touch.clientX - rect.left) * (this.canvas.width / rect.width);
            const touchY = (touch.clientY - rect.top) * (this.canvas.height / rect.height);

            if (
                touchX >= this.clothState.x &&
                touchX <= this.clothState.x + this.clothState.width &&
                touchY >= this.clothState.y &&
                touchY <= this.clothState.y + this.clothState.height
            ) {
                this.isDragging = true;
                this.dragStartX = touchX - this.clothState.x;
                this.dragStartY = touchY - this.clothState.y;
                e.preventDefault();
            }
        }
    },

    onTouchMove: function(e) {
        if (!this.isDragging || !this.canvas) return;
        if (e.touches.length === 1) {
            const touch = e.touches[0];
            const rect = this.canvas.getBoundingClientRect();
            const touchX = (touch.clientX - rect.left) * (this.canvas.width / rect.width);
            const touchY = (touch.clientY - rect.top) * (this.canvas.height / rect.height);

            this.clothState.x = touchX - this.dragStartX;
            this.clothState.y = touchY - this.dragStartY;
            this.render();
            e.preventDefault();
        }
    },

    onDragEnd: function() {
        this.isDragging = false;
    }
};