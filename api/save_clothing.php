<?php
require_once __DIR__ . '/db.php';
session_start();

// Check if admin is logged in
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Admin authentication required.']);
    exit;
}

try {
    $db = Database::getConnection();

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 1;
    $itemCode = isset($_POST['item_code']) ? trim($_POST['item_code']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : 'Carnage';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : 'mens';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '';
    $colorHex = isset($_POST['color_hex']) ? trim($_POST['color_hex']) : '#000000';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
    $comparePrice = !empty($_POST['compare_price']) ? (float)$_POST['compare_price'] : null;
    $imageUrl = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $overlayImageUrl = isset($_POST['overlay_image_url']) ? trim($_POST['overlay_image_url']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $fabricDetails = isset($_POST['fabric_details']) ? trim($_POST['fabric_details']) : '';
    $isFeatured = isset($_POST['is_featured']) ? (int)$_POST['is_featured'] : 1;

    // Handle file upload if provided
    if (!empty($_FILES['image_file']['name'])) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, WEBP, and SVG allowed.']);
            exit;
        }

        $uploadDir = __DIR__ . '/../assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            $imageUrl = 'assets/images/products/' . $fileName;
        }
    }

    if (empty($itemCode) || empty($name) || $price <= 0) {
        echo json_encode(['success' => false, 'error' => 'Please provide valid Item Code, Name and Price.']);
        exit;
    }

    if (empty($imageUrl)) {
        $imageUrl = 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=800';
    }

    // Preserve existing overlay if not explicitly overwritten
    if ($id > 0 && empty($overlayImageUrl)) {
        $prevStmt = $db->prepare("SELECT overlay_image_url FROM clothing_items WHERE id = :id");
        $prevStmt->execute([':id' => $id]);
        $prevOverlay = $prevStmt->fetchColumn();
        if (!empty($prevOverlay)) {
            $overlayImageUrl = $prevOverlay;
        }
    }

    if (empty($overlayImageUrl)) {
        $overlayImageUrl = $imageUrl;
    }

    if ($id > 0) {
        // Update existing
        $stmt = $db->prepare("UPDATE clothing_items SET 
            category_id = :cat, item_code = :code, name = :name, brand = :brand, 
            gender = :gender, color = :color, color_hex = :hex, price = :price, 
            compare_price = :cprice, image_url = :img, overlay_image_url = :oimg, 
            description = :desc, fabric_details = :fab, is_featured = :feat 
            WHERE id = :id");
        $stmt->execute([
            ':cat' => $categoryId, ':code' => $itemCode, ':name' => $name, ':brand' => $brand,
            ':gender' => $gender, ':color' => $color, ':hex' => $colorHex, ':price' => $price,
            ':cprice' => $comparePrice, ':img' => $imageUrl, ':oimg' => $overlayImageUrl,
            ':desc' => $description, ':fab' => $fabricDetails, ':feat' => $isFeatured, ':id' => $id
        ]);
        $itemId = $id;
    } else {
        // Insert new
        $stmt = $db->prepare("INSERT INTO clothing_items 
            (category_id, item_code, name, brand, gender, color, color_hex, price, compare_price, image_url, overlay_image_url, description, fabric_details, is_featured) 
            VALUES (:cat, :code, :name, :brand, :gender, :color, :hex, :price, :cprice, :img, :oimg, :desc, :fab, :feat)");
        $stmt->execute([
            ':cat' => $categoryId, ':code' => $itemCode, ':name' => $name, ':brand' => $brand,
            ':gender' => $gender, ':color' => $color, ':hex' => $colorHex, ':price' => $price,
            ':cprice' => $comparePrice, ':img' => $imageUrl, ':oimg' => $overlayImageUrl,
            ':desc' => $description, ':fab' => $fabricDetails, ':feat' => $isFeatured
        ]);
        $itemId = (int)$db->lastInsertId();
    }

    // Save sizes and stocks
    if (isset($_POST['sizes']) && is_array($_POST['sizes'])) {
        $db->prepare("DELETE FROM clothing_sizes WHERE item_id = :item_id")->execute([':item_id' => $itemId]);
        $sizeInsert = $db->prepare("INSERT INTO clothing_sizes (item_id, size_name, stock_qty) VALUES (:item_id, :size, :qty)");
        foreach ($_POST['sizes'] as $sizeName => $stockQty) {
            $sizeInsert->execute([
                ':item_id' => $itemId,
                ':size' => strtoupper(trim($sizeName)),
                ':qty' => max(0, (int)$stockQty)
            ]);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Clothing item saved successfully!',
        'item_id' => $itemId
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}