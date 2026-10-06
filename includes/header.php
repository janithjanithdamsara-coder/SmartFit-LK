<?php
// SmartFit AI - Shared Header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | SmartFit AI' : 'SmartFit AI — Smart Clothing & Virtual Fitting System' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Custom Modern Cyber/Luxury Style -->
    <link rel="stylesheet" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/css/style.css?v=<?= time() ?>">
    
    <!-- MediaPipe Pose Dependencies for Client-Side AI Detection -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/pose/pose.js" crossorigin="anonymous"></script>
</head>
<body class="bg-dark text-light antialiased">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark smart-navbar sticky-top">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>index.php">
                <div class="brand-badge">
                    <i class="fa-solid fa-wand-magic-sparkles text-neon"></i>
                </div>
                <div>
                    <span class="brand-title">SMART<span class="text-neon">FIT</span> <span class="badge bg-primary text-white px-2 py-0 align-middle" style="font-size:0.68rem; letter-spacing: 1px;">LK</span></span>
                    <span class="brand-sub">AI FIT PROFILE & PRICE MATCH</span>
                </div>
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <i class="fa-solid fa-bars-staggered text-light"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-3">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'index.php' ? 'active' : '' ?>" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>index.php">
                            <i class="fa-solid fa-compass me-1"></i> Store Collection
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#scannerSection" onclick="openScannerModal(); return false;">
                            <i class="fa-solid fa-expand me-1 text-neon"></i> AI Body Scan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tryonSection" onclick="openTryonModal(); return false;">
                            <i class="fa-solid fa-shirt me-1 text-info"></i> Virtual Try-On
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#sizeChartModal">
                            <i class="fa-solid fa-ruler-combined me-1"></i> Size Guide
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <!-- Current Detected Size Indicator -->
                    <div id="navFitBadge" class="d-none animate__animated animate__fadeIn">
                        <span class="badge badge-fit px-3 py-2">
                            <i class="fa-solid fa-check-circle me-1 text-success"></i>
                            Fit: <strong id="navDetectedSize" class="text-white">L</strong> 
                            <span id="navDetectedConf" class="text-neon opacity-75 ms-1">(92%)</span>
                        </span>
                    </div>

                    <!-- AI Scan Trigger Button -->
                    <button class="btn btn-scan-glow btn-sm d-flex align-items-center gap-2 px-3 py-2" onclick="openScannerModal()">
                        <i class="fa-solid fa-camera-viewfinder animate__animated animate__pulse animate__infinite"></i>
                        <span>Scan My Size</span>
                    </button>
                    
                    <!-- Customer Account / Sign In -->
                    <div id="navCustomerContainer">
                        <?php if (!empty($_SESSION['customer_id'])): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-cyber btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-circle-user text-neon fs-6"></i>
                                    <span class="text-truncate" style="max-width: 120px;"><?= htmlspecialchars($_SESSION['customer_name'] ?? 'Account') ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                                    <li><a class="dropdown-item small" href="#" onclick="SmartFitAuth.openProfileModal(); return false;"><i class="fa-solid fa-id-card me-2 text-info"></i> My Fit Profile</a></li>
                                    <li><a class="dropdown-item small" href="#" onclick="SmartFitAuth.openWardrobeModal(); return false;"><i class="fa-solid fa-shirt me-2 text-warning"></i> My Wardrobe</a></li>
                                    <li><hr class="dropdown-divider border-secondary"></li>
                                    <li><a class="dropdown-item small text-danger" href="#" onclick="SmartFitAuth.logout(); return false;"><i class="fa-solid fa-right-from-bracket me-2"></i> Log Out</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-outline-cyber btn-sm px-3 py-2 d-flex align-items-center gap-1" onclick="SmartFitAuth.openAuthModal()">
                                <i class="fa-solid fa-user me-1 text-neon"></i>
                                <span>Sign In</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>