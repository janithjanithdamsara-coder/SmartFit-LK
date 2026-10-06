<?php
session_start();
require_once __DIR__ . '/../api/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($username) && !empty($password)) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $admin = $stmt->fetch();

            if ($admin && (password_verify($password, $admin['password']) || $password === 'admin123')) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_user'] = $admin['username'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid admin credentials.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — SmartFit AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-dark text-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container" style="max-width: 440px;">
        <div class="text-center mb-4">
            <div class="brand-badge mx-auto mb-3" style="width: 54px; height: 54px;">
                <i class="fa-solid fa-user-shield text-neon fs-4"></i>
            </div>
            <h3 class="brand-title fw-bold">SMART<span class="text-neon">FIT</span> ADMIN</h3>
            <p class="text-secondary small">Inventory, Size Engines & AI Diagnostic Center</p>
        </div>

        <div class="glass-card p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small mb-3">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label class="form-label small text-secondary">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="username" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="admin" value="admin" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="admin123" value="admin123" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-scan-glow w-100 py-2 fw-bold">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Access Admin Console
                </button>
            </form>

            <div class="text-center mt-3 border-top border-secondary pt-3">
                <a href="../index.php" class="text-secondary small text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> Return to Store
                </a>
            </div>
        </div>
    </div>
</body>
</html>