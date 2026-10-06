/**
 * SmartFit AI - Storefront Application Controller
 */

const SmartFitApp = {
    currentGender: 'all',
    currentCategory: 'all',
    currentSize: '',
    searchQuery: '',
    clothesList: [],

    init: function() {
        this.fetchClothes();
        this.bindEvents();
    },

    bindEvents: function() {
        // Gender Buttons
        document.querySelectorAll('.gender-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.gender-filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.currentGender = btn.dataset.gender;
                this.fetchClothes();
            });
        });

        // Category Filter Buttons
        document.querySelectorAll('.category-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.category-filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.currentCategory = btn.dataset.category;
                this.fetchClothes();
            });
        });

        // Size Filter Pills
        document.querySelectorAll('.size-filter-pill').forEach(pill => {
            pill.addEventListener('click', (e) => {
                const isSelected = pill.classList.contains('active');
                document.querySelectorAll('.size-filter-pill').forEach(p => p.classList.remove('active'));
                
                if (isSelected) {
                    this.currentSize = '';
                } else {
                    pill.classList.add('active');
                    this.currentSize = pill.dataset.size;
                }
                this.fetchClothes();
            });
        });

        // Search Input
        const searchInput = document.getElementById('storeSearchInput');
        if (searchInput) {
            let timeout = null;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.searchQuery = e.target.value.trim();
                    this.fetchClothes();
                }, 300);
            });
        }
    },

    fetchClothes: async function() {
        const grid = document.getElementById('clothesGrid');
        if (!grid) return;

        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-neon" role="status"></div>
                <div class="text-muted mt-2 small">Loading SmartFit Collection...</div>
            </div>
        `;

        try {
            let url = `api/get_clothes.php?gender=${this.currentGender}&size=${this.currentSize}&category=${this.currentCategory}&search=${encodeURIComponent(this.searchQuery)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.success) {
                this.clothesList = data.data;
                this.renderProducts(data.data);
                if (window.SmartFitTryOn && typeof SmartFitTryOn.renderCatalogThumbnails === 'function') {
                    SmartFitTryOn.renderCatalogThumbnails();
                }
            } else {
                grid.innerHTML = `<div class="col-12 text-center text-danger py-5">Error: ${data.error}</div>`;
            }
        } catch (err) {
            console.error('Fetch clothes error:', err);
            grid.innerHTML = `<div class="col-12 text-center text-danger py-5">Unable to connect to server.</div>`;
        }
    },

    renderProducts: function(products) {
        const grid = document.getElementById('clothesGrid');
        const countBadge = document.getElementById('productCountBadge');
        if (countBadge) countBadge.innerText = `${products.length} Products`;

        if (products.length === 0) {
            grid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="text-secondary display-6 mb-3"><i class="fa-solid fa-shirt"></i></div>
                    <h5 class="text-light">No matching clothing items found</h5>
                    <p class="text-muted small">Try adjusting your gender, category, or size filters.</p>
                    <button class="btn btn-outline-cyber btn-sm" onclick="SmartFitApp.resetFilters()">Reset All Filters</button>
                </div>
            `;
            return;
        }

        let html = '';
        products.forEach(p => {
            // Render size tags
            let sizeHtml = '';
            if (p.sizes) {
                for (const [size, stock] of Object.entries(p.sizes)) {
                    const isMatched = this.currentSize && size === this.currentSize;
                    const inStock = stock > 0;
                    const badgeClass = isMatched ? 'recommended-match' : (inStock ? 'in-stock' : 'out-of-stock');
                    sizeHtml += `<span class="size-pill-tag ${badgeClass}" title="${stock} in stock">${size}</span> `;
                }
            }

            const isDiscounted = p.compare_price && parseFloat(p.compare_price) > parseFloat(p.price);

            html += `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 animate__animated animate__fadeIn">
                    <div class="product-card">
                        <div class="product-img-wrap">
                            <img src="${p.image_url}" class="product-img" alt="${p.name}" loading="lazy">
                            <span class="product-badge-gender">${p.gender}</span>
                            ${this.currentSize && p.sizes && p.sizes[this.currentSize] > 0 ? `<span class="product-badge-fit"><i class="fa-solid fa-check me-1"></i>Fit ${this.currentSize}</span>` : ''}
                        </div>
                        <div class="product-body">
                            <div class="product-brand">${p.brand}</div>
                            <h3 class="product-title text-truncate" title="${p.name}">${p.name}</h3>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <span class="product-price">Rs. ${parseFloat(p.price).toLocaleString()}</span>
                                ${isDiscounted ? `<span class="product-compare-price">Rs. ${parseFloat(p.compare_price).toLocaleString()}</span>` : ''}
                            </div>
                            <div class="mb-3">
                                <div class="text-muted" style="font-size: 0.72rem; margin-bottom: 4px;">AVAILABLE SIZES:</div>
                                <div class="d-flex flex-wrap gap-1">${sizeHtml}</div>
                            </div>
                            <div class="mt-auto d-flex gap-2">
                                <button class="btn btn-outline-cyber btn-sm flex-fill d-flex align-items-center justify-content-center gap-1" onclick="SmartFitTryOn.tryOnItem(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                                    <i class="fa-solid fa-shirt text-neon"></i>
                                    <span>Try On</span>
                                </button>
                                <button class="btn btn-scan-glow btn-sm px-3" onclick="SmartFitApp.showQuickView(${JSON.stringify(p).replace(/"/g, '&quot;')})" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
    },

    filterBySize: function(size) {
        this.currentSize = size;
        document.querySelectorAll('.size-filter-pill').forEach(pill => {
            if (pill.dataset.size === size) {
                pill.classList.add('active');
            } else {
                pill.classList.remove('active');
            }
        });
        this.fetchClothes();
    },

    resetFilters: function() {
        this.currentGender = 'all';
        this.currentCategory = 'all';
        this.currentSize = '';
        this.searchQuery = '';

        document.querySelectorAll('.gender-filter-btn').forEach(b => {
            if (b.dataset.gender === 'all') b.classList.add('active');
            else b.classList.remove('active');
        });

        document.querySelectorAll('.category-filter-btn').forEach(b => {
            if (b.dataset.category === 'all') b.classList.add('active');
            else b.classList.remove('active');
        });

        document.querySelectorAll('.size-filter-pill').forEach(p => p.classList.remove('active'));
        
        const searchInput = document.getElementById('storeSearchInput');
        if (searchInput) searchInput.value = '';

        this.fetchClothes();
    },

    showQuickView: function(product) {
        const qvModalEl = document.getElementById('quickViewModal');
        if (!qvModalEl) return;

        document.getElementById('qvItemImg').src = product.image_url;
        document.getElementById('qvItemTitle').innerText = product.name;
        document.getElementById('qvItemBrand').innerText = `${product.brand} • ${product.item_code}`;
        document.getElementById('qvItemPrice').innerText = 'Rs. ' + parseFloat(product.price).toLocaleString();
        document.getElementById('qvItemColor').innerText = product.color;
        document.getElementById('qvItemFabric').innerText = product.fabric_details || '87% Nylon, 13% Spandex Stretch';
        document.getElementById('qvItemDesc').innerText = product.description || 'Engineered athletic performance wear.';

        // Render sizes in quickview
        let sizeHtml = '';
        if (product.sizes) {
            for (const [s, qty] of Object.entries(product.sizes)) {
                sizeHtml += `
                    <div class="border rounded p-2 text-center ${qty > 0 ? 'border-secondary text-white' : 'border-dark text-muted opacity-50'}">
                        <div class="fw-bold">${s}</div>
                        <div style="font-size: 0.65rem;">${qty > 0 ? qty + ' in stock' : 'Out'}</div>
                    </div>
                `;
            }
        }
        document.getElementById('qvSizesGrid').innerHTML = sizeHtml;

        // WhatsApp direct order URL
        const waBtn = document.getElementById('qvWhatsAppBtn');
        if (waBtn) {
            const detectedSize = this.currentSize || 'M';
            const msg = encodeURIComponent(`Hi SmartFit AI, I want to order the "${product.name}" (Code: ${product.item_code}) in Size ${detectedSize} for Rs. ${parseFloat(product.price).toLocaleString()}!`);
            waBtn.href = `https://wa.me/94771234567?text=${msg}`;
        }

        const tryOnBtn = document.getElementById('qvTryOnBtn');
        if (tryOnBtn) {
            tryOnBtn.onclick = () => {
                const qvModal = bootstrap.Modal.getInstance(qvModalEl);
                if (qvModal) qvModal.hide();
                SmartFitTryOn.tryOnItem(product);
            };
        }

        const modal = new bootstrap.Modal(qvModalEl);
        modal.show();
    }
};

// Global helper triggers
function openScannerModal() {
    const modalEl = document.getElementById('scannerModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        setTimeout(() => {
            SmartFitScanner.startCamera();
        }, 300);
    }
}

function openTryonModal() {
    const modalEl = document.getElementById('tryonModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        setTimeout(() => {
            SmartFitTryOn.init();
        }, 200);
    }
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
    SmartFitApp.init();

    // Attach modal close event to stop camera
    const scannerModalEl = document.getElementById('scannerModal');
    if (scannerModalEl) {
        scannerModalEl.addEventListener('hidden.bs.modal', () => {
            SmartFitScanner.stopCamera();
        });
    }
});