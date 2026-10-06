<?php
// SmartFit AI - Database Connection & Helper Wrapper
if (php_sapi_name() !== 'cli' && !headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $mysqlHost = '127.0.0.1';
        $mysqlUser = 'root';
        $mysqlPass = '';
        $mysqlDb   = 'aicloth_db';
        $mysqlPort = '3306';

        // 1. Try MySQL Connection
        try {
            $rootPdo = new PDO("mysql:host=$mysqlHost;port=$mysqlPort;charset=utf8mb4", $mysqlUser, $mysqlPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2
            ]);

            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `$mysqlDb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            self::$pdo = new PDO("mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb;charset=utf8mb4", $mysqlUser, $mysqlPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $checkTable = self::$pdo->query("SHOW TABLES LIKE 'clothing_items'")->fetch();
            if (!$checkTable) {
                self::runSqlFile(__DIR__ . '/../database/schema.sql');
            }

            return self::$pdo;
        } catch (Exception $e) {
            // 2. Seamless SQLite Fallback
            try {
                $sqlitePath = __DIR__ . '/../database/aicloth.sqlite';
                $isNew = !file_exists($sqlitePath);
                self::$pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

                if ($isNew || filesize($sqlitePath) === 0) {
                    self::initSqliteTables();
                }

                return self::$pdo;
            } catch (Exception $ex) {
                if (php_sapi_name() !== 'cli') {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Database connection failed: ' . $ex->getMessage()
                    ]);
                    exit;
                }
                throw $ex;
            }
        }
    }

    private static function runSqlFile($filePath) {
        if (!file_exists($filePath)) return;
        $sql = file_get_contents($filePath);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                try {
                    self::$pdo->exec($stmt);
                } catch (Exception $e) {
                    // Ignore drop errors
                }
            }
        }
    }

    private static function initSqliteTables() {
        $db = self::$pdo;
        $db->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                gender TEXT DEFAULT 'unisex',
                icon TEXT DEFAULT 'fa-tshirt'
            );

            CREATE TABLE IF NOT EXISTS clothing_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER NOT NULL,
                item_code TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                brand TEXT DEFAULT 'Carnage',
                gender TEXT DEFAULT 'mens',
                color TEXT NOT NULL,
                color_hex TEXT DEFAULT '#000000',
                price REAL NOT NULL,
                compare_price REAL DEFAULT NULL,
                image_url TEXT NOT NULL,
                overlay_image_url TEXT DEFAULT NULL,
                description TEXT,
                fabric_details TEXT DEFAULT '87% Nylon, 13% Spandex 4-Way Stretch',
                is_featured INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS clothing_sizes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item_id INTEGER NOT NULL,
                size_name TEXT NOT NULL,
                stock_qty INTEGER DEFAULT 10
            );

            CREATE TABLE IF NOT EXISTS size_charts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                gender TEXT NOT NULL,
                category_type TEXT DEFAULT 'tops',
                size_name TEXT NOT NULL,
                min_shoulder_cm REAL NOT NULL,
                max_shoulder_cm REAL NOT NULL,
                min_chest_cm REAL NOT NULL,
                max_chest_cm REAL NOT NULL,
                min_waist_cm REAL NOT NULL,
                max_waist_cm REAL NOT NULL,
                min_height_cm REAL NOT NULL,
                max_height_cm REAL NOT NULL,
                fit_description TEXT DEFAULT 'Standard Fit'
            );

            CREATE TABLE IF NOT EXISTS customer_scans (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                gender TEXT DEFAULT 'mens',
                measured_shoulder REAL NOT NULL,
                measured_chest REAL NOT NULL,
                measured_waist REAL NOT NULL,
                measured_height REAL NOT NULL,
                recommended_size TEXT NOT NULL,
                confidence_score REAL NOT NULL,
                scanned_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $db->exec("INSERT OR IGNORE INTO admins (id, username, password, name) VALUES (1, 'admin', '$adminPass', 'SmartFit Administrator')");

        $db->exec("
            INSERT OR IGNORE INTO categories (id, name, slug, gender, icon) VALUES
            (1, 'Men''s T-Shirts & Tops', 'mens-t-shirts', 'mens', 'fa-tshirt'),
            (2, 'Men''s Polos & Seamless', 'mens-polos', 'mens', 'fa-vest'),
            (3, 'Men''s Shorts & Bottoms', 'mens-shorts', 'mens', 'fa-running'),
            (4, 'Women''s Leggings & Tights', 'womens-leggings', 'womens', 'fa-female'),
            (5, 'Women''s Athleisure & Joggers', 'womens-joggers', 'womens', 'fa-socks'),
            (6, 'Performance Outerwear', 'performance-outerwear', 'unisex', 'fa-hoodie');
        ");

        $db->exec("
            INSERT OR IGNORE INTO clothing_items (id, category_id, item_code, name, brand, gender, color, color_hex, price, compare_price, image_url, overlay_image_url, description, fabric_details, is_featured) VALUES
            (1, 2, 'CRN-0310-BLK', 'Essential Seamless 1/4 Zip Up Polo', 'Carnage', 'mens', 'Jet Black', '#111111', 5500.00, 6200.00, 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/IMG_8608_8b80d0f0-277e-48e6-a2fd-75a1a40abe23.jpg?v=1785210500', 'assets/images/products/polo_black.svg', 'Tailored, tapered athletic fit designed to accentuate physique. Sleek 1/4 zip closure with minimalist Carnage high-build logo.', '54% Nylon, 46% Polyester Stretch', 1),
            (2, 2, 'CRN-0310-GRY', 'Essential Seamless 1/4 Zip Polo', 'Carnage', 'mens', 'Grey Heather', '#55595e', 5500.00, 6200.00, 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/IMG_8641_9897e0a0-58c6-4033-bca0-80633d15fc5f.jpg?v=1785210459', 'assets/images/products/polo_grey.svg', 'Premium lightweight seamless construction engineered for breathability and aesthetic tapered contouring.', '54% Nylon, 46% Polyester Stretch', 1),
            (3, 2, 'CRN-0310-WHT', 'Essential Seamless 1/4 Zip Polo', 'Carnage', 'mens', 'Sheer White', '#f4f4f6', 5500.00, 6200.00, 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/IMG_8636_67963610-4cad-4ee1-8e86-b8028212a3e7.jpg?v=1785210532', 'assets/images/products/polo_white.svg', 'Ultra-clean white athletic polo with high elasticity, quick-drying moisture control and soft hand-feel.', '54% Nylon, 46% Polyester Stretch', 1),
            (4, 3, 'CRN-0417-GRY', 'Essential 5-Inch Performance Short', 'Carnage', 'mens', 'Ultimate Grey', '#4a4e54', 3350.00, 3950.00, 'https://cdn.shopify.com/s/files/1/0607/0619/3614/products/DUL00008.jpg?v=1651615987', 'assets/images/products/polo_grey.svg', 'Tailored 5-inch inseam short for gym and daily training. Features deep zippered pockets and elastic jacquard waistband.', '96% Polyester, 4% Spandex 190 GSM', 1),
            (5, 4, 'CRN-0847-LEG', 'Revline 7/8 High-Waist Performance Legging', 'Carnage', 'womens', 'Jet Black', '#151515', 6500.00, 7500.00, 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/IMG_8645.jpg?v=1765544102', 'assets/images/products/crop_top.svg', 'Ultra-smooth butter-soft fabric with 4-way compression stretch, squat-proof opacity and streamlined side athletic stripes.', '75% Nylon, 25% Spandex ButterSoft', 1),
            (6, 5, 'CRN-0054-JOG', 'Athleisure Tapered Jogger', 'Carnage', 'womens', 'Midnight Black', '#18181b', 7950.00, 8900.00, 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/DSC08922.jpg?v=1702370627', 'assets/images/products/hoodie_black.svg', 'Relaxed regular fit with tapered leg and snug cuffed ankles. Adjustable drawcord waist for maximum lifestyle comfort.', '87% Nylon, 13% Spandex 230 GSM', 1),
            (7, 3, 'CRN-0137-CMP', 'Performance Compression Active Short', 'Carnage', 'mens', 'Jet Black', '#09090b', 3550.00, 4200.00, 'https://cdn.shopify.com/s/files/1/0607/0619/3614/products/DUL00008.jpg?v=1651615987', 'assets/images/products/polo_black.svg', 'High breathability active base layer compression short designed for intense endurance and weight training.', '88% Polyester, 12% Spandex QuickDry', 1),
            (8, 1, 'CRN-0992-OVR', 'Carnage Signature Oversized Tee', 'Carnage', 'mens', 'Charcoal Smoke', '#27272a', 4200.00, 4800.00, 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/DSC08950_839886da-634a-498a-844b-44850e5442fb.jpg?v=1706784942', 'assets/images/products/oversized_tee.svg', 'Heavyweight 240 GSM drop-shoulder boxy oversized streetwear t-shirt with signature high-density silicone print.', '100% Combed Cotton 240 GSM', 1);
        ");

        $db->exec("
            INSERT OR IGNORE INTO clothing_sizes (id, item_id, size_name, stock_qty) VALUES
            (1, 1, 'S', 12), (2, 1, 'M', 25), (3, 1, 'L', 18), (4, 1, 'XL', 8), (5, 1, 'XXL', 4),
            (6, 2, 'S', 15), (7, 2, 'M', 20), (8, 2, 'L', 14), (9, 2, 'XL', 6), (10, 2, 'XXL', 3),
            (11, 3, 'S', 10), (12, 3, 'M', 18), (13, 3, 'L', 15), (14, 3, 'XL', 5), (15, 3, 'XXL', 2),
            (16, 4, 'S', 14), (17, 4, 'M', 22), (18, 4, 'L', 19), (19, 4, 'XL', 10),
            (20, 5, 'XS', 8), (21, 5, 'S', 24), (22, 5, 'M', 30), (23, 5, 'L', 16), (24, 5, 'XL', 7),
            (25, 6, 'XS', 5), (26, 6, 'S', 18), (27, 6, 'M', 22), (28, 6, 'L', 12), (29, 6, 'XL', 6),
            (30, 7, 'S', 16), (31, 7, 'M', 28), (32, 7, 'L', 20), (33, 7, 'XL', 11), (34, 7, 'XXL', 5),
            (35, 8, 'S', 20), (36, 8, 'M', 35), (37, 8, 'L', 30), (38, 8, 'XL', 15), (39, 8, 'XXL', 8);
        ");

        $db->exec("
            INSERT OR IGNORE INTO size_charts (id, gender, category_type, size_name, min_shoulder_cm, max_shoulder_cm, min_chest_cm, max_chest_cm, min_waist_cm, max_waist_cm, min_height_cm, max_height_cm, fit_description) VALUES
            (1, 'mens', 'tops', 'S', 39.0, 42.5, 88.0, 95.0, 72.0, 78.0, 160.0, 172.0, 'Men Small (Chest: 35-37\", Shoulder: 40-42cm)'),
            (2, 'mens', 'tops', 'M', 42.6, 45.5, 95.1, 102.0, 78.1, 84.0, 168.0, 178.0, 'Men Medium (Chest: 38-40\", Shoulder: 43-45cm)'),
            (3, 'mens', 'tops', 'L', 45.6, 48.5, 102.1, 109.0, 84.1, 91.0, 174.0, 184.0, 'Men Large (Chest: 40-43\", Shoulder: 46-48cm)'),
            (4, 'mens', 'tops', 'XL', 48.6, 52.0, 109.1, 117.0, 91.1, 99.0, 178.0, 190.0, 'Men Extra Large (Chest: 43-46\", Shoulder: 49-51cm)'),
            (5, 'mens', 'tops', 'XXL', 52.1, 56.0, 117.1, 126.0, 99.1, 108.0, 180.0, 196.0, 'Men Double XL (Chest: 46-49\", Shoulder: 52-55cm)'),
            (6, 'womens', 'tops', 'XS', 34.0, 37.0, 76.0, 82.0, 58.0, 64.0, 148.0, 158.0, 'Women XS (Bust: 30-32\", Shoulder: 35-37cm)'),
            (7, 'womens', 'tops', 'S', 37.1, 39.5, 82.1, 88.0, 64.1, 70.0, 154.0, 165.0, 'Women Small (Bust: 32-34\", Shoulder: 37-39cm)'),
            (8, 'womens', 'tops', 'M', 39.6, 42.0, 88.1, 95.0, 70.1, 77.0, 160.0, 172.0, 'Women Medium (Bust: 35-37\", Shoulder: 40-42cm)'),
            (9, 'womens', 'tops', 'L', 42.1, 45.0, 95.1, 103.0, 77.1, 85.0, 165.0, 178.0, 'Women Large (Bust: 37-40\", Shoulder: 42-44cm)'),
            (10, 'womens', 'tops', 'XL', 45.1, 48.0, 103.1, 112.0, 85.1, 94.0, 168.0, 182.0, 'Women XL (Bust: 40-44\", Shoulder: 45-47cm)');
        ");
    }
}