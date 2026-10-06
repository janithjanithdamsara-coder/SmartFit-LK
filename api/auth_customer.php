<?php
// SmartFit LK - Customer Authentication & Fit Profile API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$action = isset($_GET['action']) ? trim($_GET['action']) : '';
if (empty($action) && isset($_POST['action'])) {
    $action = trim($_POST['action']);
}

// Support JSON input as well
$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);
if (is_array($inputData) && !empty($inputData['action'])) {
    $action = trim($inputData['action']);
}

try {
    $db = Database::getConnection();

    switch ($action) {
        // ==========================================
        // 1. REGISTER NEW CUSTOMER
        // ==========================================
        case 'register':
            $fullName = trim($inputData['full_name'] ?? $_POST['full_name'] ?? '');
            $email = strtolower(trim($inputData['email'] ?? $_POST['email'] ?? ''));
            $password = trim($inputData['password'] ?? $_POST['password'] ?? '');
            $gender = trim($inputData['gender'] ?? $_POST['gender'] ?? 'mens');
            $phone = trim($inputData['phone'] ?? $_POST['phone'] ?? '');

            if (empty($fullName) || empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'Please provide full name, email, and password.']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'error' => 'Please enter a valid email address.']);
                exit;
            }

            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
                exit;
            }

            // Check if email already registered
            $stmt = $db->prepare("SELECT id FROM customers WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'This email is already registered. Please log in.']);
                exit;
            }

            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO customers (full_name, email, password, phone, gender) VALUES (:name, :email, :pass, :phone, :gender)");
            $ins->execute([
                ':name'   => $fullName,
                ':email'  => $email,
                ':pass'   => $passHash,
                ':phone'  => $phone,
                ':gender' => $gender
            ]);
            $customerId = $db->lastInsertId();

            // Set session
            $_SESSION['customer_id'] = $customerId;
            $_SESSION['customer_name'] = $fullName;
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_gender'] = $gender;

            // Fetch created profile
            $profStmt = $db->prepare("SELECT id, full_name, email, phone, gender, saved_height, saved_shoulder, saved_chest, saved_waist, recommended_size, fit_preference, body_build FROM customers WHERE id = :id");
            $profStmt->execute([':id' => $customerId]);
            $customer = $profStmt->fetch();

            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! Welcome to SmartFit LK.',
                'customer' => $customer
            ]);
            break;

        // ==========================================
        // 2. CUSTOMER LOGIN
        // ==========================================
        case 'login':
            $email = strtolower(trim($inputData['email'] ?? $_POST['email'] ?? ''));
            $password = trim($inputData['password'] ?? $_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'Please enter email and password.']);
                exit;
            }

            $stmt = $db->prepare("SELECT * FROM customers WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
                exit;
            }

            // Set session
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['full_name'];
            $_SESSION['customer_email'] = $user['email'];
            $_SESSION['customer_gender'] = $user['gender'];

            unset($user['password']);

            echo json_encode([
                'success' => true,
                'message' => 'Logged in successfully!',
                'customer' => $user
            ]);
            break;

        // ==========================================
        // 3. GET CURRENT PROFILE (OR CHECK SESSION)
        // ==========================================
        case 'get_profile':
            if (empty($_SESSION['customer_id'])) {
                echo json_encode(['success' => false, 'logged_in' => false]);
                exit;
            }

            $stmt = $db->prepare("SELECT id, full_name, email, phone, gender, saved_height, saved_shoulder, saved_chest, saved_waist, recommended_size, fit_preference, body_build FROM customers WHERE id = :id");
            $stmt->execute([':id' => $_SESSION['customer_id']]);
            $user = $stmt->fetch();

            if (!$user) {
                unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);
                echo json_encode(['success' => false, 'logged_in' => false]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'logged_in' => true,
                'customer' => $user
            ]);
            break;

        // ==========================================
        // 4. SAVE FIT PROFILE TO ACCOUNT
        // ==========================================
        case 'save_fit_profile':
            if (empty($_SESSION['customer_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please log in to save your Fit Profile.']);
                exit;
            }

            $h = (float)($inputData['height_cm'] ?? $_POST['height_cm'] ?? 172.0);
            $sh = (float)($inputData['shoulder_cm'] ?? $_POST['shoulder_cm'] ?? 44.0);
            $ch = (float)($inputData['chest_cm'] ?? $_POST['chest_cm'] ?? 98.0);
            $w = (float)($inputData['waist_cm'] ?? $_POST['waist_cm'] ?? 80.0);
            $size = strtoupper(trim($inputData['recommended_size'] ?? $_POST['recommended_size'] ?? 'M'));
            $fit = trim($inputData['fit_preference'] ?? $_POST['fit_preference'] ?? 'regular');
            $build = trim($inputData['body_build'] ?? $_POST['body_build'] ?? 'Regular Athletic');

            $stmt = $db->prepare("UPDATE customers SET 
                saved_height = :h,
                saved_shoulder = :sh,
                saved_chest = :ch,
                saved_waist = :w,
                recommended_size = :size,
                fit_preference = :fit,
                body_build = :build
                WHERE id = :id");

            $stmt->execute([
                ':h'     => $h,
                ':sh'    => $sh,
                ':ch'    => $ch,
                ':w'     => $w,
                ':size'  => $size,
                ':fit'   => $fit,
                ':build' => $build,
                ':id'    => $_SESSION['customer_id']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Fit Profile permanently saved to your account! Your store will now automatically personalize for Size ' . $size . '.',
                'saved_size' => $size
            ]);
            break;

        // ==========================================
        // 5. CUSTOMER LOGOUT
        // ==========================================
        case 'logout':
            unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email'], $_SESSION['customer_gender']);
            echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
            break;

        // ==========================================
        // 6. TOGGLE WARDROBE (SAVE / UNSAVE TRY-ON LOOK)
        // ==========================================
        case 'toggle_wardrobe':
            if (empty($_SESSION['customer_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please log in to save items to your wardrobe.']);
                exit;
            }

            $clothingId = (int)($inputData['clothing_id'] ?? $_POST['clothing_id'] ?? 0);
            $savedSize = trim($inputData['saved_size'] ?? $_POST['saved_size'] ?? 'M');

            if ($clothingId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid clothing item.']);
                exit;
            }

            $chk = $db->prepare("SELECT id FROM customer_wardrobe WHERE customer_id = :cid AND clothing_id = :clid");
            $chk->execute([':cid' => $_SESSION['customer_id'], ':clid' => $clothingId]);
            $item = $chk->fetch();

            if ($item) {
                $del = $db->prepare("DELETE FROM customer_wardrobe WHERE id = :id");
                $del->execute([':id' => $item['id']]);
                echo json_encode(['success' => true, 'saved' => false, 'message' => 'Removed from your Wardrobe.']);
            } else {
                $ins = $db->prepare("INSERT INTO customer_wardrobe (customer_id, clothing_id, saved_size) VALUES (:cid, :clid, :sz)");
                $ins->execute([':cid' => $_SESSION['customer_id'], ':clid' => $clothingId, ':sz' => $savedSize]);
                echo json_encode(['success' => true, 'saved' => true, 'message' => 'Added to your Wardrobe!']);
            }
            break;

        // ==========================================
        // 7. GET WARDROBE ITEMS
        // ==========================================
        case 'get_wardrobe':
            if (empty($_SESSION['customer_id'])) {
                echo json_encode(['success' => false, 'items' => []]);
                exit;
            }

            $sql = "SELECT w.id as wardrobe_id, w.saved_size, w.created_at as saved_date,
                           c.*, cat.name as category_name
                    FROM customer_wardrobe w
                    JOIN clothing_items c ON w.clothing_id = c.id
                    JOIN categories cat ON c.category_id = cat.id
                    WHERE w.customer_id = :cid
                    ORDER BY w.id DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute([':cid' => $_SESSION['customer_id']]);
            $wardrobeItems = $stmt->fetchAll();

            echo json_encode(['success' => true, 'items' => $wardrobeItems]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
