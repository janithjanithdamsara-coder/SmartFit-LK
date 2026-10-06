<?php
// SmartFit AI - Update Overlay Image URLs in Database
require_once __DIR__ . '/api/db.php';

try {
    $db = Database::getConnection();
    
    $overlays = [
        1 => 'assets/images/products/polo_black.svg',
        2 => 'assets/images/products/polo_grey.svg',
        3 => 'assets/images/products/polo_white.svg',
        4 => 'assets/images/products/polo_grey.svg',
        5 => 'assets/images/products/crop_top.svg',
        6 => 'assets/images/products/hoodie_black.svg',
        7 => 'assets/images/products/polo_black.svg',
        8 => 'assets/images/products/oversized_tee.svg'
    ];

    $stmt = $db->prepare("UPDATE clothing_items SET overlay_image_url = :overlay WHERE id = :id");
    foreach ($overlays as $id => $overlay) {
        $stmt->execute([':overlay' => $overlay, ':id' => $id]);
        echo "Updated Item #$id with overlay: $overlay\n";
    }

    echo "Database updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
