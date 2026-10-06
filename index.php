<?php
$pageTitle = "SmartFit LK — AI Body Fit Profile & Lowest Price Match";
$assetPrefix = "";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-smart text-center">
    <div class="hero-glow-bg"></div>
    <div class="container position-relative z-1">
        <div class="hero-pill mb-3 animate__animated animate__fadeInDown">
            <i class="fa-solid fa-sparkles text-neon"></i>
            <span>AI-Assisted Fit Profile &bull; Lowest Price Guarantee &bull; Virtual Try-On</span>
        </div>
        
        <h1 class="hero-title mb-3 animate__animated animate__fadeIn">
            FIND YOUR PERFECT FIT &amp; <br>
            <span class="gradient-text">LOWEST PRICED CLOTHES</span>
        </h1>
        
        <p class="text-secondary max-w-600 mx-auto mb-4 animate__animated animate__fadeIn animate__delay-1s">
            Step in front of your camera for an intelligent, approximate AI Body Fit Profile. 
            Instantly discover in-stock shop apparel ranked by the <strong>lowest price</strong> (🥇 Best Value) and preview with Live 2D Try-On.
        </p>
        
        <div class="d-flex flex-wrap justify-content-center gap-3 animate__animated animate__fadeInUp animate__delay-1s">
            <button class="btn btn-scan-glow btn-lg px-4 py-3 d-flex align-items-center gap-2" onclick="openScannerModal()">
                <i class="fa-solid fa-camera-viewfinder fs-5"></i>
                <span>Find My Best Fit &amp; Lowest Price</span>
            </button>
            <button class="btn btn-outline-cyber btn-lg px-4 py-3 d-flex align-items-center gap-2" onclick="openTryonModal()">
                <i class="fa-solid fa-shirt text-neon fs-5"></i>
                <span>Virtual Fitting Room</span>
            </button>
        </div>
    </div>
</section>

<!-- Main Store Section -->
<section class="container my-5">
    <!-- Filter & Search Bar -->
    <div class="glass-card p-3 mb-4">
        <div class="row g-3 align-items-center justify-content-between">
            <!-- Gender Selector -->
            <div class="col-lg-4 col-md-6">
                <div class="gender-selector w-100">
                    <button class="gender-btn gender-filter-btn active flex-fill" data-gender="all">All Styles</button>
                    <button class="gender-btn gender-filter-btn flex-fill" data-gender="mens">
                        <i class="fa-solid fa-mars me-1"></i> Men's
                    </button>
                    <button class="gender-btn gender-filter-btn flex-fill" data-gender="womens">
                        <i class="fa-solid fa-venus me-1"></i> Women's
                    </button>
                </div>
            </div>

            <!-- Size Pills -->
            <div class="col-lg-4 col-md-6 d-flex align-items-center justify-content-lg-center gap-2 flex-wrap">
                <span class="text-muted small me-1">FILTER SIZE:</span>
                <button class="size-pill-filter size-filter-pill" data-size="XS">XS</button>
                <button class="size-pill-filter size-filter-pill" data-size="S">S</button>
                <button class="size-pill-filter size-filter-pill" data-size="M">M</button>
                <button class="size-pill-filter size-filter-pill" data-size="L">L</button>
                <button class="size-pill-filter size-filter-pill" data-size="XL">XL</button>
                <button class="size-pill-filter size-filter-pill" data-size="XXL">XXL</button>
            </div>

            <!-- Search Box -->
            <div class="col-lg-4 col-md-12">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="storeSearchInput" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="Search polo, short, jogger, color...">
                </div>
            </div>
        </div>
    </div>

    <!-- Category Pills Filter Bar -->
    <div class="d-flex align-items-center gap-2 overflow-auto pb-2 mb-4">
        <button class="btn btn-sm btn-outline-cyber category-filter-btn active" data-category="all">
            <i class="fa-solid fa-border-all me-1"></i> All Items
        </button>
        <button class="btn btn-sm btn-outline-cyber category-filter-btn" data-category="mens-polos">
            <i class="fa-solid fa-vest me-1"></i> Seamless Polos
        </button>
        <button class="btn btn-sm btn-outline-cyber category-filter-btn" data-category="performance-outerwear">
            <i class="fa-solid fa-hoodie me-1"></i> Outerwear & Hoodies
        </button>
        <button class="btn btn-sm btn-outline-cyber category-filter-btn" data-category="mens-t-shirts">
            <i class="fa-solid fa-tshirt me-1"></i> Oversized Streetwear
        </button>
        <button class="btn btn-sm btn-outline-cyber category-filter-btn" data-category="womens-leggings">
            <i class="fa-solid fa-venus me-1"></i> Women's Crops & Tops
        </button>
    </div>

    <!-- Active Filters & Product Counter -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0 fw-bold">Activewear Collection</h4>
            <span id="productCountBadge" class="badge bg-secondary">Loading...</span>
        </div>
        <button class="btn btn-link text-neon text-decoration-none small p-0" onclick="SmartFitApp.resetFilters()">
            <i class="fa-solid fa-rotate-left me-1"></i> Reset Filters
        </button>
    </div>

    <!-- Product Grid -->
    <div id="clothesGrid" class="row">
        <!-- Live populated via JS -->
    </div>
