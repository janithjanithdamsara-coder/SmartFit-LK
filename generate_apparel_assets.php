<?php
// SmartFit AI - Transparent Apparel Asset Generator
// Generates clean transparent SVG and PNG overlays for Virtual Try-On Atelier

$dir = __DIR__ . '/assets/images/products';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// 1. Black 1/4 Zip Polo
$svgBlackPolo = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 580" width="500" height="580">
  <defs>
    <linearGradient id="poloBlackGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#24272c" />
      <stop offset="50%" stop-color="#181a1e" />
      <stop offset="100%" stop-color="#101114" />
    </linearGradient>
    <linearGradient id="sleeveLGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1c1e22" />
      <stop offset="100%" stop-color="#121316" />
    </linearGradient>
    <linearGradient id="collarGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#33373d" />
      <stop offset="100%" stop-color="#1a1c20" />
    </linearGradient>
    <filter id="garmentShadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.6"/>
    </filter>
  </defs>
  
  <g filter="url(#garmentShadow)">
    <!-- Left Sleeve -->
    <path d="M 140,85 L 35,175 L 75,235 L 145,175 Z" fill="url(#sleeveLGrad)" stroke="#2d3036" stroke-width="1.5" />
    <!-- Right Sleeve -->
    <path d="M 360,85 L 465,175 L 425,235 L 355,175 Z" fill="url(#sleeveLGrad)" stroke="#2d3036" stroke-width="1.5" />
    
    <!-- Torso Body -->
    <path d="M 140,85 C 180,95 320,95 360,85 L 365,180 C 355,270 360,430 365,540 C 310,548 190,548 135,540 C 140,430 145,270 135,180 Z" fill="url(#poloBlackGrad)" stroke="#2d3036" stroke-width="2" />
    
    <!-- Athletic Contouring Side Seams -->
    <path d="M 155,180 Q 170,330 155,520" fill="none" stroke="#2c3036" stroke-width="2" stroke-dasharray="4,2" opacity="0.6" />
    <path d="M 345,180 Q 330,330 345,520" fill="none" stroke="#2c3036" stroke-width="2" stroke-dasharray="4,2" opacity="0.6" />
    
    <!-- Chest Muscle Contour Shadows -->
    <path d="M 180,210 Q 250,235 320,210" fill="none" stroke="#0e1012" stroke-width="3" opacity="0.5" />
    
    <!-- Carnage High-Build Silicone Logo -->
    <g transform="translate(190, 195) scale(0.65)">
      <path d="M 5,20 L 25,5 L 45,20 L 35,22 L 25,12 L 15,22 Z" fill="#ffffff" opacity="0.9" />
      <text x="52" y="20" font-family="'Inter', sans-serif" font-weight="900" font-size="14" fill="#ffffff" letter-spacing="2" opacity="0.95">CARNAGE</text>
    </g>
    
    <!-- 1/4 Zipper Placket -->
    <rect x="244" y="80" width="12" height="115" rx="3" fill="#15171a" stroke="#33373d" stroke-width="1.5" />
    <line x1="250" y1="80" x2="250" y2="190" stroke="#71717a" stroke-width="2" stroke-dasharray="2,2" />
    <!-- Metal Zipper Puller -->
    <rect x="247" y="165" width="6" height="14" rx="2" fill="#d4d4d8" />
    
    <!-- Athletic Polo Collar (Folded V-Collar) -->
    <!-- Left Collar Wing -->
    <path d="M 180,72 Q 215,95 244,145 L 244,80 Q 215,62 180,72 Z" fill="url(#collarGrad)" stroke="#3a3e45" stroke-width="1.5" />
    <!-- Right Collar Wing -->
    <path d="M 320,72 Q 285,95 256,145 L 256,80 Q 285,62 320,72 Z" fill="url(#collarGrad)" stroke="#3a3e45" stroke-width="1.5" />
    <!-- Back Neckline -->
    <path d="M 180,72 C 215,55 285,55 320,72 C 285,65 215,65 180,72 Z" fill="#121316" stroke="#2a2c30" stroke-width="1" />

    <!-- Hem Band -->
    <path d="M 135,530 C 190,538 310,538 365,530 L 365,540 C 310,548 190,548 135,540 Z" fill="#121315" opacity="0.7" />
  </g>
</svg>
SVG;

