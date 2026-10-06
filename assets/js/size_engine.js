/**
 * SmartFit LK - Size Recommendation, Fit Profile & Lowest Price Matching Engine
 */

const SmartFitSizeEngine = {
    currentResult: null,
    currentMeasurements: null,

    // Standard client-side fallback charts (CM)
    sizeCharts: {
        mens: [
            { size: 'S', minSh: 39.0, maxSh: 42.5, minCh: 88.0, maxCh: 95.0, minW: 72.0, maxW: 78.0, minH: 160.0, maxH: 172.0, desc: 'Men Small (Chest: 35-37", Shoulder: 40-42cm)' },
            { size: 'M', minSh: 42.6, maxSh: 45.5, minCh: 95.1, maxCh: 102.0, minW: 78.1, maxW: 84.0, minH: 168.0, maxH: 178.0, desc: 'Men Medium (Chest: 38-40", Shoulder: 43-45cm)' },
            { size: 'L', minSh: 45.6, maxSh: 48.5, minCh: 102.1, maxCh: 109.0, minW: 84.1, maxW: 91.0, minH: 174.0, maxH: 184.0, desc: 'Men Large (Chest: 40-43", Shoulder: 46-48cm)' },
            { size: 'XL', minSh: 48.6, maxSh: 52.0, minCh: 109.1, maxCh: 117.0, minW: 91.1, maxW: 99.0, minH: 178.0, maxH: 190.0, desc: 'Men Extra Large (Chest: 43-46", Shoulder: 49-51cm)' },
            { size: 'XXL', minSh: 52.1, maxSh: 56.0, minCh: 117.1, maxCh: 126.0, minW: 99.1, maxW: 108.0, minH: 180.0, maxH: 196.0, desc: 'Men Double XL (Chest: 46-49", Shoulder: 52-55cm)' }
        ],
        womens: [
            { size: 'XS', minSh: 34.0, maxSh: 37.0, minCh: 76.0, maxCh: 82.0, minW: 58.0, maxW: 64.0, minH: 148.0, maxH: 158.0, desc: 'Women XS (Bust: 30-32", Shoulder: 35-37cm)' },
            { size: 'S', minSh: 37.1, maxSh: 39.5, minCh: 82.1, maxCh: 88.0, minW: 64.1, maxW: 70.0, minH: 154.0, maxH: 165.0, desc: 'Women Small (Bust: 32-34", Shoulder: 37-39cm)' },
            { size: 'M', minSh: 39.6, maxSh: 42.0, minCh: 88.1, maxCh: 95.0, minW: 70.1, maxW: 77.0, minH: 160.0, maxH: 172.0, desc: 'Women Medium (Bust: 35-37", Shoulder: 40-42cm)' },
            { size: 'L', minSh: 42.1, maxSh: 45.0, minCh: 95.1, maxCh: 103.0, minW: 77.1, maxW: 85.0, minH: 165.0, maxH: 178.0, desc: 'Women Large (Bust: 37-40", Shoulder: 42-44cm)' },
            { size: 'XL', minSh: 45.1, maxSh: 48.0, minCh: 103.1, maxCh: 112.0, minW: 85.1, maxW: 94.0, minH: 168.0, maxH: 182.0, desc: 'Women XL (Bust: 40-44", Shoulder: 45-47cm)' }
        ]
    },

    calculateSize: async function(measurementData) {
        this.currentMeasurements = measurementData;
        this.showLoading();

        const payload = {
            gender: measurementData.gender || 'mens',
            shoulder_cm: parseFloat(measurementData.shoulder_cm) || 44.0,
            chest_cm: parseFloat(measurementData.chest_cm) || 98.0,
            waist_cm: parseFloat(measurementData.waist_cm) || 80.0,
            height_cm: parseFloat(measurementData.height_cm) || 172.0,
            fit_preference: measurementData.fit_preference || 'regular'
        };

        let result = null;

        try {
            const queryParams = new URLSearchParams(payload).toString();
            const response = await fetch(`api/calculate_size.php?${queryParams}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            if (response.ok) {
                const json = await response.json();
                if (json && json.success) {
                    result = json;
                }
            }
        } catch (apiErr) {
            console.warn('Server API fallback:', apiErr);
        }

        if (!result) {
            result = this.computeLocalFit(payload);
        }

        this.currentResult = result;
        this.currentResult.capturedImageSrc = measurementData.capturedImageSrc;
        this.renderResults(result);

        this.updateNavFitBadge(result.recommended_size, result.confidence_score);

        if (window.SmartFitApp) {
            SmartFitApp.filterBySize(result.recommended_size);
        }

        if (window.SmartFitTryOn && measurementData.capturedImageSrc) {
            SmartFitTryOn.setUserPhoto(measurementData.capturedImageSrc);
        }
    },

    computeLocalFit: function(p) {
        const gender = p.gender || 'mens';
        const fitPref = p.fit_preference || 'regular';
        const charts = this.sizeCharts[gender] || this.sizeCharts.mens;

        let fitOffsetCm = 0;
        if (fitPref === 'oversized') fitOffsetCm = 1.8;
        if (fitPref === 'snug') fitOffsetCm = -1.5;

        const effectiveShoulder = p.shoulder_cm + fitOffsetCm;

        let bestSize = 'M';
        let highestScore = 0;
        const sizeBreakdown = {};

        charts.forEach(c => {
            const midSh = (c.minSh + c.maxSh) / 2;
            const rangeSh = Math.max(1.0, (c.maxSh - c.minSh) / 2);
            const shDiff = Math.abs(effectiveShoulder - midSh);
            const shScore = Math.max(0, 100 - (shDiff / rangeSh) * 25);

            const midCh = (c.minCh + c.maxCh) / 2;
            const rangeCh = Math.max(1.0, (c.maxCh - c.minCh) / 2);
            const chDiff = Math.abs(p.chest_cm - midCh);
            const chScore = Math.max(0, 100 - (chDiff / rangeCh) * 25);

            const midW = (c.minW + c.maxW) / 2;
            const rangeW = Math.max(1.0, (c.maxW - c.minW) / 2);
            const wDiff = Math.abs(p.waist_cm - midW);
            const wScore = Math.max(0, 100 - (wDiff / rangeW) * 25);

            const midH = (c.minH + c.maxH) / 2;
            const rangeH = Math.max(1.0, (c.maxH - c.minH) / 2);
            const hDiff = Math.abs(p.height_cm - midH);
            const hScore = Math.max(0, 100 - (hDiff / rangeH) * 25);

            let total = (shScore * 0.40) + (chScore * 0.35) + (wScore * 0.15) + (hScore * 0.10);
            total = parseFloat(Math.min(98.5, Math.max(10.0, total)).toFixed(1));

            sizeBreakdown[c.size] = {
                score: total,
                description: c.desc,
                shoulder_range: `${c.minSh} - ${c.maxSh} cm`,
                chest_range: `${c.minCh} - ${c.maxCh} cm`
            };

            if (total > highestScore) {
                highestScore = total;
                bestSize = c.size;
            }
        });

        // Filter and sort products by price (Lowest Price First!)
        let matchingProds = [];
        if (window.SmartFitApp && window.SmartFitApp.clothesList) {
            matchingProds = window.SmartFitApp.clothesList.filter(item => {
                const gMatch = item.gender === gender || item.gender === 'unisex';
                const sMatch = item.sizes && item.sizes[bestSize] > 0;
                return gMatch && sMatch;
            });

            // Sort by price ascending
            matchingProds.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));

            matchingProds = matchingProds.map((item, idx) => {
                const rank = idx + 1;
                return {
                    ...item,
                    rank: rank,
                    rank_badge: rank === 1 ? '🥇 Lowest Price Match' : (rank === 2 ? '🥈 Value Pick' : '🥉 Classic Choice'),
                    badge_class: rank === 1 ? 'bg-warning text-dark fw-bold' : 'bg-secondary',
                    fit_score: Math.max(84, Math.round(highestScore - (idx * 1.5))),
                    price_formatted: 'Rs. ' + parseFloat(item.price).toLocaleString()
                };
            }).slice(0, 9);
        }

        const shoulderFrame = p.shoulder_cm < 41.5 ? 'Slim / Lean Frame (< 41.5 cm)' : (p.shoulder_cm > 46.5 ? 'Broad / Athletic Frame (> 46.5 cm)' : 'Medium Frame (42 - 45 cm)');
        const buildType = p.shoulder_cm > 46 ? 'Athletic V-Taper' : (p.shoulder_cm < 41 ? 'Slim / Ectomorph' : 'Regular Athletic');

        return {
            success: true,
            recommended_size: bestSize,
            confidence_score: highestScore,
            fit_profile: {
                height_display: `~${Math.round(p.height_cm)} cm`,
                shoulder_frame: shoulderFrame,
                upper_body_build: buildType,
                waist_profile: `~${Math.round(p.waist_cm)} cm (${Math.round(p.waist_cm / 2.54)} in)`,
                category_recommendations: {
                    tshirt: bestSize,
                    shirt_polo: bestSize,
                    hoodie: bestSize === 'S' ? 'M' : (bestSize === 'M' ? 'L' : 'XL')
                }
            },
            measurements: {
                shoulder_cm: p.shoulder_cm,
                chest_cm: p.chest_cm,
                waist_cm: p.waist_cm,
                height_cm: p.height_cm,
                gender: gender,
                fit_preference: fitPref
            },
            size_breakdown: sizeBreakdown,
            recommended_products: matchingProds
        };
    },

    showLoading: function() {
        const scanModal = bootstrap.Modal.getInstance(document.getElementById('scannerModal'));
        if (scanModal) scanModal.hide();

        const resultModalEl = document.getElementById('sizeResultModal');
        if (resultModalEl) {
            const resultModal = new bootstrap.Modal(resultModalEl);
            document.getElementById('sizeResultLoading').classList.remove('d-none');
            document.getElementById('sizeResultContent').classList.add('d-none');
            resultModal.show();
        }
    },

    renderResults: function(data) {
        document.getElementById('sizeResultLoading').classList.add('d-none');
        document.getElementById('sizeResultContent').classList.remove('d-none');

        // Hero Recommended Size
        const sizeHero = document.getElementById('resHeroSize');
        if (sizeHero) sizeHero.innerText = data.recommended_size;

        // Confidence score
        const confText = document.getElementById('resConfidenceScore');
        if (confText) confText.innerText = Math.round(data.confidence_score) + '%';

        const confBar = document.getElementById('resConfidenceBar');
        if (confBar) confBar.style.width = data.confidence_score + '%';

        // Category-Specific Recommendations
        const catRecs = data.fit_profile?.category_recommendations || {
            tshirt: data.recommended_size,
            shirt_polo: data.recommended_size,
            hoodie: data.recommended_size === 'S' ? 'M' : (data.recommended_size === 'M' ? 'L' : 'XL')
        };
        const recT = document.getElementById('recTshirtSize');
        if (recT) recT.innerText = catRecs.tshirt || data.recommended_size;
        const recP = document.getElementById('recPoloSize');
        if (recP) recP.innerText = catRecs.shirt_polo || data.recommended_size;
        const recH = document.getElementById('recHoodieSize');
        if (recH) recH.innerText = catRecs.hoodie || data.recommended_size;

        // AI Estimated Fit Profile Details
        const fp = data.fit_profile || {};
        const m = data.measurements || {};

        const heightVal = document.getElementById('resHeightVal');
        if (heightVal) heightVal.innerText = fp.height_display || `~${Math.round(m.height_cm || 172)} cm`;

        const shoulderVal = document.getElementById('resShoulderVal');
        if (shoulderVal) shoulderVal.innerText = fp.shoulder_frame || 'Medium (42-45 cm)';

        const buildVal = document.getElementById('resBuildVal');
        if (buildVal) buildVal.innerText = fp.upper_body_build || 'Regular Athletic';

        const waistVal = document.getElementById('resWaistVal');
        if (waistVal) waistVal.innerText = fp.waist_profile || `~${Math.round(m.waist_cm || 80)} cm`;

        // Sync fine-tune sliders
        const shSlider = document.getElementById('tuneShoulderSlider');
        if (shSlider && m.shoulder_cm) {
            shSlider.value = m.shoulder_cm;
            document.getElementById('tuneShoulderVal').innerText = m.shoulder_cm + ' cm';
        }
        const hSlider = document.getElementById('tuneHeightSlider');
        if (hSlider && m.height_cm) {
            hSlider.value = m.height_cm;
            document.getElementById('tuneHeightVal').innerText = Math.round(m.height_cm) + ' cm';
        }

        // Render Size Chart Match Distribution Bars
        const distContainer = document.getElementById('resSizeDistribution');
        if (distContainer && data.size_breakdown) {
            let html = '';
            for (const [sizeName, info] of Object.entries(data.size_breakdown)) {
                const isBest = sizeName === data.recommended_size;
                const barColor = isBest ? 'bg-primary' : 'bg-secondary';
                html += `
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="${isBest ? 'fw-bold text-neon' : 'text-muted'}">Size ${sizeName} ${isBest ? '(Best Match ★)' : ''}</span>
                            <span class="${isBest ? 'fw-bold text-white' : 'text-muted'}">${info.score}%</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(255,255,255,0.08);">
                            <div class="progress-bar ${barColor}" style="width: ${info.score}%;"></div>
                        </div>
                    </div>
                `;
            }
            distContainer.innerHTML = html;
        }

        // 💰 Render Ranked Matched Products (Lowest Price Match First!)
        const prodContainer = document.getElementById('resRecommendedProducts');
        if (prodContainer) {
            let prods = data.recommended_products;
            if (!prods || prods.length === 0) {
                if (window.SmartFitApp && window.SmartFitApp.clothesList) {
                    prods = window.SmartFitApp.clothesList.filter(item => {
                        return item.sizes && item.sizes[data.recommended_size] > 0;
                    }).sort((a, b) => parseFloat(a.price) - parseFloat(b.price)).slice(0, 9);
                }
            }

            if (!prods || prods.length === 0) {
                prodContainer.innerHTML = `<div class="col-12 text-center text-muted py-3">No matching items currently in stock for Size ${data.recommended_size}.</div>`;
            } else {
                let phtml = '';
                prods.forEach((p, idx) => {
                    const isLowest = idx === 0;
                    const cardClass = isLowest ? 'card-lowest-price' : '';
                    const badgeRank = p.rank_badge || (isLowest ? '🥇 Lowest Price Match' : (idx === 1 ? '🥈 Value Pick' : '🥉 Choice'));
                    const badgeClass = isLowest ? 'badge-gold' : (idx === 1 ? 'badge-silver' : 'badge-bronze');
                    const priceFormatted = p.price_formatted || ('Rs. ' + parseFloat(p.price).toLocaleString());
                    const fitScore = p.fit_score || Math.max(85, Math.round(data.confidence_score - (idx * 1.5)));

                    const itemDataAttr = JSON.stringify(p).replace(/"/g, '&quot;');
                    const whatsappMsg = encodeURIComponent(`Hi SmartFit LK! I scanned my size (${data.recommended_size}) and would like to order: ${p.name} (${p.item_code}) for ${priceFormatted}.`);

                    phtml += `
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="product-card ${cardClass} h-100 d-flex flex-column justify-content-between p-2">
                                <div class="position-relative">
                                    <div class="product-img-wrap mb-2" style="height: 180px; overflow: hidden; border-radius: 8px;">
                                        <img src="${p.image_url}" class="product-img w-100 h-100" style="object-fit: cover;" alt="${p.name}">
                                    </div>
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge ${badgeClass} p-1 px-2" style="font-size: 0.72rem;">${badgeRank}</span>
                                    </div>
                                </div>

                                <div class="product-body flex-grow-1 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="text-neon fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">${p.brand || 'SmartFit'} &bull; Fit ${data.recommended_size}</div>
                                        <h6 class="product-title text-white my-1 fw-bold text-truncate" title="${p.name}" style="font-size: 0.88rem;">${p.name}</h6>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fs-6 fw-bold text-white">${priceFormatted}</span>
                                            <span class="badge bg-dark border border-secondary text-emerald" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-bullseye me-1"></i>${fitScore}% Fit
                                            </span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-1 mt-2">
                                        <button class="btn btn-sm btn-outline-cyber flex-fill py-1 px-2 d-flex align-items-center justify-content-center gap-1" style="font-size: 0.78rem;" onclick="SmartFitTryOn.tryOnItem(${itemDataAttr})">
                                            <i class="fa-solid fa-shirt"></i> Try On
                                        </button>
                                        <a href="https://wa.me/94771234567?text=${whatsappMsg}" target="_blank" class="btn btn-sm btn-success px-2 py-1" title="Order via WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                prodContainer.innerHTML = phtml;
            }
        }
    },

    // Interactive Live Recalculation from Sliders
    onFineTuneChange: function() {
        if (!this.currentMeasurements) return;
        const sh = parseFloat(document.getElementById('tuneShoulderSlider').value);
        const h = parseFloat(document.getElementById('tuneHeightSlider').value);
        const fit = document.querySelector('input[name="tuneFitPref"]:checked')?.value || 'regular';

        document.getElementById('tuneShoulderVal').innerText = sh + ' cm';
        document.getElementById('tuneHeightVal').innerText = Math.round(h) + ' cm';

        const updated = {
            gender: this.currentMeasurements.gender || 'mens',
            shoulder_cm: sh,
            chest_cm: this.currentMeasurements.gender === 'womens' ? (sh * 2.25) : (sh * 2.22),
            waist_cm: this.currentMeasurements.gender === 'womens' ? (sh * 2.25 * 0.77) : (sh * 2.22 * 0.82),
            height_cm: h,
            fit_preference: fit
        };

        const result = this.computeLocalFit(updated);
        this.renderResults(result);
        this.updateNavFitBadge(result.recommended_size, result.confidence_score);

        if (window.SmartFitApp) {
            SmartFitApp.filterBySize(result.recommended_size);
        }
    },

    updateNavFitBadge: function(size, conf) {
        const badge = document.getElementById('navFitBadge');
        const sizeEl = document.getElementById('navDetectedSize');
        const confEl = document.getElementById('navDetectedConf');
        if (badge && sizeEl) {
            sizeEl.innerText = size;
            if (confEl) confEl.innerText = `(${Math.round(conf)}%)`;
            badge.classList.remove('d-none');
        }
    }
};