</section>

<!-- ========================================== -->
<!-- 1. AI CAMERA BODY SCANNER MODAL           -->
<!-- ========================================== -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card border-secondary text-light">
            <div class="modal-header border-secondary">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-badge"><i class="fa-solid fa-expand text-neon"></i></div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold">AI Smart Body Scanner</h5>
                        <small class="text-secondary" style="font-size: 0.75rem;">MediaPipe Real-Time Computer Vision Pose Estimation</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="SmartFitScanner.stopCamera()"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Mode Tabs: Camera AI vs Direct Manual -->
                <ul class="nav nav-pills custom-pills mb-3 justify-content-center" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#cameraScanTab" onclick="SmartFitScanner.startCamera()">
                            <i class="fa-solid fa-camera me-1"></i> AI Camera Scan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#manualCalcTab" onclick="SmartFitScanner.stopCamera()">
                            <i class="fa-solid fa-calculator me-1"></i> Quick Measurements (No Camera)
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tab 1: AI Camera Scan -->
                    <div class="tab-pane fade show active" id="cameraScanTab">
                        <!-- Calibration Bar (Gender, Height & Body Build) -->
                        <div class="p-3 bg-dark rounded border border-dark mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Target Gender:</label>
                                    <div class="gender-selector w-100">
                                        <button class="gender-btn scanner-gender-btn active flex-fill py-1 small" data-gender="mens" onclick="SmartFitScanner.setGender('mens')">Men's</button>
                                        <button class="gender-btn scanner-gender-btn flex-fill py-1 small" data-gender="womens" onclick="SmartFitScanner.setGender('womens')">Women's</button>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Your Height (CM):</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="scannerUserHeight" class="form-control bg-dark text-light border-secondary shadow-none" value="173" min="140" max="210" oninput="SmartFitScanner.setUserHeight(this.value)">
                                        <span class="input-group-text bg-dark border-secondary text-muted">cm</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small text-muted mb-1">Body Build:</label>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-cyber scanner-build-btn p-1 flex-fill small" data-build="slim" onclick="SmartFitScanner.setUserBuild('slim')">Slim</button>
                                        <button class="btn btn-sm btn-outline-cyber scanner-build-btn active p-1 flex-fill small" data-build="regular" onclick="SmartFitScanner.setUserBuild('regular')">Regular</button>
                                        <button class="btn btn-sm btn-outline-cyber scanner-build-btn p-1 flex-fill small" data-build="athletic" onclick="SmartFitScanner.setUserBuild('athletic')">Athletic</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Camera Viewport Stage -->
                        <div class="scanner-viewport">
                            <video id="webcamVideo" autoplay playsinline muted></video>
                            <canvas id="poseCanvas"></canvas>
                            
                            <div class="scanner-overlay-guide">
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-dark border border-secondary px-2 py-1 small">
                                        <i class="fa-solid fa-circle text-danger me-1 animate__animated animate__pulse animate__infinite"></i> LIVE
                                    </span>
                                    <span class="badge bg-dark border border-secondary px-2 py-1 small">
                                        Anthropometric Ratio Engine
                                    </span>
                                </div>
                                
                                <div class="scanner-crosshairs"></div>
                                
                                <div id="scannerStatusPill" class="scanner-status-pill text-info">
                                    Initializing Camera...
                                </div>
                            </div>

                            <div id="scanCountdown" class="countdown-overlay"></div>
                        </div>

                        <!-- Guidance Notes -->
                        <div class="row g-2 mt-3 text-start small text-secondary">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2 p-2 bg-dark rounded border border-dark">
                                    <i class="fa-solid fa-person text-neon fs-5"></i>
                                    <span>Stand back until shoulders & hips are in frame</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2 p-2 bg-dark rounded border border-dark">
                                    <i class="fa-solid fa-ruler text-warning fs-5"></i>
                                    <span>Height calibration eliminates camera distance errors</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2 p-2 bg-dark rounded border border-dark">
                                    <i class="fa-solid fa-stopwatch text-emerald fs-5"></i>
                                    <span>Hold steady 3s to lock measurements</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Manual Measurements Direct Calculator -->
                    <div class="tab-pane fade" id="manualCalcTab">
                        <div class="p-4 bg-dark rounded border border-dark">
                            <h5 class="fw-bold mb-3 text-neon"><i class="fa-solid fa-calculator me-2"></i>Quick Body Parameter Fit Calculator</h5>
                            <p class="text-secondary small mb-4">Enter your basic body dimensions for instantaneous AI-estimated fit profile &amp; lowest price matches.</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Gender</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="manualGender" id="mg1" value="mens" checked>
                                            <label class="form-check-label text-white" for="mg1">Men's Apparel</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="manualGender" id="mg2" value="womens">
                                            <label class="form-check-label text-white" for="mg2">Women's Apparel</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Body Build Type</label>
                                    <select id="manualBuildSelect" class="form-select bg-dark text-light border-secondary shadow-none">
                                        <option value="regular" selected>Regular / Average</option>
                                        <option value="slim">Slim / Lean</option>
                                        <option value="athletic">Athletic / Muscular (V-Taper)</option>
                                        <option value="plus">Broad / Plus Size</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Height (CM)</label>
                                    <input type="number" id="manualHeightInput" class="form-control bg-dark text-light border-secondary shadow-none" value="175" min="140" max="215" placeholder="e.g. 175">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Weight (KG)</label>
                                    <input type="number" id="manualWeightInput" class="form-control bg-dark text-light border-secondary shadow-none" value="74" min="40" max="160" placeholder="e.g. 74">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small text-muted">Fit Style Preference</label>
                                    <select id="manualFitPref" class="form-select bg-dark text-light border-secondary shadow-none">
                                        <option value="regular" selected>Standard / Regular Fit</option>
                                        <option value="snug">Snug / Tight Athletic Fit</option>
                                        <option value="oversized">Relaxed / Streetwear Oversized Fit</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <button class="btn btn-scan-glow w-100 py-2 fw-bold" onclick="SmartFitScanner.calculateManual()">
                                        <i class="fa-solid fa-bullseye me-1"></i> Calculate Exact Size & Match Clothes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-secondary justify-content-between">
                <button id="simulatedScanBtn" class="btn btn-outline-cyber btn-sm" onclick="SmartFitScanner.simulateScan()">
                    <i class="fa-solid fa-bolt me-1 text-warning"></i> Test Sample Benchmark Scan
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" onclick="SmartFitScanner.stopCamera()">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. SIZE DIAGNOSTICS & RECOMMENDATION MODAL -->
<!-- ========================================== -->
<div class="modal fade" id="sizeResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card border-secondary text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-neon"></i>
                    <span>AI Estimated Fit Profile &amp; Lowest Price Match</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Loading State -->
                <div id="sizeResultLoading" class="text-center py-5">
                    <div class="spinner-border text-neon mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h5 class="text-light">Generating Your AI Fit Profile...</h5>
                    <p class="text-muted small">Analyzing camera pose landmarks &amp; scanning shop database for lowest priced matches</p>
                </div>

                <!-- Result Content -->
                <div id="sizeResultContent" class="d-none">
                    <div class="row g-4 align-items-center">
                        <!-- Hero Size Card -->
                        <div class="col-md-5">
                            <div class="size-result-box text-center p-3 rounded" style="background: rgba(18, 24, 38, 0.85); border: 1px solid rgba(99, 102, 241, 0.3);">
                                <div class="text-secondary small fw-bold tracking-wider">RECOMMENDED BEST SIZE</div>
                                <div id="resHeroSize" class="size-hero-letter" style="font-size: 4.5rem; font-weight: 800; color: #818cf8; line-height: 1;">M</div>
                                <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                                    <span class="badge bg-emerald text-dark fw-bold px-3 py-1">BEST FIT</span>
                                    <span class="text-neon fw-bold" id="resConfidenceScore">96%</span>
                                </div>
                                <div class="confidence-bar-wrap mb-3">
                                    <div id="resConfidenceBar" class="confidence-bar-fill" style="width: 96%;"></div>
                                </div>

                                <!-- Category Recommendation Pills -->
                                <div class="p-2 bg-dark rounded border border-dark text-start">
                                    <div class="text-muted small mb-1 fw-bold" style="font-size: 0.7rem;">RECOMMENDED BY CLOTHING TYPE:</div>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <span class="category-fit-pill">👕 Tee: <strong class="text-neon ms-1" id="recTshirtSize">M</strong></span>
                                        <span class="category-fit-pill">👔 Polo: <strong class="text-cyan ms-1" id="recPoloSize">M</strong></span>
                                        <span class="category-fit-pill">🧥 Hoodie: <strong class="text-warning ms-1" id="recHoodieSize">L</strong></span>
                                    </div>
                                    <button id="saveFitToProfileBtn" class="btn btn-outline-cyber btn-sm w-100 py-1 d-flex align-items-center justify-content-center gap-2" style="font-size: 0.8rem;" onclick="SmartFitAuth.saveFitProfile()">
                                        <i class="fa-solid fa-cloud-arrow-up text-neon"></i>
                                        <span>Save to My Profile</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Body Measurements Details -->
                        <div class="col-md-7">
                            <h6 class="fw-bold mb-3 text-neon"><i class="fa-solid fa-id-card-clip me-1"></i> AI Estimated Fit Profile:</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 bg-dark rounded border border-dark">
                                        <div class="text-muted small">Height (Approx):</div>
                                        <div class="fw-bold text-white fs-6" id="resHeightVal">~174 cm</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-dark rounded border border-dark">
                                        <div class="text-muted small">Shoulder Frame:</div>
                                        <div class="fw-bold text-white fs-6" id="resShoulderVal">Medium (42-45 cm)</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-dark rounded border border-dark">
                                        <div class="text-muted small">Upper Body Build:</div>
                                        <div class="fw-bold text-white fs-6" id="resBuildVal">Regular Athletic</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-dark rounded border border-dark">
                                        <div class="text-muted small">Waist Profile:</div>
                                        <div class="fw-bold text-white fs-6" id="resWaistVal">~81 cm (32 in)</div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="fw-bold mb-2 text-secondary small">Size Fit Score Confidence:</h6>
                            <div id="resSizeDistribution">
                                <!-- Dynamic Breakdown bars -->
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Live Fine-Tuner Accordion -->
                    <div class="accordion custom-accordion mt-4" id="fineTuneAccordion">
                        <div class="accordion-item bg-dark border border-secondary rounded overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-dark text-light shadow-none py-2" type="button" data-bs-toggle="collapse" data-bs-target="#fineTuneCollapse">
                                    <i class="fa-solid fa-sliders me-2 text-neon"></i>
                                    <span>Fine-Tune Measurements &amp; Fit Preference</span>
                                </button>
                            </h2>
                            <div id="fineTuneCollapse" class="accordion-collapse collapse" data-bs-parent="#fineTuneAccordion">
                                <div class="accordion-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label d-flex justify-content-between small text-muted">
                                                <span>Adjust Shoulder Width</span>
                                                <span class="fw-bold text-neon" id="tuneShoulderVal">44.5 cm</span>
                                            </label>
                                            <input type="range" id="tuneShoulderSlider" class="form-range" min="36" max="56" step="0.5" value="44.5" oninput="SmartFitSizeEngine.onFineTuneChange()">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label d-flex justify-content-between small text-muted">
                                                <span>Adjust Height</span>
                                                <span class="fw-bold text-info" id="tuneHeightVal">174 cm</span>
                                            </label>
                                            <input type="range" id="tuneHeightSlider" class="form-range" min="145" max="205" step="1" value="174" oninput="SmartFitSizeEngine.onFineTuneChange()">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label small text-muted mb-1">Fit Preference:</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tuneFitPref" id="tf1" value="snug" onchange="SmartFitSizeEngine.onFineTuneChange()">
                                                    <label class="form-check-label small text-white" for="tf1">Snug / Athletic Fit</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tuneFitPref" id="tf2" value="regular" checked onchange="SmartFitSizeEngine.onFineTuneChange()">
                                                    <label class="form-check-label small text-white" for="tf2">Regular Fit</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="tuneFitPref" id="tf3" value="oversized" onchange="SmartFitSizeEngine.onFineTuneChange()">
                                                    <label class="form-check-label small text-white" for="tf3">Relaxed / Oversized</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 💰 Matching Recommended Products Ranked by Lowest Price -->
                    <div class="mt-4 pt-4 border-top border-secondary">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-tags text-warning"></i>
                                    <span>Matched Products (Ranked by Lowest Price)</span>
                                </h5>
                                <small class="text-secondary">Showing in-stock clothes that fit your profile &bull; 🥇 Best Price Guaranteed</small>
                            </div>
                        </div>
                        <div id="resRecommendedProducts" class="row g-3">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Back to Store</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. INTERACTIVE VIRTUAL TRY-ON MODAL       -->