// 2. Grey Heather Polo
$svgGreyPolo = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 580" width="500" height="580">
  <defs>
    <linearGradient id="poloGreyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#6b7280" />
      <stop offset="50%" stop-color="#4b5563" />
      <stop offset="100%" stop-color="#374151" />
    </linearGradient>
    <linearGradient id="sleeveGreyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#5a626f" />
      <stop offset="100%" stop-color="#3b434e" />
    </linearGradient>
    <linearGradient id="collarGreyGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#7e8694" />
      <stop offset="100%" stop-color="#4b5563" />
    </linearGradient>
    <filter id="garmentShadowG" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.6"/>
    </filter>
  </defs>
  
  <g filter="url(#garmentShadowG)">
    <!-- Left Sleeve -->
    <path d="M 140,85 L 35,175 L 75,235 L 145,175 Z" fill="url(#sleeveGreyGrad)" stroke="#4b5563" stroke-width="1.5" />
    <!-- Right Sleeve -->
    <path d="M 360,85 L 465,175 L 425,235 L 355,175 Z" fill="url(#sleeveGreyGrad)" stroke="#4b5563" stroke-width="1.5" />
    
    <!-- Torso Body -->
    <path d="M 140,85 C 180,95 320,95 360,85 L 365,180 C 355,270 360,430 365,540 C 310,548 190,548 135,540 C 140,430 145,270 135,180 Z" fill="url(#poloGreyGrad)" stroke="#4b5563" stroke-width="2" />
    
    <!-- Seams -->
    <path d="M 155,180 Q 170,330 155,520" fill="none" stroke="#374151" stroke-width="2" stroke-dasharray="4,2" opacity="0.6" />
    <path d="M 345,180 Q 330,330 345,520" fill="none" stroke="#374151" stroke-width="2" stroke-dasharray="4,2" opacity="0.6" />
    
    <!-- Carnage Logo -->
    <g transform="translate(190, 195) scale(0.65)">
      <path d="M 5,20 L 25,5 L 45,20 L 35,22 L 25,12 L 15,22 Z" fill="#111827" opacity="0.9" />
      <text x="52" y="20" font-family="'Inter', sans-serif" font-weight="900" font-size="14" fill="#111827" letter-spacing="2">CARNAGE</text>
    </g>
    
    <!-- 1/4 Zipper Placket -->
    <rect x="244" y="80" width="12" height="115" rx="3" fill="#374151" stroke="#4b5563" stroke-width="1.5" />
    <line x1="250" y1="80" x2="250" y2="190" stroke="#9ca3af" stroke-width="2" stroke-dasharray="2,2" />
    <rect x="247" y="165" width="6" height="14" rx="2" fill="#d1d5db" />
    
    <!-- Collar Wings -->
    <path d="M 180,72 Q 215,95 244,145 L 244,80 Q 215,62 180,72 Z" fill="url(#collarGreyGrad)" stroke="#6b7280" stroke-width="1.5" />
    <path d="M 320,72 Q 285,95 256,145 L 256,80 Q 285,62 320,72 Z" fill="url(#collarGreyGrad)" stroke="#6b7280" stroke-width="1.5" />
    <path d="M 180,72 C 215,55 285,55 320,72 C 285,65 215,65 180,72 Z" fill="#2d3748" stroke="#374151" stroke-width="1" />
  </g>
</svg>
SVG;

// 3. White Sheer Polo
$svgWhitePolo = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 580" width="500" height="580">
  <defs>
    <linearGradient id="poloWhiteGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#ffffff" />
      <stop offset="50%" stop-color="#f3f4f6" />
      <stop offset="100%" stop-color="#e5e7eb" />
    </linearGradient>
    <linearGradient id="sleeveWGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#f9fafb" />
      <stop offset="100%" stop-color="#e5e7eb" />
    </linearGradient>
    <filter id="garmentShadowW" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.4"/>
    </filter>
  </defs>
  
  <g filter="url(#garmentShadowW)">
    <!-- Left Sleeve -->
    <path d="M 140,85 L 35,175 L 75,235 L 145,175 Z" fill="url(#sleeveWGrad)" stroke="#d1d5db" stroke-width="1.5" />
    <!-- Right Sleeve -->
    <path d="M 360,85 L 465,175 L 425,235 L 355,175 Z" fill="url(#sleeveWGrad)" stroke="#d1d5db" stroke-width="1.5" />
    
    <!-- Torso Body -->
    <path d="M 140,85 C 180,95 320,95 360,85 L 365,180 C 355,270 360,430 365,540 C 310,548 190,548 135,540 C 140,430 145,270 135,180 Z" fill="url(#poloWhiteGrad)" stroke="#d1d5db" stroke-width="2" />
    
    <!-- Carnage Black Logo on White -->
    <g transform="translate(190, 195) scale(0.65)">
      <path d="M 5,20 L 25,5 L 45,20 L 35,22 L 25,12 L 15,22 Z" fill="#18181b" />
      <text x="52" y="20" font-family="'Inter', sans-serif" font-weight="900" font-size="14" fill="#18181b" letter-spacing="2">CARNAGE</text>
    </g>
    
    <!-- 1/4 Zipper Placket (Matte Black Contrast) -->
    <rect x="244" y="80" width="12" height="115" rx="3" fill="#1f2937" stroke="#111827" stroke-width="1.5" />
    <line x1="250" y1="80" x2="250" y2="190" stroke="#9ca3af" stroke-width="2" stroke-dasharray="2,2" />
    <rect x="247" y="165" width="6" height="14" rx="2" fill="#d1d5db" />
    
    <!-- Collar Wings -->
    <path d="M 180,72 Q 215,95 244,145 L 244,80 Q 215,62 180,72 Z" fill="#ffffff" stroke="#d1d5db" stroke-width="1.5" />
    <path d="M 320,72 Q 285,95 256,145 L 256,80 Q 285,62 320,72 Z" fill="#ffffff" stroke="#d1d5db" stroke-width="1.5" />
    <path d="M 180,72 C 215,55 285,55 320,72 C 285,65 215,65 180,72 Z" fill="#d1d5db" stroke="#9ca3af" stroke-width="1" />
  </g>
