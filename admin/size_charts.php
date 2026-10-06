<?php
require_once __DIR__ . '/../includes/auth.php';
checkAdminAuth();
require_once __DIR__ . '/../api/db.php';

$db = Database::getConnection();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charts'])) {
    try {
        $upStmt = $db->prepare("UPDATE size_charts SET 
            min_shoulder_cm = :min_sh, max_shoulder_cm = :max_sh,
            min_chest_cm = :min_ch, max_chest_cm = :max_ch,
            min_waist_cm = :min_w, max_waist_cm = :max_w,
            min_height_cm = :min_h, max_height_cm = :max_h,
            fit_description = :desc
            WHERE id = :id");

        foreach ($_POST['charts'] as $id => $row) {
            $upStmt->execute([
                ':min_sh' => (float)$row['min_shoulder_cm'],
                ':max_sh' => (float)$row['max_shoulder_cm'],
                ':min_ch' => (float)$row['min_chest_cm'],
                ':max_ch' => (float)$row['max_chest_cm'],
                ':min_w'  => (float)$row['min_waist_cm'],
                ':max_w'  => (float)$row['max_waist_cm'],
                ':min_h'  => (float)$row['min_height_cm'],
                ':max_h'  => (float)$row['max_height_cm'],
                ':desc'   => trim($row['fit_description']),
                ':id'     => (int)$id
            ]);
        }
        $message = 'Size chart calibrations updated successfully!';
    } catch (Exception $e) {
        $message = 'Error updating size charts: ' . $e->getMessage();
    }
}

// Fetch charts
$mensCharts = $db->query("SELECT * FROM size_charts WHERE gender = 'mens' ORDER BY id ASC")->fetchAll();
$womensCharts = $db->query("SELECT * FROM size_charts WHERE gender = 'womens' ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Size Chart Calibration — SmartFit AI Admin</title>
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
                <div class="brand-badge"><i class="fa-solid fa-ruler text-neon"></i></div>
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
                        <a class="nav-link" href="index.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clothes.php"><i class="fa-solid fa-shirt me-1"></i> Clothing Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="size_charts.php"><i class="fa-solid fa-ruler me-1"></i> Size Charts</a>
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

    <main class="container-fluid px-lg-5 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1">Size Chart Thresholds & Calibration</h3>
                <p class="text-secondary small mb-0">Fine-tune anatomical measurements (in CM) used by the MediaPipe AI recommendation engine.</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success py-2 small mb-4">
                <i class="fa-solid fa-circle-check me-1"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="size_charts.php">
            <!-- Men's Table -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-3 text-neon"><i class="fa-solid fa-mars me-2"></i>Men's Standard Sizing Chart (CM)</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle custom-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Size</th>
                                <th>Min Shoulder</th>
                                <th>Max Shoulder</th>
                                <th>Min Chest</th>
                                <th>Max Chest</th>
                                <th>Min Waist</th>
                                <th>Max Waist</th>
                                <th>Min Height</th>
                                <th>Max Height</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mensCharts as $row): ?>
                                <tr>
                                    <td><span class="badge bg-primary fs-6"><?= htmlspecialchars($row['size_name']) ?></span></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][min_shoulder_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['min_shoulder_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][max_shoulder_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['max_shoulder_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][min_chest_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['min_chest_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][max_chest_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['max_chest_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][min_waist_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['min_waist_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][max_waist_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['max_waist_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][min_height_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['min_height_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][max_height_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['max_height_cm'] ?>"></td>
                                    <td><input type="text" name="charts[<?= $row['id'] ?>][fit_description]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= htmlspecialchars($row['fit_description']) ?>"></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Women's Table -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-3 text-info"><i class="fa-solid fa-venus me-2"></i>Women's Standard Sizing Chart (CM)</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle custom-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Size</th>
                                <th>Min Shoulder</th>
                                <th>Max Shoulder</th>
                                <th>Min Chest</th>
                                <th>Max Chest</th>
                                <th>Min Waist</th>
                                <th>Max Waist</th>
                                <th>Min Height</th>
                                <th>Max Height</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($womensCharts as $row): ?>
                                <tr>
                                    <td><span class="badge bg-info text-dark fs-6"><?= htmlspecialchars($row['size_name']) ?></span></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][min_shoulder_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['min_shoulder_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][max_shoulder_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['max_shoulder_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][min_chest_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['min_chest_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][max_chest_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['max_chest_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][min_waist_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['min_waist_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][max_waist_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['max_waist_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][min_height_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['min_height_cm'] ?>"></td>
                                    <td><input type="number" step="0.1" name="charts[<?= $row['id'] ?>][max_height_cm]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= $row['max_height_cm'] ?>"></td>
                                    <td><input type="text" name="charts[<?= $row['id'] ?>][fit_description]" class="form-control form-control-sm bg-dark text-light border-secondary" value="<?= htmlspecialchars($row['fit_description']) ?>"></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-scan-glow px-4 py-2">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Chart Thresholds
                </button>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>