<!-- ========================================== -->
<div class="modal fade" id="tryonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content glass-card border-secondary text-light">
            <div class="modal-header border-secondary">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-badge"><i class="fa-solid fa-shirt text-neon"></i></div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold">2D Virtual Fitting Preview</h5>
                        <small class="text-secondary" style="font-size: 0.75rem;">Interactive 2D Garment Overlay &amp; Real-time Webcam AR Preview</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="row g-4 align-items-center">
                    <!-- Left: Interactive TryOn Canvas Stage -->
                    <div class="col-lg-7">
                        <div class="tryon-stage-wrap">
                            <canvas id="tryonCanvas" width="500" height="550"></canvas>
                        </div>
                        <div class="text-center mt-2 text-muted small">
                            <i class="fa-solid fa-hand-pointer me-1 text-neon"></i> Drag garment with mouse/touch to position over torso.
                        </div>
                    </div>

                    <!-- Right: Garment Details & Alignment Controls -->
                    <div class="col-lg-5">
                        <div class="glass-card p-3 mb-3">
                            <span class="text-neon small fw-bold" id="tryonItemBrand">Carnage Apparel</span>
                            <h4 class="fw-bold my-1 text-white" id="tryonItemTitle">Essential Seamless 1/4 Zip Polo</h4>
                            <div class="fs-5 fw-bold text-white mb-2" id="tryonItemPrice">Rs. 5,500</div>
                        </div>

                        <!-- Quick Actions Bar -->
                        <div class="d-flex gap-2 mb-3">
                            <button id="tryonLiveARBtn" class="btn btn-outline-cyber btn-sm flex-fill" onclick="SmartFitTryOn.toggleLiveAR()">
                                <i class="fa-solid fa-camera me-1"></i> Live AR Mirror
                            </button>
                            <label class="btn btn-outline-cyber btn-sm flex-fill mb-0">
                                <i class="fa-solid fa-cloud-arrow-up me-1 text-neon"></i> Upload Photo
                                <input type="file" accept="image/*" class="d-none" onchange="SmartFitTryOn.handleUserPhotoUpload(this)">
                            </label>
                        </div>

                        <!-- Canvas Transform Controls -->
                        <div class="tryon-controls mb-3">
                            <h6 class="fw-bold text-secondary small mb-3">Interactive Fit Controls:</h6>
                            
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between small text-muted">
                                    <span>Garment Scale / Size</span>
                                    <span>Zoom</span>
                                </label>
                                <input type="range" id="tryonScaleSlider" class="form-range" min="0.6" max="1.6" step="0.05" value="1.0" oninput="SmartFitTryOn.updateScale(this.value)">
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between small text-muted">
                                    <span>Fabric Opacity / Blend</span>
                                    <span>Intensity</span>
                                </label>
                                <input type="range" id="tryonOpacitySlider" class="form-range" min="0.4" max="1.0" step="0.05" value="0.95" oninput="SmartFitTryOn.updateOpacity(this.value)">
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-cyber btn-sm flex-fill" onclick="SmartFitTryOn.resetTransform()">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Snap / Reset Fit
                                </button>
                                <button class="btn btn-scan-glow btn-sm flex-fill" onclick="SmartFitTryOn.downloadSnapshot()">
                                    <i class="fa-solid fa-download me-1"></i> Save Fit Photo
                                </button>
                            </div>
                            <button class="btn btn-outline-cyber btn-sm w-100 mt-2 d-flex align-items-center justify-content-center gap-2" onclick="SmartFitAuth.toggleWardrobe(SmartFitTryOn.currentItem ? SmartFitTryOn.currentItem.id : 0, SmartFitSizeEngine.currentResult ? SmartFitSizeEngine.currentResult.recommended_size : 'M')">
                                <i class="fa-solid fa-bookmark text-warning"></i>
                                <span>Save Look to My Wardrobe</span>
                            </button>
                            <a id="tryonWhatsAppBtn" href="https://wa.me/94771234567?text=Hi%20SmartFit%20AI,%20I%20would%20like%20to%20order%20this%20apparel!" target="_blank" class="btn btn-success btn-sm w-100 mt-2 d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-brands fa-whatsapp fs-6"></i>
                                <span>Order This Look via WhatsApp</span>
                            </a>
                        </div>

                        <!-- Garments Switcher Bar -->
                        <div class="p-3 bg-dark rounded border border-dark">
                            <div class="text-secondary small fw-bold mb-2">
                                <i class="fa-solid fa-layer-group text-neon me-1"></i> Switch Styles Instantly:
                            </div>
                            <div id="tryonClothesCarousel" class="d-flex overflow-auto pb-1" style="white-space: nowrap;">
                                <!-- Live loaded from store -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close Atelier</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 4. PRODUCT QUICK VIEW MODAL               -->