</svg>
SVG;

// 4. Oversized Boxy Streetwear Tee
$svgOversizedTee = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 580" width="500" height="580">
  <defs>
    <linearGradient id="teeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#33373e" />
      <stop offset="50%" stop-color="#24272c" />
      <stop offset="100%" stop-color="#181a1e" />
    </linearGradient>
    <filter id="garmentShadowT" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.6"/>
    </filter>
  </defs>
  
  <g filter="url(#garmentShadowT)">
    <!-- Drop Shoulder Sleeves -->
    <path d="M 120,95 L 15,200 L 65,270 L 130,210 Z" fill="#24272c" stroke="#3b3f46" stroke-width="1.5" />
    <path d="M 380,95 L 485,200 L 435,270 L 370,210 Z" fill="#24272c" stroke="#3b3f46" stroke-width="1.5" />
    
    <!-- Boxy Body -->
    <path d="M 120,95 C 170,105 330,105 380,95 L 380,210 C 375,320 375,440 375,555 C 310,560 190,560 125,555 C 125,440 125,320 120,210 Z" fill="url(#teeGrad)" stroke="#3b3f46" stroke-width="2" />
    
    <!-- Thick Ribbed Crew Neck -->
    <path d="M 180,82 C 210,120 290,120 320,82 C 290,68 210,68 180,82 Z" fill="#181a1e" stroke="#454952" stroke-width="2.5" />
    
    <!-- Oversized Streetwear High-Density Graphic Print -->
    <g transform="translate(145, 230)">
      <rect x="0" y="0" width="210" height="150" rx="6" fill="#101114" opacity="0.6" stroke="#3a3e46" stroke-width="1"/>
      <text x="105" y="45" font-family="'Impact', 'Arial Black', sans-serif" font-size="32" fill="#e4e4e7" text-anchor="middle" letter-spacing="4">CARNAGE</text>
      <text x="105" y="75" font-family="'Inter', sans-serif" font-weight="900" font-size="14" fill="#a1a1aa" text-anchor="middle" letter-spacing="6">HEAVYWEIGHT</text>
      <line x1="30" y1="92" x2="180" y2="92" stroke="#6366f1" stroke-width="3" />
      <text x="105" y="118" font-family="'Inter', sans-serif" font-weight="700" font-size="11" fill="#71717a" text-anchor="middle" letter-spacing="3">DIVISION 0992 // ATHLETIC</text>
    </g>
  </g>
</svg>
SVG;

// 5. Women's Performance Athletic Crop Top / Tank
$svgCropTop = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 580" width="500" height="580">
  <defs>
    <linearGradient id="cropGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#27272a" />
      <stop offset="50%" stop-color="#18181b" />
      <stop offset="100%" stop-color="#09090b" />
    </linearGradient>
    <filter id="garmentShadowC" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.6"/>
    </filter>
  </defs>
  <g filter="url(#garmentShadowC)">
    <!-- Straps & Body -->
    <path d="M 160,80 L 195,80 L 220,180 C 235,185 265,185 280,180 L 305,80 L 340,80 L 355,200 C 340,260 345,340 350,380 C 300,385 200,385 150,380 C 155,340 160,260 145,200 Z" fill="url(#cropGrad)" stroke="#3f3f46" stroke-width="2" />
    <!-- White Side Athletic Racing Stripes -->
    <path d="M 155,210 Q 165,300 158,375" fill="none" stroke="#ffffff" stroke-width="3" opacity="0.9"/>
    <path d="M 345,210 Q 335,300 342,375" fill="none" stroke="#ffffff" stroke-width="3" opacity="0.9"/>
    <!-- Deep Scoop Neckline -->
    <path d="M 195,80 C 215,160 285,160 305,80" fill="none" stroke="#3f3f46" stroke-width="2" />
    <!-- Carnage Chest Logo -->
    <g transform="translate(205, 240) scale(0.6)">
      <path d="M 5,20 L 25,5 L 45,20 L 35,22 L 25,12 L 15,22 Z" fill="#ffffff" opacity="0.95" />
      <text x="52" y="20" font-family="'Inter', sans-serif" font-weight="900" font-size="14" fill="#ffffff" letter-spacing="2">CARNAGE</text>
    </g>
    <!-- Underbust Compression Band -->
    <rect x="150" y="355" width="200" height="25" rx="3" fill="#09090b" stroke="#3f3f46" stroke-width="1.5" />
  </g>
