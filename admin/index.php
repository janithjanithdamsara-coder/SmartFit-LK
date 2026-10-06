<?php
require_once __DIR__ . '/../includes/auth.php';
checkAdminAuth();
require_once __DIR__ . '/../api/db.php';

$db = Database::getConnection();

// Fetch summary metrics
$totalProducts = (int)$db->query("SELECT COUNT(*) FROM clothing_items")->fetchColumn();
$totalStock = (int)$db->query("SELECT SUM(stock_qty) FROM clothing_sizes")->fetchColumn();
$totalScans = (int)$db->query("SELECT COUNT(*) FROM customer_scans")->fetchColumn();

// Recent 10 Scans
$recentScans = $db->query("SELECT * FROM customer_scans ORDER BY id DESC LIMIT 10")->fetchAll();

// Low stock items (< 5 units)
$lowStock = $db->query("SELECT c.name, c.item_code, cs.size_name, cs.stock_qty 
                        FROM clothing_sizes cs 
                        JOIN clothing_items c ON cs.item_id = c.id 
                        WHERE cs.stock_qty < 5 
                        ORDER BY cs.stock_qty ASC LIMIT 6")->fetchAll();

// Size Distribution
$sizeDist = $db->query("SELECT recommended_size, COUNT(*) as count FROM customer_scans GROUP BY recommended_size ORDER BY count DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — SmartFit AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-dark text-light">
    <!-- Admin Nav -->
    <nav class="navbar navbar-expand-lg navbar-dark smart-navbar sticky-top">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <div class="brand-badge"><i class="fa-solid fa-chart-line text-neon"></i></div>
                <div>
                    <span class="brand-title">SMART<span class="text-neon">FIT</span></span>
                    <span class="brand-sub">ADMIN PORTAL</span>
                </div>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <i class="fa-solid fa-bars text-light"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-3">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clothes.php"><i class="fa-solid fa-shirt me-1"></i> Clothing Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="size_charts.php"><i class="fa-solid fa-ruler me-1"></i> Size Charts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Live Store</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <span class="text-secondary small d-none d-md-inline">
                        <i class="fa-solid fa-circle-user text-neon me-1"></i> <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
                    </span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm px-3">
                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container-fluid px-lg-5 py-4">
        <!-- Page Title & Quick Actions -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1">System Intelligence & Operations</h3>
                <p class="text-secondary small mb-0">Live analytics on garment stock, customer body scans, and size matching precision.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="clothes.php?action=add" class="btn btn-scan-glow btn-sm px-3 py-2 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add New Garment
                </a>
            </div>
        </div>

        <!-- 4 KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">TOTAL STYLES</div>
                        <div class="fs-3 fw-bold text-white mt-1"><?= $totalProducts ?></div>
                        <small class="text-neon"><i class="fa-solid fa-shirt me-1"></i> Active Catalog</small>
                    </div>
                    <div class="brand-badge" style="width: 48px; height: 48px;"><i class="fa-solid fa-boxes-stacked text-neon fs-5"></i></div>
                </div>
            </div>
            
            <div class="col-xl-3 col-sm-6">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">TOTAL STOCK UNITS</div>
                        <div class="fs-3 fw-bold text-white mt-1"><?= $totalStock ?></div>
                        <small class="text-emerald"><i class="fa-solid fa-check me-1"></i> Across All Sizes</small>
                    </div>
                    <div class="brand-badge" style="width: 48px; height: 48px;"><i class="fa-solid fa-warehouse text-emerald fs-5"></i></div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">AI BODY SCANS</div>
                        <div class="fs-3 fw-bold text-white mt-1"><?= $totalScans ?></div>
                        <small class="text-info"><i class="fa-solid fa-camera me-1"></i> Camera Landmarks</small>
                    </div>
                    <div class="brand-badge" style="width: 48px; height: 48px;"><i class="fa-solid fa-expand text-info fs-5"></i></div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="glass-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">AVG CONFIDENCE</div>
                        <div class="fs-3 fw-bold text-white mt-1">94.2%</div>
                        <small class="text-warning"><i class="fa-solid fa-bolt me-1"></i> Fit Precision</small>
                    </div>
                    <div class="brand-badge" style="width: 48px; height: 48px;"><i class="fa-solid fa-award text-warning fs-5"></i></div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row g-4">
            <!-- Recent Body Scans Log -->
            <div class="col-lg-8">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-neon"></i>Recent Customer Body Scans</h5>
                        <span class="badge bg-secondary"><?= count($recentScans) ?> Recent</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle custom-table">
                            <thead>
                                <tr>
                                    <th>Scan #</th>
                                    <th>Gender</th>
                                    <th>Shoulder (cm)</th>
                                    <th>Chest (cm)</th>
                                    <th>Fit Recommendation</th>
                                    <th>Confidence</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentScans)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No body scans recorded yet. Try scanning from the storefront!</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentScans as $scan): ?>
                                        <tr>
                                            <td class="text-muted">#<?= $scan['id'] ?></td>
                                            <td>
                                                <span class="badge bg-dark border border-secondary text-uppercase">
                                                    <?= htmlspecialchars($scan['gender']) ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold"><?= $scan['measured_shoulder'] ?> cm</td>
                                            <td><?= $scan['measured_chest'] ?> cm</td>
                                            <td>
                                                <span class="badge bg-primary px-2 py-1">
                                                    Size <?= htmlspecialchars($scan['recommended_size']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-emerald fw-bold"><?= $scan['confidence_score'] ?>%</span>
                                            </td>
                                            <td class="text-secondary small"><?= $scan['scanned_at'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Size Distribution & Low Stock Alerts -->
            <div class="col-lg-4">
                <!-- Size Distribution -->
                <div class="glass-card p-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie me-2 text-neon"></i>Customer Size Demand</h5>
                    <?php if (empty($sizeDist)): ?>
                        <p class="text-muted small">No scan data available yet.</p>
                    <?php else: ?>
                        <?php foreach ($sizeDist as $sd): ?>
                            <?php 
                                $pct = $totalScans > 0 ? round(($sd['count'] / $totalScans) * 100) : 0;
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold text-white">Size <?= htmlspecialchars($sd['recommended_size']) ?></span>
                                    <span class="text-neon"><?= $sd['count'] ?> scans (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.08);">
                                    <div class="progress-bar bg-primary" style="width: <?= $pct ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Low Stock Alert Box -->
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-3 text-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>Low Stock Warnings</h5>
                    <?php if (empty($lowStock)): ?>
                        <div class="p-3 bg-dark rounded border border-dark text-center text-secondary small">
                            <i class="fa-solid fa-circle-check text-success me-1"></i> All garment sizes are well stocked!
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush bg-transparent">
                            <?php foreach ($lowStock as $ls): ?>
                                <div class="list-group-item bg-transparent text-light border-secondary px-0 py-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="small fw-bold text-truncate" style="max-width: 180px;"><?= htmlspecialchars($ls['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($ls['item_code']) ?> • Size <?= htmlspecialchars($ls['size_name']) ?></small>
                                    </div>
                                    <span class="badge bg-danger"><?= $ls['stock_qty'] ?> left</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>