<!-- ========================================== -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card border-secondary text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold">Garment Specifications</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5 text-center">
                        <img id="qvItemImg" src="" class="img-fluid rounded border border-secondary" style="max-height: 380px; object-fit: cover;" alt="Product">
                    </div>
                    <div class="col-md-7">
                        <div class="text-neon small fw-bold" id="qvItemBrand">Carnage</div>
                        <h4 class="fw-bold text-white mb-2" id="qvItemTitle"></h4>
                        <div class="fs-4 fw-bold text-white mb-3" id="qvItemPrice"></div>
                        
                        <div class="mb-3">
                            <div class="text-muted small">Color: <strong class="text-white" id="qvItemColor"></strong></div>
                            <div class="text-muted small">Fabric Composition: <strong class="text-white" id="qvItemFabric"></strong></div>
                        </div>

                        <p class="text-secondary small mb-3" id="qvItemDesc"></p>

                        <div class="mb-3">
                            <div class="text-muted small mb-2 fw-bold">STOCK BY SIZE:</div>
                            <div id="qvSizesGrid" class="d-flex gap-2 flex-wrap"></div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button id="qvTryOnBtn" class="btn btn-scan-glow flex-fill d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-shirt"></i>
                                <span>Try On in Virtual Fitting Room</span>
                            </button>
                            <a id="qvWhatsAppBtn" href="https://wa.me/94771234567?text=Hi%20SmartFit%20AI,%20I%20would%20like%20to%20order!" target="_blank" class="btn btn-success d-flex align-items-center justify-content-center gap-2 px-3" title="Order via WhatsApp">
                                <i class="fa-brands fa-whatsapp fs-5"></i>
                                <span>Order</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 5. CUSTOMER AUTH MODAL (LOGIN / REGISTER)  -->