</svg>
SVG;

// 6. Signature Athleisure Hoodie
$svgHoodie = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 580" width="500" height="580">
  <defs>
    <linearGradient id="hoodieGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#2a2d34" />
      <stop offset="50%" stop-color="#1c1e23" />
      <stop offset="100%" stop-color="#121316" />
    </linearGradient>
    <filter id="garmentShadowH" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.6"/>
    </filter>
  </defs>
  <g filter="url(#garmentShadowH)">
    <!-- Long Sleeves with Ribbed Cuffs -->
    <path d="M 130,90 L 25,230 L 60,320 L 135,210 Z" fill="#1c1e23" stroke="#33373e" stroke-width="1.5" />
    <rect x="25" y="300" width="40" height="22" rx="3" fill="#121316" stroke="#33373e" transform="rotate(-30 25 300)" />
    
    <path d="M 370,90 L 475,230 L 440,320 L 365,210 Z" fill="#1c1e23" stroke="#33373e" stroke-width="1.5" />
    <rect x="435" y="300" width="40" height="22" rx="3" fill="#121316" stroke="#33373e" transform="rotate(30 435 300)" />

    <!-- Torso Body -->
    <path d="M 130,90 C 170,100 330,100 370,90 L 375,210 C 370,330 370,450 370,550 C 310,555 190,555 130,550 C 130,450 130,330 125,210 Z" fill="url(#hoodieGrad)" stroke="#33373e" stroke-width="2" />

    <!-- Kangaroo Pocket -->
    <path d="M 180,380 L 320,380 L 345,490 L 155,490 Z" fill="#15171b" stroke="#383c44" stroke-width="1.5" />
    <circle cx="205" cy="435" r="8" fill="#1c1e23" opacity="0.4"/>
    <circle cx="295" cy="435" r="8" fill="#1c1e23" opacity="0.4"/>

    <!-- Double Layered Hood Structure -->
    <path d="M 170,80 C 160,20 340,20 330,80 C 290,110 210,110 170,80 Z" fill="#15171b" stroke="#3f434c" stroke-width="2" />
    <path d="M 185,75 C 185,35 315,35 315,75 C 285,95 215,95 185,75 Z" fill="#0d0e10" />

    <!-- Hood Drawstrings with Metal Aglets -->
    <path d="M 220,95 Q 215,180 210,240" fill="none" stroke="#d4d4d8" stroke-width="3" stroke-linecap="round" />
    <rect x="207" y="235" width="6" height="15" rx="2" fill="#71717a" />
    <path d="M 280,95 Q 285,180 290,240" fill="none" stroke="#d4d4d8" stroke-width="3" stroke-linecap="round" />
    <rect x="287" y="235" width="6" height="15" rx="2" fill="#71717a" />

    <!-- Minimalist Carnage Emblem -->
    <g transform="translate(205, 175) scale(0.65)">
      <path d="M 5,20 L 25,5 L 45,20 L 35,22 L 25,12 L 15,22 Z" fill="#ffffff" opacity="0.9" />
      <text x="52" y="20" font-family="'Inter', sans-serif" font-weight="900" font-size="14" fill="#ffffff" letter-spacing="2">CARNAGE</text>
    </g>
  </g>
</svg>
SVG;

// Save SVGs
$assets = [
    'polo_black.svg' => $svgBlackPolo,
    'polo_grey.svg'  => $svgGreyPolo,
    'polo_white.svg' => $svgWhitePolo,
    'oversized_tee.svg' => $svgOversizedTee,
    'crop_top.svg'   => $svgCropTop,
    'hoodie_black.svg' => $svgHoodie,
    'short_grey.svg' => $svgGreyPolo,
    'short_black.svg' => $svgBlackPolo
];

foreach ($assets as $filename => $svgContent) {
    file_put_contents($dir . '/' . $filename, trim($svgContent));
    echo "Saved: assets/images/products/$filename\n";
}

echo "All apparel vector assets generated successfully!\n";
