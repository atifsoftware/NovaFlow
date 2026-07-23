<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Admin Panel — NovaFlow') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/theme.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Hind Siliguri','Inter',sans-serif; background: #f0f3f0; color: #1a1a2e; }

        /* ===== CUSTOM SCROLLBAR (Desktop & Webkit) ===== */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.02);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(46, 125, 50, 0.2);
            border-radius: 10px;
            transition: all 0.3s;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(46, 125, 50, 0.5);
        }

        /* Sidebar Specific: Hidden by default, shows on hover */
        .adm-sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }
        .adm-sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .adm-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .adm-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        .adm-sidebar:hover::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
        }

        /* ===== SIDEBAR ===== */
        .adm-sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            z-index: 200;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform .3s ease;
        }

        .adm-brand {
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            flex-shrink: 0;
            background: #2b3a4a; /* Slightly different shade for header area */
        }
        .adm-brand a { text-decoration: none; }
        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .brand-logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #e65100, #ff6d00);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
        }
        .brand-text { line-height: 1.2; text-align: center; }
        .brand-name { font-size: 16px; font-weight: 700; color: white; font-family: 'Inter',sans-serif; }
        .brand-name span { color: #ffcc02; }

        /* User Profile (Top Aligned) */
        .sidebar-profile {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            background: rgba(0,0,0,0.1);
        }
        .profile-avatar-wrapper {
            position: relative;
            flex-shrink: 0;
        }
        .profile-avatar {
            width: 45px; height: 45px;
            border-radius: 50%;
            background: #4a5a6a;
            border: 2px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            font-weight: 700;
        }
        .status-dot {
            position: absolute;
            bottom: 2px; right: 2px;
            width: 12px; height: 12px;
            background: #2eb85c;
            border: 2px solid #323e48;
            border-radius: 50%;
        }
        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
            text-align: left;
        }
        .profile-name {
            font-size: 14px;
            font-weight: 700;
            color: white;
        }
        .profile-status {
            font-size: 11px;
            color: rgba(255,255,255,0.6);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Nav Sections & Collapsible */
        .adm-nav { flex: 1; padding: 0; }
        .nav-section { border-bottom: 1px solid rgba(255,255,255,0.03); }
        .nav-link-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all .2s;
            cursor: pointer;
            position: relative;
        }
        .nav-link-item .nav-icon {
            width: 20px;
            font-size: 14px;
            text-align: center;
            flex-shrink: 0;
            transition: all .2s;
        }
        .nav-link-item:hover {
            color: white;
            background: rgba(255,255,255,0.05);
        }
        .nav-link-item.active {
            color: white;
            background: rgba(255,255,255,0.08);
        }
        .nav-link-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            background: #00bcd4; /* Light blue / Cyan bar */
        }
        .submenu-arrow {
            margin-left: auto;
            font-size: 10px;
            transition: transform 0.3s;
            opacity: 0.5;
        }
        .nav-section.expanded .submenu-arrow {
            transform: rotate(-90deg);
        }

        /* Submenu */
        .nav-submenu {
            background: rgba(0,0,0,0.2);
            display: block;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-out;
        }
        .nav-section.expanded .nav-submenu {
            max-height: 500px; /* Large enough to show content */
        }
        .nav-sub-item {
            padding: 10px 20px 10px 52px;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: all .2s;
        }
        .nav-sub-item:hover {
            color: white;
            background: rgba(255,255,255,0.03);
        }
        .nav-sub-item.active {
            color: #ffcc02;
            font-weight: 600;
        }
        .nav-sub-item .sub-icon {
            font-size: 12px;
            width: 14px;
        }
        .nav-badge {
            margin-left: auto;
            background: #ff6f00;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            min-width: 20px;
            text-align: center;
        }
        .nav-divider { border: none; border-top: 1px solid rgba(255,255,255,.07); margin: 6px 16px; }

        /* Bottom user section */
        /* Sidebar Logout Footer */
        .sidebar-footer-actions {
            padding: 10px 20px;
            border-top: 1px solid rgba(255,255,255,.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-action-link {
            color: rgba(255,255,255,0.4);
            font-size: 14px;
            text-decoration: none;
            transition: color .2s;
        }
        .footer-action-link:hover {
            color: #ef5350;
        }

        /* ===== TOPBAR ===== */
        .adm-topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--topbar-h);
            background: white;
            border-bottom: 1px solid #e8ede8;
            z-index: 100;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }
        .topbar-toggle {
            display: none;
            background: none;
            border: 1px solid #e0e7e0;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #666;
            font-size: 16px;
        }
        .breadcrumb-adm {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #888;
            flex: 1;
        }
        .breadcrumb-adm strong { color: #333; }
        .topbar-actions { display: flex; align-items: center; gap: 10px; }
        .topbar-btn {
            position: relative;
            background: #f5f8f5;
            border: 1px solid #e0e7e0;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            cursor: pointer;
            text-decoration: none;
            font-size: 15px;
            transition: all .2s;
        }
        .topbar-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }
        .topbar-dot {
            position: absolute;
            top: 5px; right: 5px;
            width: 8px; height: 8px;
            background: #ff6f00;
            border-radius: 50%;
            border: 2px solid white;
        }
        .topbar-divider { width: 1px; height: 28px; background: #e0e7e0; }
        .topbar-site-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: #fff3e0;
            color: #e65100;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }
        .topbar-site-link:hover { background: #e65100; color: white; }

        /* ===== CONTENT ===== */
        .adm-content {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }
        .adm-page { padding: 24px; }
        .adm-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .adm-page-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a0a00;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .adm-page-title .title-icon {
            width: 40px;
            height: 40px;
            background: #fff3e0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #e65100;
        }

        /* Flash in content */
        .adm-flash { margin-bottom: 16px; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .adm-sidebar { transform: translateX(-100%); }
            .adm-sidebar.open { transform: translateX(0); box-shadow: 4px 0 30px rgba(0,0,0,.3); }
            .adm-topbar { left: 0; }
            .adm-content { margin-left: 0; }
            .topbar-toggle { display: flex; }
            .adm-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 199;
                display: none;
            }
            .adm-overlay.open { display: block; }
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay (Mobile) -->
<div class="adm-overlay" id="admOverlay" onclick="closeSidebar()"></div>

<!-- ===== SIDEBAR ===== -->
<!-- ===== SIDEBAR ===== -->
<aside class="adm-sidebar" id="admSidebar">
    <div class="adm-brand">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="text-decoration-none">
            <div class="brand-logo">
                <div class="brand-logo-icon"><i class="fas fa-store"></i></div>
                <div class="brand-text">
                    <div class="brand-name">Nova<span>Flow</span></div>
                </div>
            </div>
        </a>
    </div>

    <!-- User Profile (Top) -->
    <div class="sidebar-profile">
        <div class="profile-avatar-wrapper">
            <div class="profile-avatar">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="status-dot"></div>
        </div>
        <div class="profile-details">
            <div class="profile-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></div>
            <div class="profile-status">
                <i class="fas fa-circle" style="font-size:6px; color:#2eb85c;"></i> Online
            </div>
        </div>
    </div>

    <nav class="adm-nav">
        <!-- হোম -->
        <div class="nav-section">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="nav-link-item <?= str_contains($_SERVER['REQUEST_URI']??'', 'dashboard') ? 'active' : '' ?>">
                <div class="nav-icon"><i class="fas fa-th-large"></i></div>
                হোম
            </a>
            <a href="<?= BASE_URL ?>/api/docs" class="nav-link-item" target="_blank">
                <div class="nav-icon"><i class="fas fa-code"></i></div>
                API এক্সপ্লোরার
            </a>
        </div>

        <!-- পণ্য ব্যবস্থাপনা -->
        <?php 
            $isCatalogExpanded = str_contains($_SERVER['REQUEST_URI']??'', '/admin/products') || 
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/categories') || 
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/brands') ||
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/banners');
        ?>
        <div class="nav-section <?= $isCatalogExpanded ? 'expanded' : '' ?>">
            <div class="nav-link-item submenu-toggle">
                <div class="nav-icon"><i class="fas fa-box"></i></div>
                পণ্য ব্যবস্থাপনা
                <i class="fas fa-angle-left submenu-arrow"></i>
            </div>
            <div class="nav-submenu">
                <a href="<?= BASE_URL ?>/admin/products" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/products') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-shopping-basket"></i></div> সকল পণ্য
                </a>
                <a href="<?= BASE_URL ?>/admin/categories" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/categories') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-layer-group"></i></div> ক্যাটাগরি
                </a>
                <a href="<?= BASE_URL ?>/admin/brands" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/brands') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-tags"></i></div> ব্র্যান্ড
                </a>
                <a href="<?= BASE_URL ?>/admin/banners" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/banners') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-images"></i></div> ব্যানার
                </a>
            </div>
        </div>

        <!-- ইনভেন্টরি ও ক্রয় -->
        <?php 
            $isInventoryExpanded = str_contains($_SERVER['REQUEST_URI']??'', '/admin/suppliers') || 
                                 str_contains($_SERVER['REQUEST_URI']??'', '/admin/purchases');
        ?>
        <div class="nav-section <?= $isInventoryExpanded ? 'expanded' : '' ?>">
            <div class="nav-link-item submenu-toggle">
                <div class="nav-icon"><i class="fas fa-warehouse"></i></div>
                ইনভেন্টরি ও ক্রয়
                <i class="fas fa-angle-left submenu-arrow"></i>
            </div>
            <div class="nav-submenu">
                <a href="<?= BASE_URL ?>/admin/suppliers" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/suppliers') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-user-tie"></i></div> সরবরাহকারী
                </a>
                <a href="<?= BASE_URL ?>/admin/purchases" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/purchases') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-file-invoice"></i></div> ক্রয় ভাউচার
                </a>
                <a href="<?= BASE_URL ?>/admin/purchases/create" class="nav-sub-item">
                    <div class="sub-icon"><i class="fas fa-plus"></i></div> নতুন ক্রয়
                </a>
            </div>
        </div>

        <!-- বিক্রয় ও অর্ডার -->
        <?php 
            $isSalesExpanded = str_contains($_SERVER['REQUEST_URI']??'', '/admin/orders') ||
                             str_contains($_SERVER['REQUEST_URI']??'', '/admin/coupons');
        ?>
        <div class="nav-section <?= $isSalesExpanded ? 'expanded' : '' ?>">
            <div class="nav-link-item submenu-toggle">
                <div class="nav-icon"><i class="fas fa-shopping-cart"></i></div>
                বিক্রয় ও অর্ডার
                <i class="fas fa-angle-left submenu-arrow"></i>
            </div>
            <div class="nav-submenu">
                <a href="<?= BASE_URL ?>/admin/orders" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/orders') && !isset($_GET['status']) ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-list"></i></div> সকল অর্ডার
                </a>
                <a href="<?= BASE_URL ?>/admin/orders?status=pending" class="nav-sub-item <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-clock"></i></div> অপেক্ষমান
                </a>
                <a href="<?= BASE_URL ?>/admin/orders?status=processing" class="nav-sub-item <?= isset($_GET['status']) && $_GET['status'] == 'processing' ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-cog"></i></div> প্রক্রিয়াধীন
                </a>
                <a href="<?= BASE_URL ?>/admin/coupons" class="nav-sub-item">
                    <div class="sub-icon"><i class="fas fa-ticket-alt"></i></div> কুপন কোড
                </a>
            </div>
        </div>

        <!-- অ্যাকাউন্টস ও রিপোর্ট -->
        <?php 
            $isAccountExpanded = str_contains($_SERVER['REQUEST_URI']??'', '/admin/expenses') || 
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/reports');
        ?>
        <div class="nav-section <?= $isAccountExpanded ? 'expanded' : '' ?>">
            <div class="nav-link-item submenu-toggle">
                <div class="nav-icon"><i class="fas fa-calculator"></i></div>
                অ্যাকাউন্টস ও রিপোর্ট
                <i class="fas fa-angle-left submenu-arrow"></i>
            </div>
            <div class="nav-submenu">
                <a href="<?= BASE_URL ?>/admin/expenses" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/expenses') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-minus-circle"></i></div> ব্যবসায়িক খরচ
                </a>
                <a href="<?= BASE_URL ?>/admin/reports/accounting" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/reports/accounting') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-file-invoice-dollar"></i></div> আর্থিক রিপোর্ট
                </a>
                <a href="<?= BASE_URL ?>/admin/reports/stock" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/reports/stock') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-boxes"></i></div> স্টক রিপোর্ট
                </a>
                <a href="<?= BASE_URL ?>/admin/reports/sales" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/reports/sales') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-chart-line"></i></div> বিক্রয় রিপোর্ট
                </a>
            </div>
        </div>

        <!-- গ্রাহক ও সাপোর্ট -->
        <?php 
            $isSupportExpanded = str_contains($_SERVER['REQUEST_URI']??'', '/admin/customers') || 
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/reviews') ||
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/testimonials') ||
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/pages') ||
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/faqs') ||
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/contacts') ||
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/qa');
        ?>
        <div class="nav-section <?= $isSupportExpanded ? 'expanded' : '' ?>">
            <div class="nav-link-item submenu-toggle">
                <div class="nav-icon"><i class="fas fa-users-cog"></i></div>
                সাপোর্ট ও সিএমএস
                <i class="fas fa-angle-left submenu-arrow"></i>
            </div>
            <div class="nav-submenu">
                <a href="<?= BASE_URL ?>/admin/customers" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/customers') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-users"></i></div> গ্রাহক তালিকা
                </a>
                <a href="<?= BASE_URL ?>/admin/reviews" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/reviews') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-star"></i></div> রিভিউ অনুমোদন
                </a>
                <a href="<?= BASE_URL ?>/admin/testimonials" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/testimonials') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-comment-dots"></i></div> টেস্টমোনিয়াল
                </a>
                <a href="<?= BASE_URL ?>/admin/pages" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/pages') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-file-alt"></i></div> পেইজসমূহ
                </a>
                <a href="<?= BASE_URL ?>/admin/qa" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/qa') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-comments"></i></div> প্রশ্ন-উত্তর
                </a>
            </div>
        </div>

        <!-- আউটলেট ব্যবস্থাপনা -->
        <?php 
            $isOutletExpanded = str_contains($_SERVER['REQUEST_URI']??'', '/admin/outlets') || 
                               str_contains($_SERVER['REQUEST_URI']??'', '/admin/pos');
        ?>
        <div class="nav-section <?= $isOutletExpanded ? 'expanded' : '' ?>">
            <div class="nav-link-item submenu-toggle">
                <div class="nav-icon"><i class="fas fa-store-alt"></i></div>
                আউটলেট ব্যবস্থাপনা
                <i class="fas fa-angle-left submenu-arrow"></i>
            </div>
            <div class="nav-submenu">
                <a href="<?= BASE_URL ?>/admin/pos" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/pos') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-desktop"></i></div> পিওএস (POS)
                </a>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>/admin/outlets" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/outlets') && !str_contains($_SERVER['REQUEST_URI']??'', 'dashboard') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-list"></i></div> আউটলেট তালিকা
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/admin/outlets/dashboard" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/outlets/dashboard') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-chart-pie"></i></div> আউটলেট ড্যাশবোর্ড
                </a>
                <a href="<?= BASE_URL ?>/admin/requisitions" class="nav-sub-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/requisitions') ? 'active' : '' ?>">
                    <div class="sub-icon"><i class="fas fa-file-export"></i></div> স্টক চাহিদা (Requisition)
                </a>
            </div>
        </div>

        <!-- ব্যবহারকারী হইসেস -->
        <div class="nav-section">
            <a href="<?= BASE_URL ?>/admin/users" class="nav-link-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/users') ? 'active' : '' ?>">
                <div class="nav-icon"><i class="fas fa-users"></i></div>
                ব্যবহারকারী হইসেস
            </a>
        </div>

        <!-- সেটিংস -->
        <div class="nav-section">
            <a href="<?= BASE_URL ?>/admin/settings" class="nav-link-item <?= str_contains($_SERVER['REQUEST_URI']??'', '/admin/settings') ? 'active' : '' ?>">
                <div class="nav-icon"><i class="fas fa-cog"></i></div>
                সাইট সেটিংস
            </a>
        </div>
    </nav>

    <!-- Sidebar Footer Actions -->
    <div class="sidebar-footer-actions">
        <a href="<?= BASE_URL ?>/logout" class="footer-action-link" title="লগআউট">
            <i class="fas fa-power-off"></i> লগআউট
        </a>
    </div>
</aside>

<!-- ===== TOPBAR ===== -->
<div class="adm-topbar">
    <button class="topbar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="breadcrumb-adm">
        <a href="<?= BASE_URL ?>/admin/dashboard" style="color:#888;text-decoration:none;">Admin</a>
        <i class="fas fa-chevron-right" style="font-size:10px;"></i>
        <strong><?= htmlspecialchars(explode(' — ', $title ?? 'ড্যাশবোর্ড')[0]) ?></strong>
    </div>

    <div class="topbar-actions">
        <a href="<?= BASE_URL ?>/admin/orders?status=pending" class="topbar-btn" title="অপেক্ষমান অর্ডার">
            <i class="fas fa-bell"></i>
            <span class="topbar-dot"></span>
        </a>
        <div class="topbar-divider"></div>
        <a href="<?= BASE_URL ?>/" class="topbar-site-link" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span class="d-none d-md-inline">সাইট দেখুন</span>
        </a>
    </div>
</div>

<!-- ===== CONTENT ===== -->
<div class="adm-content">
    <div class="adm-page">
        <!-- Flash Messages -->
        <div class="adm-flash">
            <?php flash('success'); flash('error'); ?>
        </div>
        <?= $content ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= csrf_token() ?>';

// Sidebar toggle
function toggleSidebar() {
    document.getElementById('admSidebar').classList.toggle('open');
    document.getElementById('admOverlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('admSidebar').classList.remove('open');
    document.getElementById('admOverlay').classList.remove('open');
}

// Submenu Toggle logic
$(document).ready(function() {
    $('.submenu-toggle').on('click', function() {
        const section = $(this).closest('.nav-section');
        const submenu = section.find('.nav-submenu');
        
        // Toggle Expanded state
        section.toggleClass('expanded');
        
        // If your theme requires close others:
        // $('.nav-section').not(section).removeClass('expanded');
    });
});

// Confirm dialogs
document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
        if (!confirm(btn.dataset.confirm || 'নিশ্চিত করুন?')) e.preventDefault();
    });
});

// Toast
function showToast(msg, type = 'success') {
    const d = document.createElement('div');
    d.style.cssText = `position:fixed;bottom:20px;right:20px;z-index:9999;background:${type==='success'?'var(--primary)':'#c62828'};color:white;padding:12px 18px;border-radius:8px;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,.25);max-width:300px;`;
    d.textContent = msg;
    document.body.appendChild(d);
    setTimeout(() => d.remove(), 3500);
}
</script>
</body>
</html>