<!-- ========================================== -->
<div class="modal fade" id="authCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-secondary text-light">
            <div class="modal-header border-secondary pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-badge bg-primary bg-opacity-25 p-2 rounded-circle border border-primary">
                        <i class="fa-solid fa-user-shield text-neon fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold">Customer Portal</h5>
                        <small class="text-secondary" style="font-size: 0.78rem;">SmartFit LK &bull; Save Your Fit Profile &amp; Wardrobe</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4 pt-3">
                <!-- Segmented Tab Switcher -->
                <div class="auth-tabs-wrap mb-4">
                    <ul class="nav nav-pills w-100" role="tablist">
                        <li class="nav-item flex-fill">
                            <button class="nav-link auth-tab-link active w-100 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="pill" data-bs-target="#authLoginTab">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                <span>Sign In</span>
                            </button>
                        </li>
                        <li class="nav-item flex-fill">
                            <button class="nav-link auth-tab-link w-100 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="pill" data-bs-target="#authRegTab">
                                <i class="fa-solid fa-user-plus"></i>
                                <span>Create Account</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <!-- Sign In Tab -->
                    <div class="tab-pane fade show active" id="authLoginTab">
                        <div id="authLoginAlert" class="d-none mb-3"></div>
                        <form onsubmit="SmartFitAuth.handleLogin(event)">
                            <div class="mb-3">
                                <label class="auth-label">
                                    <i class="fa-solid fa-envelope text-neon"></i>
                                    <span>Email Address</span>
                                </label>
                                <input type="email" id="authLoginEmail" class="form-control auth-input shadow-none" placeholder="name@example.com" required autocomplete="email">
                            </div>
                            <div class="mb-3">
                                <label class="auth-label">
                                    <i class="fa-solid fa-lock text-neon"></i>
                                    <span>Password</span>
                                </label>
                                <input type="password" id="authLoginPassword" class="form-control auth-input shadow-none" placeholder="••••••••" required autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn btn-scan-glow w-100 py-2 fw-bold mt-2">
                                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Sign In to Account
                            </button>
                        </form>

                        <!-- Quick Demo Credentials Box -->
                        <div class="demo-credentials-card mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-bolt text-warning fs-6"></i>
                                <div>
                                    <div class="text-white small fw-bold" style="font-size: 0.8rem;">Demo Test Account</div>
                                    <div class="text-secondary" style="font-size: 0.72rem;">demo@smartfit.lk &bull; demo123</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-cyber py-1 px-2" style="font-size: 0.75rem;" onclick="SmartFitAuth.fillDemoCredentials()">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Fill
                            </button>
                        </div>
                    </div>

                    <!-- Register Tab -->
                    <div class="tab-pane fade" id="authRegTab">
                        <div id="authRegAlert" class="d-none mb-3"></div>
                        <form onsubmit="SmartFitAuth.handleRegister(event)">
                            <div class="mb-3">
                                <label class="auth-label">
                                    <i class="fa-solid fa-user text-neon"></i>
                                    <span>Full Name</span>
                                </label>
                                <input type="text" id="authRegName" class="form-control auth-input shadow-none" placeholder="e.g. Sandes Thameesha" required autocomplete="name">
                            </div>
                            <div class="mb-3">
                                <label class="auth-label">
                                    <i class="fa-solid fa-envelope text-neon"></i>
                                    <span>Email Address</span>
                                </label>
                                <input type="email" id="authRegEmail" class="form-control auth-input shadow-none" placeholder="name@example.com" required autocomplete="email">
                            </div>
                            <div class="mb-3">
                                <label class="auth-label">
                                    <i class="fa-solid fa-lock text-neon"></i>
                                    <span>Password (Min. 6 chars)</span>
                                </label>
                                <input type="password" id="authRegPassword" class="form-control auth-input shadow-none" minlength="6" placeholder="Create a secure password" required autocomplete="new-password">
                            </div>
                            <div class="mb-3">
                                <label class="auth-label">
                                    <i class="fa-solid fa-venus-mars text-neon"></i>
                                    <span>Apparel Preference</span>
                                </label>
                                <select id="authRegGender" class="form-select auth-input shadow-none">
                                    <option value="mens">Men's Apparel</option>
                                    <option value="womens">Women's Apparel</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-scan-glow w-100 py-2 fw-bold mt-2">
                                <i class="fa-solid fa-user-plus me-2"></i> Create Free Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 6. CUSTOMER FIT PROFILE MODAL              -->
