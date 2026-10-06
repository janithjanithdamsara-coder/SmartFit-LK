    <!-- Global Size Guide Modal -->
    <div class="modal fade" id="sizeChartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-card border-secondary text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i class="fa-solid fa-ruler-combined text-neon"></i>
                        <span>Official Size Chart & Measurements (CM)</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Gender Tabs -->
                    <ul class="nav nav-pills custom-pills mb-3 justify-content-center" id="sizeChartTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="men-chart-tab" data-bs-toggle="pill" data-bs-target="#menChart">
                                <i class="fa-solid fa-mars me-1"></i> Men's Apparel Guide
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="women-chart-tab" data-bs-toggle="pill" data-bs-target="#womenChart">
                                <i class="fa-solid fa-venus me-1"></i> Women's Apparel Guide
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="sizeChartTabsContent">
                        <!-- Men's Chart -->
                        <div class="tab-pane fade show active" id="menChart">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle custom-table">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Shoulder Width (cm)</th>
                                            <th>Chest Circumference (cm)</th>
                                            <th>Waist (cm)</th>
                                            <th>Est. Height (cm)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><span class="badge bg-secondary">S</span></td><td>39.0 - 42.5</td><td>88.0 - 95.0 (35-37")</td><td>72 - 78</td><td>160 - 172</td></tr>
                                        <tr class="table-active-row"><td><span class="badge bg-primary">M</span></td><td>42.6 - 45.5</td><td>95.1 - 102.0 (38-40")</td><td>78 - 84</td><td>168 - 178</td></tr>
                                        <tr><td><span class="badge bg-secondary">L</span></td><td>45.6 - 48.5</td><td>102.1 - 109.0 (40-43")</td><td>84 - 91</td><td>174 - 184</td></tr>
                                        <tr><td><span class="badge bg-secondary">XL</span></td><td>48.6 - 52.0</td><td>109.1 - 117.0 (43-46")</td><td>91 - 99</td><td>178 - 190</td></tr>
                                        <tr><td><span class="badge bg-secondary">XXL</span></td><td>52.1 - 56.0</td><td>117.1 - 126.0 (46-49")</td><td>99 - 108</td><td>180 - 196</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Women's Chart -->
                        <div class="tab-pane fade" id="womenChart">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle custom-table">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Shoulder Width (cm)</th>
                                            <th>Bust / Chest (cm)</th>
                                            <th>Waist (cm)</th>
                                            <th>Est. Height (cm)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><span class="badge bg-secondary">XS</span></td><td>34.0 - 37.0</td><td>76.0 - 82.0 (30-32")</td><td>58 - 64</td><td>148 - 158</td></tr>
                                        <tr><td><span class="badge bg-secondary">S</span></td><td>37.1 - 39.5</td><td>82.1 - 88.0 (32-34")</td><td>64 - 70</td><td>154 - 165</td></tr>
                                        <tr class="table-active-row"><td><span class="badge bg-primary">M</span></td><td>39.6 - 42.0</td><td>88.1 - 95.0 (35-37")</td><td>70 - 77</td><td>160 - 172</td></tr>
                                        <tr><td><span class="badge bg-secondary">L</span></td><td>42.1 - 45.0</td><td>95.1 - 103.0 (37-40")</td><td>77 - 85</td><td>165 - 178</td></tr>
                                        <tr><td><span class="badge bg-secondary">XL</span></td><td>45.1 - 48.0</td><td>103.1 - 112.0 (40-44")</td><td>85 - 94</td><td>168 - 182</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-cyber mt-3 p-2 text-center small">
                        <i class="fa-solid fa-circle-info me-1 text-neon"></i>
                        Our AI Camera Scanner automatically detects your body landmarks and matches you with these exact chart thresholds.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-smart text-light py-5 mt-5 border-top border-secondary">
        <div class="container text-center">
            <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                <span class="brand-title">SMART<span class="text-neon">FIT</span> <span class="badge bg-primary text-white ms-1" style="font-size:0.65rem;">LK</span></span>
                <span class="badge bg-neon-glow text-dark px-2 py-1 small">IntelliCon '26 Edition</span>
            </div>
            <p class="text-secondary small max-w-600 mx-auto">
                AI-assisted body fit profile, anthropometric pose diagnostics, lowest price clothing matching, and 2D virtual fitting preview.
            </p>
            <div class="d-flex justify-content-center gap-3 my-3">
                <a href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>index.php" class="text-muted text-decoration-none small hover-neon">Store</a>
                <a href="#scan" onclick="openScannerModal(); return false;" class="text-muted text-decoration-none small hover-neon">AI Scanner</a>
                <a href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>admin/index.php" class="text-muted text-decoration-none small hover-neon">Admin Console</a>
            </div>
            <div class="text-muted small border-top border-dark pt-3 mt-3">
                &copy; <?= date('Y') ?> SmartFit LK. Built for IntelliCon '26 (AIESEC in SLIIT).
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- App Scripts -->
    <script src="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/js/mediapipe_scanner.js?v=<?= time() ?>"></script>
    <script src="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/js/size_engine.js?v=<?= time() ?>"></script>
    <script src="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/js/tryon_canvas.js?v=<?= time() ?>"></script>
    <script src="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/js/customer_auth.js?v=<?= time() ?>"></script>
    <script src="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>