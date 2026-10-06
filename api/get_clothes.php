<?php
require_once __DIR__ . '/db.php';

try {
    $db = Database::getConnection();
    
    $gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $size = isset($_GET['size']) ? strtoupper(trim($_GET['size'])) : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $featured = isset($_GET['featured']) ? (int)$_GET['featured'] : null;

    $where = ["1=1"];
    $params = [];

    if (!empty($gender) && $gender !== 'all') {
        $where[] = "(c.gender = :gender OR c.gender = 'unisex')";
        $params[':gender'] = $gender;
    }

    if (!empty($category) && $category !== 'all') {
        if (is_numeric($category)) {
            $where[] = "c.category_id = :category";
            $params[':category'] = (int)$category;
        } else {
            $where[] = "cat.slug = :category";
            $params[':category'] = $category;
        }
    }

    if (!empty($search)) {
        $where[] = "(c.name LIKE :search OR c.item_code LIKE :search OR c.color LIKE :search OR c.description LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    if ($featured !== null) {
        $where[] = "c.is_featured = :featured";
        $params[':featured'] = $featured;
    }

    $whereSql = implode(' AND ', $where);

    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug
            FROM clothing_items c
            JOIN categories cat ON c.category_id = cat.id
            WHERE $whereSql
            ORDER BY c.id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // Fetch sizes for each item
    $results = [];
    foreach ($items as $item) {
        $sizeStmt = $db->prepare("SELECT size_name, stock_qty FROM clothing_sizes WHERE item_id = :item_id ORDER BY id ASC");
        $sizeStmt->execute([':item_id' => $item['id']]);
        $sizes = $sizeStmt->fetchAll();

        $sizeMap = [];
        $hasSelectedSizeInStock = false;
        $totalStock = 0;

        foreach ($sizes as $s) {
            $sizeMap[$s['size_name']] = (int)$s['stock_qty'];
            $totalStock += (int)$s['stock_qty'];
            if (!empty($size) && $s['size_name'] === $size && (int)$s['stock_qty'] > 0) {
                $hasSelectedSizeInStock = true;
            }
        }

        $item['sizes'] = $sizeMap;
        $item['total_stock'] = $totalStock;
        $item['in_stock'] = $totalStock > 0;

        // If filtering by size, only include if that size is in stock
        if (!empty($size) && !$hasSelectedSizeInStock) {
            continue;
        }

        $results[] = $item;
    }

    echo json_encode([
        'success' => true,
        'count' => count($results),
        'filter_size' => $size,
        'data' => $results
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}