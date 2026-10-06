<?php
require_once __DIR__ . '/../includes/auth.php';
checkAdminAuth();
require_once __DIR__ . '/../api/db.php';

$db = Database::getConnection();

// Fetch all categories
$categories = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

// Fetch all clothing items with category and sizes
$items = $db->query("SELECT c.*, cat.name as category_name 
                     FROM clothing_items c 
                     JOIN categories cat ON c.category_id = cat.id 
                     ORDER BY c.id DESC")->fetchAll();

// Attach sizes to each item
foreach ($items as &$item) {
    $sizeStmt = $db->prepare("SELECT size_name, stock_qty FROM clothing_sizes WHERE item_id = :item_id ORDER BY id ASC");
    $sizeStmt->execute([':item_id' => $item['id']]);
    $item['sizes'] = $sizeStmt->fetchAll();
}
unset($item);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clothing Management — SmartFit AI Admin</title>
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
                <div class="brand-badge"><i class="fa-solid fa-shirt text-neon"></i></div>
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
                        <a class="nav-link active" href="clothes.php"><i class="fa-solid fa-shirt me-1"></i> Clothing Inventory</a>
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
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1">Clothing Inventory Catalog</h3>
                <p class="text-secondary small mb-0">Manage apparel styles, photography, colorways, and multi-size stock counts.</p>
            </div>
            <button class="btn btn-scan-glow btn-sm px-3 py-2 d-flex align-items-center gap-2" onclick="openAddModal()">
                <i class="fa-solid fa-plus"></i> Add New Garment
            </button>
        </div>

        <!-- Clothing Table Card -->
        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle custom-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Image</th>
                            <th>Item Code</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Gender</th>
                            <th>Color</th>
                            <th>Price (LKR)</th>
                            <th>Stock by Size (S / M / L / XL / XXL)</th>
                            <th style="width: 120px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-5">No clothing items in database. Click 'Add New Garment' to create one!</td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" class="rounded border border-secondary" style="width: 48px; height: 60px; object-fit: cover;" alt="">
                                    </td>
                                    <td class="fw-bold text-neon"><?= htmlspecialchars($item['item_code']) ?></td>
                                    <td>
                                        <div class="fw-bold text-white"><?= htmlspecialchars($item['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($item['brand']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark border border-secondary"><?= htmlspecialchars($item['category_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark border border-secondary text-uppercase"><?= htmlspecialchars($item['gender']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-block rounded-circle" style="width: 14px; height: 14px; background: <?= htmlspecialchars($item['color_hex']) ?>; border: 1px solid #666;"></span>
                                            <small><?= htmlspecialchars($item['color']) ?></small>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-white">Rs. <?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($item['sizes'] as $s): ?>
                                                <span class="badge <?= $s['stock_qty'] > 0 ? 'bg-secondary' : 'bg-danger opacity-50' ?>" title="<?= $s['stock_qty'] ?> units in stock">
                                                    <?= htmlspecialchars($s['size_name']) ?>: <?= $s['stock_qty'] ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-cyber me-1 p-1 px-2" onclick="openEditModal(<?= htmlspecialchars(json_encode($item)) ?>)" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger p-1 px-2" onclick="deleteClothing(<?= $item['id'] ?>)" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal: Add / Edit Garment -->
    <div class="modal fade" id="clothingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-card border-secondary text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold" id="modalTitle">Add New Clothing Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form id="clothingForm" onsubmit="saveClothing(event)">
                    <input type="hidden" name="id" id="clothId" value="0">

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-secondary">Item Code (SKU)</label>
                                <input type="text" name="item_code" id="clothCode" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="e.g. CRN-0310-BLK" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small text-secondary">Garment Name</label>
                                <input type="text" name="name" id="clothName" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="e.g. Essential Seamless 1/4 Zip Polo" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small text-secondary">Category</label>
                                <select name="category_id" id="clothCat" class="form-select bg-dark text-light border-secondary shadow-none">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small text-secondary">Gender Target</label>
                                <select name="gender" id="clothGender" class="form-select bg-dark text-light border-secondary shadow-none">
                                    <option value="mens">Men's</option>
                                    <option value="womens">Women's</option>
                                    <option value="unisex">Unisex</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small text-secondary">Brand</label>
                                <input type="text" name="brand" id="clothBrand" class="form-control bg-dark text-light border-secondary shadow-none" value="Carnage">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small text-secondary">Color Name</label>
                                <input type="text" name="color" id="clothColor" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="e.g. Jet Black" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small text-secondary">Color Hex</label>
                                <input type="color" name="color_hex" id="clothColorHex" class="form-control form-control-color bg-dark border-secondary w-100" value="#111111">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small text-secondary">Price (LKR)</label>
                                <input type="number" step="0.01" name="price" id="clothPrice" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="5500.00" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small text-secondary">Compare Price (LKR)</label>
                                <input type="number" step="0.01" name="compare_price" id="clothComparePrice" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="6200.00">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small text-secondary">Product Photo URL (Web / CDN)</label>
                                <input type="url" name="image_url" id="clothImgUrl" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="https://cdn.shopify.com/s/files/.../IMG_8608.jpg">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small text-secondary">Virtual Try-On Transparent Overlay (SVG / PNG)</label>
                                <input type="text" name="overlay_image_url" id="clothOverlayUrl" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="assets/images/products/hoodie_black.svg">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small text-secondary">Or Upload Local Image File</label>
                                <input type="file" name="image_file" id="clothImgFile" class="form-control bg-dark text-light border-secondary shadow-none" accept="image/*">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small text-secondary">Fabric & Material Composition</label>
                                <input type="text" name="fabric_details" id="clothFabric" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="e.g. 54% Nylon, 46% Polyester 4-Way Stretch">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small text-secondary">Description</label>
                                <textarea name="description" id="clothDesc" rows="2" class="form-control bg-dark text-light border-secondary shadow-none" placeholder="Detailed product fit description..."></textarea>
                            </div>

                            <!-- Multi-Size Stock Quantities -->
                            <div class="col-md-12">
                                <label class="form-label small text-secondary fw-bold">Stock Quantities by Size:</label>
                                <div class="row g-2">
                                    <div class="col-2">
                                        <label class="small text-muted">XS</label>
                                        <input type="number" name="sizes[XS]" id="stock_XS" class="form-control bg-dark text-light border-secondary form-control-sm text-center" value="0">
                                    </div>
                                    <div class="col-2">
                                        <label class="small text-muted">S</label>
                                        <input type="number" name="sizes[S]" id="stock_S" class="form-control bg-dark text-light border-secondary form-control-sm text-center" value="10">
                                    </div>
                                    <div class="col-2">
                                        <label class="small text-muted">M</label>
                                        <input type="number" name="sizes[M]" id="stock_M" class="form-control bg-dark text-light border-secondary form-control-sm text-center" value="15">
                                    </div>
                                    <div class="col-2">
                                        <label class="small text-muted">L</label>
                                        <input type="number" name="sizes[L]" id="stock_L" class="form-control bg-dark text-light border-secondary form-control-sm text-center" value="12">
                                    </div>
                                    <div class="col-2">
                                        <label class="small text-muted">XL</label>
                                        <input type="number" name="sizes[XL]" id="stock_XL" class="form-control bg-dark text-light border-secondary form-control-sm text-center" value="6">
                                    </div>
                                    <div class="col-2">
                                        <label class="small text-muted">XXL</label>
                                        <input type="number" name="sizes[XXL]" id="stock_XXL" class="form-control bg-dark text-light border-secondary form-control-sm text-center" value="2">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-scan-glow btn-sm px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Garment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let clothingModal = null;
        document.addEventListener('DOMContentLoaded', () => {
            clothingModal = new bootstrap.Modal(document.getElementById('clothingModal'));
        });

        function openAddModal() {
            document.getElementById('clothingForm').reset();
            document.getElementById('clothId').value = "0";
            document.getElementById('clothOverlayUrl').value = "";
            document.getElementById('modalTitle').innerText = "Add New Clothing Item";
            clothingModal.show();
        }

        function openEditModal(item) {
            document.getElementById('clothingForm').reset();
            document.getElementById('clothId').value = item.id;
            document.getElementById('clothCode').value = item.item_code;
            document.getElementById('clothName').value = item.name;
            document.getElementById('clothCat').value = item.category_id;
            document.getElementById('clothGender').value = item.gender;
            document.getElementById('clothBrand').value = item.brand;
            document.getElementById('clothColor').value = item.color;
            document.getElementById('clothColorHex').value = item.color_hex || '#111111';
            document.getElementById('clothPrice').value = item.price;
            document.getElementById('clothComparePrice').value = item.compare_price || '';
            document.getElementById('clothImgUrl').value = item.image_url;
            document.getElementById('clothOverlayUrl').value = item.overlay_image_url || '';
            document.getElementById('clothFabric').value = item.fabric_details || '';
            document.getElementById('clothDesc').value = item.description || '';

            // Reset and fill sizes
            ['XS', 'S', 'M', 'L', 'XL', 'XXL'].forEach(s => {
                const el = document.getElementById('stock_' + s);
                if (el) el.value = 0;
            });

            if (item.sizes) {
                item.sizes.forEach(s => {
                    const el = document.getElementById('stock_' + s.size_name);
                    if (el) el.value = s.stock_qty;
                });
            }

            document.getElementById('modalTitle').innerText = "Edit Clothing Item #" + item.id;
            clothingModal.show();
        }

        async function saveClothing(e) {
            e.preventDefault();
            const form = document.getElementById('clothingForm');
            const formData = new FormData(form);

            try {
                const res = await fetch('../api/save_clothing.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    alert('Garment saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (err) {
                alert('Request failed: ' + err.message);
            }
        }

        async function deleteClothing(id) {
            if (!confirm('Are you sure you want to delete this clothing item from the catalog?')) return;

            const fd = new FormData();
            fd.append('id', id);

            try {
                const res = await fetch('../api/delete_clothing.php', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Delete failed: ' + data.error);
                }
            } catch (err) {
                alert('Request failed: ' + err.message);
            }
        }
    </script>
</body>
</html>