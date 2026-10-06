/**
 * SmartFit LK - Customer Authentication & Saved Fit Profile Engine
 * Aligned with IntelliCon '26 Startup Proposal (Mix-Trendz)
 */

const SmartFitAuth = {
    currentUser: null,
    pendingFitProfileToSave: null,

    init: async function() {
        await this.checkSession();
    },

    checkSession: async function() {
        try {
            const res = await fetch('api/auth_customer.php?action=get_profile');
            const data = await res.json();
            if (data.success && data.logged_in && data.customer) {
                this.currentUser = data.customer;
                this.renderNavbarUser(data.customer);

                // Auto-apply saved size if customer hasn't scanned yet
                if (data.customer.recommended_size && window.SmartFitApp) {
                    window.SmartFitApp.currentSize = data.customer.recommended_size;
                    const navSize = document.getElementById('navDetectedSize');
                    const navBadge = document.getElementById('navFitBadge');
                    if (navSize && navBadge) {
                        navSize.innerText = data.customer.recommended_size;
                        navBadge.classList.remove('d-none');
                    }
                }
            } else {
                this.currentUser = null;
                this.renderNavbarGuest();
            }
        } catch (e) {
            console.warn('Session check fallback:', e);
        }
    },

    renderNavbarUser: function(user) {
        const container = document.getElementById('navCustomerContainer');
        if (!container) return;

        const sizeLabel = user.recommended_size ? `(${user.recommended_size})` : '';

        container.innerHTML = `
            <div class="dropdown">
                <button class="btn btn-outline-cyber btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-circle-user text-neon fs-6"></i>
                    <span class="text-truncate" style="max-width: 130px;">${user.full_name.split(' ')[0]} ${sizeLabel}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li class="px-3 py-2 border-bottom border-secondary mb-1">
                        <div class="small fw-bold text-white">${user.full_name}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">${user.email}</div>
                        ${user.recommended_size ? `<div class="badge bg-primary mt-1" style="font-size: 0.68rem;">Saved Size: ${user.recommended_size}</div>` : ''}
                    </li>
                    <li><a class="dropdown-item small" href="#" onclick="SmartFitAuth.openProfileModal(); return false;"><i class="fa-solid fa-id-card me-2 text-info"></i> My Fit Profile</a></li>
                    <li><a class="dropdown-item small" href="#" onclick="SmartFitAuth.openWardrobeModal(); return false;"><i class="fa-solid fa-shirt me-2 text-warning"></i> My Wardrobe</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small text-danger" href="#" onclick="SmartFitAuth.logout(); return false;"><i class="fa-solid fa-right-from-bracket me-2"></i> Log Out</a></li>
                </ul>
            </div>
        `;
    },

    renderNavbarGuest: function() {
        const container = document.getElementById('navCustomerContainer');
        if (!container) return;

        container.innerHTML = `
            <button class="btn btn-outline-cyber btn-sm px-3 py-2 d-flex align-items-center gap-1" onclick="SmartFitAuth.openAuthModal()">
                <i class="fa-solid fa-user me-1 text-neon"></i>
                <span>Sign In</span>
            </button>
        `;
    },

    openAuthModal: function(pendingFit = null) {
        if (pendingFit) {
            this.pendingFitProfileToSave = pendingFit;
        }

        const modalEl = document.getElementById('authCustomerModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    },

    fillDemoCredentials: function() {
        const emailEl = document.getElementById('authLoginEmail');
        const passEl = document.getElementById('authLoginPassword');
        if (emailEl && passEl) {
            emailEl.value = 'demo@smartfit.lk';
            passEl.value = 'demo123';
            const loginTabBtn = document.querySelector('[data-bs-target="#authLoginTab"]');
            if (loginTabBtn) {
                const tab = bootstrap.Tab.getOrCreateInstance(loginTabBtn);
                tab.show();
            }
        }
    },

    handleLogin: async function(e) {
        if (e) e.preventDefault();
        const email = document.getElementById('authLoginEmail').value.trim();
        const password = document.getElementById('authLoginPassword').value;
        const alertEl = document.getElementById('authLoginAlert');

        if (!email || !password) {
            this.showAlert(alertEl, 'Please enter your email and password.', 'danger');
            return;
        }

        try {
            const res = await fetch('api/auth_customer.php?action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', email, password })
            });
            const data = await res.json();

            if (data.success && data.customer) {
                this.currentUser = data.customer;
                this.renderNavbarUser(data.customer);

                // Hide modal
                const modalEl = document.getElementById('authCustomerModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // If there was a pending fit profile from scanner, save it now!
                if (this.pendingFitProfileToSave) {
                    await this.saveFitProfile(this.pendingFitProfileToSave);
                    this.pendingFitProfileToSave = null;
                }

                // Smooth reload to sync session
                setTimeout(() => {
                    window.location.reload();
                }, 400);
            } else {
                this.showAlert(alertEl, data.error || 'Login failed. Please check credentials.', 'danger');
            }
        } catch (err) {
            this.showAlert(alertEl, 'Server connection error. Please try again.', 'danger');
        }
    },

    handleRegister: async function(e) {
        if (e) e.preventDefault();
        const fullName = document.getElementById('authRegName').value.trim();
        const email = document.getElementById('authRegEmail').value.trim();
        const password = document.getElementById('authRegPassword').value;
        const gender = document.getElementById('authRegGender').value;
        const alertEl = document.getElementById('authRegAlert');

        if (!fullName || !email || !password) {
            this.showAlert(alertEl, 'Please fill in all required fields.', 'danger');
            return;
        }

        try {
            const res = await fetch('api/auth_customer.php?action=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'register', full_name: fullName, email, password, gender })
            });
            const data = await res.json();

            if (data.success && data.customer) {
                this.currentUser = data.customer;
                this.renderNavbarUser(data.customer);

                const modalEl = document.getElementById('authCustomerModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // If pending fit profile, save immediately
                if (this.pendingFitProfileToSave) {
                    await this.saveFitProfile(this.pendingFitProfileToSave);
                    this.pendingFitProfileToSave = null;
                }

                setTimeout(() => {
                    window.location.reload();
                }, 400);
            } else {
                this.showAlert(alertEl, data.error || 'Registration failed.', 'danger');
            }
        } catch (err) {
            this.showAlert(alertEl, 'Server connection error. Please try again.', 'danger');
        }
    },

    saveFitProfile: async function(fitData = null) {
        const payload = fitData || (window.SmartFitSizeEngine ? window.SmartFitSizeEngine.currentMeasurements : null);

        if (!payload) {
            alert('No active scan data found. Please run a quick scan first.');
            return;
        }

        if (!this.currentUser) {
            // Prompt to login/register to save
            this.openAuthModal(payload);
            return;
        }

        const recSize = (window.SmartFitSizeEngine && window.SmartFitSizeEngine.currentResult) 
                        ? window.SmartFitSizeEngine.currentResult.recommended_size : 'M';

        try {
            const res = await fetch('api/auth_customer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'save_fit_profile',
                    height_cm: payload.height_cm || 172.0,
                    shoulder_cm: payload.shoulder_cm || 44.0,
                    chest_cm: payload.chest_cm || 98.0,
                    waist_cm: payload.waist_cm || 80.0,
                    recommended_size: recSize,
                    fit_preference: payload.fit_preference || 'regular',
                    body_build: payload.user_build || 'Regular Athletic'
                })
            });
            const data = await res.json();

            if (data.success) {
                this.currentUser.recommended_size = recSize;
                this.renderNavbarUser(this.currentUser);

                // Update save button UI
                const saveBtn = document.getElementById('saveFitToProfileBtn');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fa-solid fa-circle-check text-emerald me-1"></i> Saved to Account!';
                    saveBtn.classList.remove('btn-outline-cyber');
                    saveBtn.classList.add('btn-success');
                }
            } else {
                alert(data.error || 'Unable to save profile.');
            }
        } catch (err) {
            console.error('Save fit error:', err);
        }
    },

    openProfileModal: function() {
        if (!this.currentUser) {
            this.openAuthModal();
            return;
        }

        const u = this.currentUser;
        document.getElementById('profName').innerText = u.full_name;
        document.getElementById('profEmail').innerText = u.email;
        document.getElementById('profGender').innerText = u.gender ? (u.gender.charAt(0).toUpperCase() + u.gender.slice(1)) : "Men's";
        document.getElementById('profSize').innerText = u.recommended_size || 'M';
        document.getElementById('profHeight').innerText = (u.saved_height || 172) + ' cm';
        document.getElementById('profShoulder').innerText = (u.saved_shoulder || 44.0) + ' cm';
        document.getElementById('profBuild').innerText = u.body_build || 'Regular Athletic';
        document.getElementById('profFitPref').innerText = (u.fit_preference || 'Regular').toUpperCase();

        const modalEl = document.getElementById('customerProfileModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    },

    openWardrobeModal: async function() {
        if (!this.currentUser) {
            this.openAuthModal();
            return;
        }

        const modalEl = document.getElementById('customerWardrobeModal');
        const container = document.getElementById('wardrobeItemsContainer');

        if (modalEl && container) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            container.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border text-neon"></div></div>';

            try {
                const res = await fetch('api/auth_customer.php?action=get_wardrobe');
                const data = await res.json();

                if (data.success && data.items && data.items.length > 0) {
                    let html = '';
                    data.items.forEach(item => {
                        const itemJson = JSON.stringify(item).replace(/"/g, '&quot;');
                        html += `
                            <div class="col-md-4 col-6 mb-3">
                                <div class="glass-card p-2 text-center h-100 d-flex flex-column justify-content-between">
                                    <img src="${item.image_url}" class="rounded w-100 mb-2" style="height: 140px; object-fit: cover;" alt="${item.name}">
                                    <div class="small fw-bold text-white text-truncate">${item.name}</div>
                                    <div class="text-neon small fw-bold">Size ${item.saved_size} &bull; Rs. ${parseFloat(item.price).toLocaleString()}</div>
                                    <div class="mt-2 d-flex gap-1">
                                        <button class="btn btn-outline-cyber btn-sm flex-fill py-1" onclick="SmartFitTryOn.tryOnItem(${itemJson})">
                                            <i class="fa-solid fa-shirt"></i> Try On
                                        </button>
                                        <button class="btn btn-danger btn-sm py-1 px-2" onclick="SmartFitAuth.toggleWardrobe(${item.id})">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="fa-solid fa-shirt-long display-6 mb-3 text-secondary"></i>
                            <p class="mb-0">Your Wardrobe is currently empty.</p>
                            <small>Click "Save to Wardrobe" while trying on clothes to store your favorite looks.</small>
                        </div>
                    `;
                }
            } catch (err) {
                container.innerHTML = '<div class="col-12 text-center text-danger py-4">Error loading wardrobe.</div>';
            }
        }
    },

    toggleWardrobe: async function(clothingId, size = 'M') {
        if (!this.currentUser) {
            this.openAuthModal();
            return;
        }

        try {
            const res = await fetch('api/auth_customer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_wardrobe', clothing_id: clothingId, saved_size: size })
            });
            const data = await res.json();
            alert(data.message);
            if (document.getElementById('customerWardrobeModal')?.classList.contains('show')) {
                this.openWardrobeModal();
            }
        } catch (err) {
            console.error('Wardrobe toggle error:', err);
        }
    },

    logout: async function() {
        try {
            await fetch('api/auth_customer.php?action=logout');
            this.currentUser = null;
            this.renderNavbarGuest();
            window.location.reload();
        } catch (e) {
            window.location.reload();
        }
    },

    showAlert: function(el, msg, type) {
        if (!el) return;
        el.className = `alert alert-${type} py-2 small`;
        el.innerText = msg;
        el.classList.remove('d-none');
    }
};

// Initialize Customer Auth when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    SmartFitAuth.init();
});
