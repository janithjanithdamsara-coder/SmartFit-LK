<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = Database::getConnection();

    // Read input from POST JSON or GET
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);

    $gender         = isset($inputData['gender']) ? strtolower(trim($inputData['gender'])) : (isset($_GET['gender']) ? strtolower(trim($_GET['gender'])) : 'mens');
    $shoulderCm     = isset($inputData['shoulder_cm']) ? (float)$inputData['shoulder_cm'] : (isset($_GET['shoulder_cm']) ? (float)$_GET['shoulder_cm'] : 0);
    $chestCm        = isset($inputData['chest_cm']) ? (float)$inputData['chest_cm'] : (isset($_GET['chest_cm']) ? (float)$_GET['chest_cm'] : 0);
    $waistCm        = isset($inputData['waist_cm']) ? (float)$inputData['waist_cm'] : (isset($_GET['waist_cm']) ? (float)$_GET['waist_cm'] : 0);
    $heightCm       = isset($inputData['height_cm']) ? (float)$inputData['height_cm'] : (isset($_GET['height_cm']) ? (float)$_GET['height_cm'] : 0);
    $fitPreference  = isset($inputData['fit_preference']) ? strtolower(trim($inputData['fit_preference'])) : (isset($_GET['fit_preference']) ? strtolower(trim($_GET['fit_preference'])) : 'regular');
    $categoryFilter = isset($inputData['category']) ? trim($inputData['category']) : (isset($_GET['category']) ? trim($_GET['category']) : 'all');

    if ($shoulderCm <= 0 && $heightCm <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Please provide valid body measurements (shoulder_cm or height_cm).'
        ]);
        exit;
    }

    // Anthropometric estimations if chest/waist/height are not directly measured
    if ($chestCm <= 0 && $shoulderCm > 0) {
        $chestCm = $gender === 'womens' ? ($shoulderCm * 2.25) : ($shoulderCm * 2.22);
    }
    if ($waistCm <= 0 && $chestCm > 0) {
        $waistCm = $gender === 'womens' ? ($chestCm * 0.78) : ($chestCm * 0.82);
    }
    if ($heightCm <= 0 && $shoulderCm > 0) {
        $heightCm = $gender === 'womens' ? ($shoulderCm * 4.1) : ($shoulderCm * 3.85);
    }

    // Fetch size charts for gender
    $stmt = $db->prepare("SELECT * FROM size_charts WHERE gender = :gender ORDER BY id ASC");
    $stmt->execute([':gender' => $gender]);
    $charts = $stmt->fetchAll();

    if (empty($charts)) {
        $stmt = $db->prepare("SELECT * FROM size_charts WHERE gender = 'mens' ORDER BY id ASC");
        $stmt->execute();
        $charts = $stmt->fetchAll();
    }

    $bestSize = 'M';
    $highestConfidence = 0.0;
    $sizeScores = [];

    // Fit preference modifier (oversized pushes toward larger size, snug pushes toward tighter)
    $fitOffset = 0.0;
    if ($fitPreference === 'oversized') $fitOffset = 1.8;
    if ($fitPreference === 'snug') $fitOffset = -1.5;

    $adjustedShoulder = $shoulderCm + $fitOffset;

    foreach ($charts as $chart) {
        $sizeName = $chart['size_name'];

        // Measure fit difference for shoulder (40% weight)
        $midShoulder = ($chart['min_shoulder_cm'] + $chart['max_shoulder_cm']) / 2;
        $rangeShoulder = max(1.0, ($chart['max_shoulder_cm'] - $chart['min_shoulder_cm']) / 2);
        $shoulderDiff = abs($adjustedShoulder - $midShoulder);
        $shoulderScore = max(0, 100 - ($shoulderDiff / $rangeShoulder) * 25);

        // Measure fit difference for chest (35% weight)
        $midChest = ($chart['min_chest_cm'] + $chart['max_chest_cm']) / 2;
        $rangeChest = max(1.0, ($chart['max_chest_cm'] - $chart['min_chest_cm']) / 2);
        $chestDiff = abs($chestCm - $midChest);
        $chestScore = max(0, 100 - ($chestDiff / $rangeChest) * 25);

        // Measure fit difference for waist (15% weight)
        $midWaist = ($chart['min_waist_cm'] + $chart['max_waist_cm']) / 2;
        $rangeWaist = max(1.0, ($chart['max_waist_cm'] - $chart['min_waist_cm']) / 2);
        $waistDiff = abs($waistCm - $midWaist);
        $waistScore = max(0, 100 - ($waistDiff / $rangeWaist) * 25);

        // Measure fit difference for height (10% weight)
        $midHeight = ($chart['min_height_cm'] + $chart['max_height_cm']) / 2;
        $rangeHeight = max(1.0, ($chart['max_height_cm'] - $chart['min_height_cm']) / 2);
        $heightDiff = abs($heightCm - $midHeight);
        $heightScore = max(0, 100 - ($heightDiff / $rangeHeight) * 25);

        // Weighted Total Fit Score
        $totalScore = ($shoulderScore * 0.40) + ($chestScore * 0.35) + ($waistScore * 0.15) + ($heightScore * 0.10);
        $totalScore = round(min(98.5, max(10.0, $totalScore)), 1);

        $sizeScores[$sizeName] = [
            'score' => $totalScore,
            'description' => $chart['fit_description'],
            'shoulder_range' => "{$chart['min_shoulder_cm']} - {$chart['max_shoulder_cm']} cm",
            'chest_range' => "{$chart['min_chest_cm']} - {$chart['max_chest_cm']} cm"
        ];

        if ($totalScore > $highestConfidence) {
            $highestConfidence = $totalScore;
            $bestSize = $sizeName;
        }
    }

    // Build the AI Estimated Fit Profile
    $shoulderFrame = 'Medium Frame (42 - 45 cm)';
    if ($shoulderCm < 41.5) {
        $shoulderFrame = 'Slim / Lean Frame (< 41.5 cm)';
    } elseif ($shoulderCm > 46.5) {
        $shoulderFrame = 'Broad / Athletic Frame (> 46.5 cm)';
    }

    $upperBodyBuild = 'Regular Athletic';
    if ($shoulderCm > 46 && $waistCm < 84) {
        $upperBodyBuild = 'Athletic V-Taper';
    } elseif ($shoulderCm < 41) {
        $upperBodyBuild = 'Slim / Ectomorph';
    } elseif ($waistCm > 92) {
        $upperBodyBuild = 'Broad / Comfort Fit';
    }

    // Category-specific size recommendations (T-Shirt vs Hoodie vs Shirt)
    $categorySizes = [
        'tshirt' => $bestSize,
        'shirt_polo' => $bestSize,
        'hoodie' => ($bestSize === 'S' ? 'M' : ($bestSize === 'M' ? 'L' : ($bestSize === 'L' ? 'XL' : $bestSize))),
    ];

    // Save scan to customer_scans table
    try {
        $ins = $db->prepare("INSERT INTO customer_scans (gender, measured_shoulder, measured_chest, measured_waist, measured_height, recommended_size, confidence_score) VALUES (:gender, :sh, :ch, :w, :h, :size, :conf)");
        $ins->execute([
            ':gender' => $gender,
            ':sh'     => round($shoulderCm, 1),
            ':ch'     => round($chestCm, 1),
            ':w'      => round($waistCm, 1),
            ':h'      => round($heightCm, 1),
            ':size'   => $bestSize,
            ':conf'   => $highestConfidence
        ]);
    } catch (Exception $e) {
        // Silently continue
    }

    // 💰 Query Matching Products Sorted by Price (Lowest Price Match First!)
    $sql = "SELECT c.*, cat.name as category_name, cat.slug as category_slug, cs.stock_qty
            FROM clothing_items c
            JOIN categories cat ON c.category_id = cat.id
            JOIN clothing_sizes cs ON c.id = cs.item_id
            WHERE (c.gender = :gender OR c.gender = 'unisex')
              AND cs.size_name = :size
              AND cs.stock_qty > 0";

    $params = [
        ':gender' => $gender,
        ':size'   => $bestSize
    ];

    if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
        $sql .= " AND cat.slug = :catslug";
        $params[':catslug'] = $categoryFilter;
    }

    // CRITICAL: Sort by lowest price first!
    $sql .= " ORDER BY c.price ASC LIMIT 9";

    $prodStmt = $db->prepare($sql);
    $prodStmt->execute($params);
    $matchedProducts = $prodStmt->fetchAll();

    // Enrich each matched product with rank badges and fit scores
    $lowestPrice = null;
    $rankedProducts = [];
    foreach ($matchedProducts as $idx => $p) {
        $pPrice = (float)$p['price'];
        if ($lowestPrice === null) {
            $lowestPrice = $pPrice;
        }

        $rank = $idx + 1;
        $rankBadge = '🥉 Choice Match';
        $badgeClass = 'bg-secondary';

        if ($rank === 1) {
            $rankBadge = '🥇 Lowest Price Match';
            $badgeClass = 'bg-warning text-dark fw-bold';
        } elseif ($rank === 2) {
            $rankBadge = '🥈 Value Pick';
            $badgeClass = 'bg-info text-dark fw-bold';
        } elseif ($rank === 3) {
            $rankBadge = '🥉 Classic Pick';
            $badgeClass = 'bg-light text-dark fw-bold';
        }

        // Slightly vary fit score around highestConfidence for realistic feel
        $itemFitScore = max(82, round($highestConfidence - ($idx * 1.5)));

        $p['rank'] = $rank;
        $p['rank_badge'] = $rankBadge;
        $p['badge_class'] = $badgeClass;
        $p['fit_score'] = $itemFitScore;
        $p['price_diff'] = $pPrice - $lowestPrice;
        $p['price_formatted'] = 'Rs. ' . number_format($pPrice, 0);

        $rankedProducts[] = $p;
    }

    echo json_encode([
        'success' => true,
        'recommended_size' => $bestSize,
        'confidence_score' => $highestConfidence,
        'fit_profile' => [
            'height_display' => '~' . round($heightCm) . ' cm',
            'shoulder_frame' => $shoulderFrame,
            'upper_body_build' => $upperBodyBuild,
            'waist_profile' => '~' . round($waistCm) . ' cm (' . (round($waistCm / 2.54)) . ' in)',
            'category_recommendations' => $categorySizes
        ],
        'measurements' => [
            'shoulder_cm' => round($shoulderCm, 1),
            'chest_cm'    => round($chestCm, 1),
            'waist_cm'    => round($waistCm, 1),
            'height_cm'   => round($heightCm, 1),
            'gender'      => $gender,
            'fit_preference' => $fitPreference
        ],
        'size_breakdown' => $sizeScores,
        'cheapest_match' => !empty($rankedProducts) ? $rankedProducts[0] : null,
        'recommended_products' => $rankedProducts
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}