<!-- ========================================== -->
<div class="modal fade" id="customerProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-secondary text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-id-card text-neon"></i>
                    <span>My AI Fit Profile</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="display-5 text-neon mb-2"><i class="fa-solid fa-circle-user"></i></div>
                    <h5 class="fw-bold text-white mb-0" id="profName">User</h5>
                    <div class="text-secondary small" id="profEmail">user@example.com</div>
                    <span class="badge bg-secondary mt-1" id="profGender">Men's</span>
                </div>

                <div class="p-3 bg-dark rounded border border-dark text-center mb-3">
                    <div class="text-muted small fw-bold tracking-wider">SAVED RECOMMENDED SIZE</div>
                    <div id="profSize" class="size-hero-letter text-neon my-1" style="font-size: 3.5rem; font-weight: 800; line-height: 1;">M</div>
                    <small class="text-muted">Automatically applied across Store &amp; Try-On</small>
                </div>

                <div class="row g-2 small">
                    <div class="col-6">
                        <div class="p-2 bg-dark rounded border border-dark">
                            <span class="text-muted">Height:</span>
                            <strong class="text-white d-block" id="profHeight">174 cm</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-dark rounded border border-dark">
                            <span class="text-muted">Shoulder Frame:</span>
                            <strong class="text-white d-block" id="profShoulder">44.5 cm</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-dark rounded border border-dark">
                            <span class="text-muted">Body Build:</span>
                            <strong class="text-white d-block" id="profBuild">Regular Athletic</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-dark rounded border border-dark">
                            <span class="text-muted">Fit Style:</span>
                            <strong class="text-white d-block" id="profFitPref">REGULAR</strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-outline-cyber btn-sm flex-fill" data-bs-dismiss="modal" onclick="openScannerModal()">
                        <i class="fa-solid fa-camera-viewfinder me-1 text-neon"></i> Re-Scan with AI Camera
                    </button>
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 7. CUSTOMER VIRTUAL WARDROBE MODAL        -->
<!-- ========================================== -->
<div class="modal fade" id="customerWardrobeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card border-secondary text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-shirt text-warning"></i>
                    <span>My Virtual Wardrobe</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-muted small mb-3">Saved clothing looks from your virtual fitting room sessions:</div>
                <div id="wardrobeItemsContainer" class="row g-3">
                    <!-- Populated dynamically -->
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>