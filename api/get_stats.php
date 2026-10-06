<?php
require_once __DIR__ . '/db.php';

try {
    $db = Database::getConnection();

    // Total Products
    $totalProducts = (int)$db->query("SELECT COUNT(*) FROM clothing_items")->fetchColumn();

    // Total Stock Units
    $totalStock = (int)$db->query("SELECT SUM(stock_qty) FROM clothing_sizes")->fetchColumn();

    // Total Scans
    $totalScans = (int)$db->query("SELECT COUNT(*) FROM customer_scans")->fetchColumn();

    // Scans Today (Cross-compatible MySQL & SQLite)
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $scansToday = (int)$db->query("SELECT COUNT(*) FROM customer_scans WHERE DATE(scanned_at) = DATE('now', 'localtime')")->fetchColumn();
    } else {
        $scansToday = (int)$db->query("SELECT COUNT(*) FROM customer_scans WHERE DATE(scanned_at) = CURDATE()")->fetchColumn();
    }

    // Size Breakdown Distribution
    $sizeDistStmt = $db->query("SELECT recommended_size, COUNT(*) as count FROM customer_scans GROUP BY recommended_size ORDER BY count DESC");
    $sizeDistribution = $sizeDistStmt->fetchAll();

    // Recent Scans
    $recentScansStmt = $db->query("SELECT * FROM customer_scans ORDER BY id DESC LIMIT 8");
    $recentScans = $recentScansStmt->fetchAll();

    // Low stock items (stock < 5)
    $lowStockStmt = $db->query("SELECT c.name, c.item_code, cs.size_name, cs.stock_qty 
                                FROM clothing_sizes cs 
                                JOIN clothing_items c ON cs.item_id = c.id 
                                WHERE cs.stock_qty < 5 
                                ORDER BY cs.stock_qty ASC LIMIT 5");
    $lowStock = $lowStockStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'total_scans' => $totalScans,
            'scans_today' => $scansToday,
            'size_distribution' => $sizeDistribution,
            'recent_scans' => $recentScans,
            'low_stock_alerts' => $lowStock
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}