import matplotlib.pyplot as plt
import matplotlib.patches as patches
import os

def create_architecture_diagram():
    # Setup figure with 16:9 ratio and ultra-high DPI
    fig, ax = plt.subplots(figsize=(16, 9), dpi=300)
    fig.patch.set_facecolor('#070b14')
    ax.set_facecolor('#070b14')
    ax.set_xlim(0, 160)
    ax.set_ylim(0, 90)
    ax.axis('off')

    # Color palette
    c_indigo = '#6366f1'
    c_emerald = '#10b981'
    c_amber = '#f59e0b'
    c_cyan = '#06b6d4'
    c_card_bg = '#0f172a'
    c_card_border = '#334155'
    c_text_main = '#f8fafc'
    c_text_sub = '#94a3b8'

    # Title & Subtitle Header
    ax.text(80, 85.8, "SMARTFIT LK — SYSTEM ARCHITECTURE DIAGRAM", 
            fontsize=20, weight='bold', color=c_text_main, ha='center', fontfamily='sans-serif')
    ax.text(80, 82.6, "AI-Assisted Body Fit Profile, Lowest-Price Matching & 2D Virtual Fitting Engine | IntelliCon '26", 
            fontsize=10.5, color=c_indigo, ha='center', fontfamily='sans-serif')

    def draw_box(x, y, w, h, title, subtitle, items, border_color, accent_color, badge=""):
        # Shadow/glow
        shadow = patches.FancyBboxPatch((x-0.4, y-0.4), w+0.8, h+0.8, boxstyle="round,pad=0.3,rounding_size=1.2",
                                        facecolor=border_color, alpha=0.12, edgecolor='none')
        ax.add_patch(shadow)
        
        # Card Body
        box = patches.FancyBboxPatch((x, y), w, h, boxstyle="round,pad=0.3,rounding_size=1.0",
                                     facecolor=c_card_bg, edgecolor=border_color, linewidth=1.6)
        ax.add_patch(box)

        # Header bar
        header_bar = patches.FancyBboxPatch((x, y + h - 3.8), w, 3.8, boxstyle="round,pad=0.2,rounding_size=0.6",
                                            facecolor=border_color, alpha=0.2, edgecolor='none')
        ax.add_patch(header_bar)

        # Title
        ax.text(x + 2, y + h - 2.5, title, fontsize=11, weight='bold', color=c_text_main, va='center')
        if badge:
            # Badge pill
            ax.text(x + w - 2, y + h - 2.5, badge, fontsize=8, weight='bold', color=accent_color, ha='right', va='center')

        # Subtitle
        if subtitle:
            ax.text(x + 2, y + h - 5.2, subtitle, fontsize=8.2, style='italic', color=c_text_sub, va='center')

        # Items list
        start_y = y + h - (7.2 if subtitle else 5.2)
        for i, itm in enumerate(items):
            cur_y = start_y - (i * 2.7)
            ax.plot(x + 2.5, cur_y, marker='s', markersize=3.2, color=accent_color)
            ax.text(x + 4.2, cur_y, itm, fontsize=8.0, color=c_text_main, va='center')

    # ================= LAYER 1: CLIENT FRONTEND (Top Row) =================
    ax.text(7, 76.8, "LAYER 1: CLIENT-SIDE PRESENTATION & AR INTERACTION (Browser)", 
            fontsize=9.5, weight='bold', color='#a5b4fc',
            bbox=dict(boxstyle="round,pad=0.3", facecolor='#1e1b4b', edgecolor=c_indigo, alpha=0.8, lw=0.8))
    
    # Box 1.1: WebCam & UI Portal
    draw_box(7, 49, 44, 25, 
             "CLIENT STOREFRONT & SCANNER", "HTML5, Bootstrap 5 & Cyber Theme",
             ["Live WebCam Feed & Stream Capture",
              "Dynamic Apparel Catalog (Filter by Size)",
              "Lowest Price Matched Badges (Best Value)",
              "Customer Fit Profile & Wardrobe",
              "Admin Dashboard (Inventory & Size Charts)",
              "Direct WhatsApp Quick Order Bridge"],
             c_indigo, c_indigo, "UI / UX")

    # Box 1.2: Client-side AI Computer Vision
    draw_box(57, 49, 46, 25,
             "CLIENT-SIDE AI VISION ENGINE", "Google MediaPipe Pose (WASM / WebGL)",
             ["33 3D Skeletal Landmark Detection",
              "Live Upper-Body Tracking (30+ FPS)",
              "Facing & Posture Quality Validator",
              "Anthropometric Torso & Shoulder Calc",
              "Manual Fallback Measurement Form",
              "Zero-Latency Local Frame Processing"],
             c_cyan, c_cyan, "EDGE AI")

    # Box 1.3: 2D Virtual Try-On Atelier
    draw_box(109, 49, 44, 25,
             "2D VIRTUAL TRY-ON CANVAS", "HTML5 Canvas 2D AR Fitting",
             ["Anchor Matching (Neck, Shoulders, Waist)",
              "Linear Interpolation (0.35 LERP Smoothing)",
              "Garment Drag, Manual Scale & Opacity",
              "Live Mirror Mode (Follows Movements)",
              "Transparent PNG Texture Mapping",
              "High-Res Try-On Snapshot Generator"],
             c_emerald, c_emerald, "AR PREVIEW")

    # ================= LAYER 2: BACKEND & DATA (Bottom Row) =================
    ax.text(7, 45.0, "LAYER 2: BUSINESS LOGIC, APIS & DATA PERSISTENCE (Server)", 
            fontsize=9.2, weight='bold', color='#fde047',
            bbox=dict(boxstyle="round,pad=0.28", facecolor='#422006', edgecolor=c_amber, alpha=0.85, lw=0.8))

    # Box 2.1: Recommendation & Pricing Engine
    draw_box(7, 12, 44, 25,
             "INTELLIGENT SIZE & PRICE ENGINE", "PHP REST Core Algorithms",
             ["Fit Profile Generator (Height, Shoulders, Build)",
              "Category Size Chart Mapping (Tee, Polo, Hoodie)",
              "Lowest-Price Sorting (ASC) & Matcher",
              "Fit Confidence Scoring (85% - 98%)",
              "Alternative & Premium Pick Suggestion",
              "Adaptive Fit Preference (Slim, Regular, Relaxed)"],
             c_amber, c_amber, "ALGORITHM")

    # Box 2.2: REST API Controllers
    draw_box(57, 12, 46, 25,
             "REST API CONTROLLERS & AUTH", "Modular PHP Endpoints (/api/)",
             ["api/auth_customer.php (Register, Login, Session)",
              "Permanent Saved Fit Profile Handler",
              "Customer Virtual Wardrobe Manager",
              "api/products.php (Catalog & Stock Query)",
              "api/size_engine.php (Benchmark Testing)",
              "Admin Auth & Secure CRUD API"],
             c_indigo, c_indigo, "BACKEND")

    # Box 2.3: Relational Database
    draw_box(109, 12, 44, 25,
             "MYSQL RELATIONAL DATABASE", "XAMPP MariaDB Storage",
             ["`customers` (Account, saved_height, size)",
              "`customer_wardrobe` (Saved look bookmarks)",
              "`products` (SKU, brand, price, PNG assets)",
              "`product_sizes` (chest, shoulder, waist dims)",
              "`categories` (T-Shirts, Shirts, Hoodies)",
              "`admins` (Credentials & Inventory Role)"],
             c_emerald, c_emerald, "DATABASE")

    # ================= CLEAN ORTHOGONAL CONNECTORS / ARROWS =================
    def draw_arrow(x1, y1, x2, y2, color, label="", offset_label_y=1.2, offset_label_x=0):
        ax.annotate('', xy=(x2, y2), xytext=(x1, y1),
                    arrowprops=dict(arrowstyle="-|>", color=color, lw=1.8,
                                    mutation_scale=14, shrinkA=2, shrinkB=2))
        if label:
            mx = (x1 + x2) / 2 + offset_label_x
            my = (y1 + y2) / 2 + offset_label_y
            ax.text(mx, my, label, fontsize=7.5, weight='bold', color=color, ha='center', va='center',
                    bbox=dict(boxstyle="round,pad=0.22", facecolor='#070b14', edgecolor=color, alpha=0.95, lw=0.9))

    # Horizontal 1: Storefront -> MediaPipe
    draw_arrow(51, 61.5, 57, 61.5, c_cyan, "WebCam Video Stream")

    # Horizontal 2: MediaPipe -> Virtual Try-On
    draw_arrow(103, 61.5, 109, 61.5, c_emerald, "33 Pose Landmarks")

    # Vertical 1: MediaPipe AI -> REST API Controllers
    draw_arrow(80, 49, 80, 37, c_cyan, "Pose Parameters", offset_label_x=7.5, offset_label_y=0)

    # Horizontal 3: REST API Controllers -> Size Engine (Left)
    draw_arrow(57, 24.5, 51, 24.5, c_amber, "Calculate Size & Price")

    # Vertical 2: Size & Price Engine -> Client Storefront (Up)
    draw_arrow(29, 37, 29, 49, c_amber, "Recommended Fit & Ranked Products", offset_label_x=12.5, offset_label_y=0)

    # Horizontal 4: REST API Controllers <-> MySQL DB (Right)
    draw_arrow(103, 26, 109, 26, c_emerald, "SQL Queries")
    draw_arrow(109, 22.5, 103, 22.5, c_emerald, "Data Records")

    # Vertical 3: Database / Assets -> Virtual Try-On Canvas (Up)
    draw_arrow(131, 37, 131, 49, c_emerald, "Garment PNG Textures", offset_label_x=8.5, offset_label_y=0)

    # Bottom status / tech stack bar
    footer_text = "Technology Stack: Google MediaPipe (WASM) • JavaScript ES6 / Canvas 2D • PHP 8 (REST) • MySQL / MariaDB • Bootstrap 5 • Apache (XAMPP)"
    ax.text(80, 4.8, footer_text, fontsize=9, color='#64748b', ha='center', fontfamily='sans-serif')

    plt.tight_layout()
    os.makedirs(r"C:\xampp\htdocs\aicloth\assets", exist_ok=True)
    output_path = r"C:\xampp\htdocs\aicloth\assets\SmartFit_Architecture_Diagram.png"
    desktop_path = r"C:\Users\MSI\Desktop\SmartFit_Architecture_Diagram.png"
    downloads_path = r"C:\Users\MSI\Downloads\SmartFit_Architecture_Diagram.png"
    
    fig.savefig(output_path, facecolor=fig.get_facecolor(), edgecolor='none', bbox_inches='tight')
    fig.savefig(desktop_path, facecolor=fig.get_facecolor(), edgecolor='none', bbox_inches='tight')
    fig.savefig(downloads_path, facecolor=fig.get_facecolor(), edgecolor='none', bbox_inches='tight')
    plt.close(fig)
    print("Architecture diagram created successfully at Desktop and Downloads!")

if __name__ == "__main__":
    create_architecture_diagram()
