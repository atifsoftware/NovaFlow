<!DOCTYPE html>
<html lang="<?= current_lang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'NovaFlow v2.1.0 — Enterprise-Grade Modern PHP MVC Framework' ?></title>
    <meta name="description" content="NovaFlow is an ultra-fast, enterprise-grade PHP 8.2 MVC framework with Multi-DB Grammars, Modular ERP Architecture, Route Caching, and Bank-Grade Security.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --nf-primary: #ef4444;
            --nf-primary-glow: rgba(239, 68, 68, 0.35);
            --nf-accent: #06b6d4;
            --nf-accent-glow: rgba(6, 182, 212, 0.3);
            --nf-dark-bg: #0b0f19;
            --nf-dark-card: #111827;
            --nf-dark-border: rgba(255, 255, 255, 0.08);
            --nf-text-muted: #94a3b8;
        }

        body {
            font-family: <?= current_lang() === 'bn' ? "'Hind Siliguri', 'Plus Jakarta Sans', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
            background-color: #0b0f19;
            color: #f1f5f9;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Glassmorphism Navbar */
        .nf-navbar {
            background: rgba(11, 15, 25, 0.82);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--nf-dark-border);
            transition: all 0.3s ease;
        }

        .nf-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: 1.5rem;
            color: #ffffff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nf-brand span.highlight {
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nf-nav-link {
            color: #94a3b8 !important;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 8px 16px !important;
            border-radius: 8px;
            transition: all 0.25s ease;
            text-decoration: none;
        }

        .nf-nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.05);
        }

        .btn-glow-primary {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff !important;
            border: none;
            box-shadow: 0 4px 18px var(--nf-primary-glow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-glow-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.55);
            color: #ffffff !important;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0 !important;
            border: 1px solid var(--nf-dark-border);
            backdrop-filter: blur(8px);
            transition: all 0.25s ease;
        }

        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .lang-switch {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--nf-dark-border);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.85rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .lang-switch:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* Ultra-Modern Dark Footer */
        .nf-footer {
            background: linear-gradient(180deg, rgba(13, 17, 28, 0.95) 0%, #060911 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            color: #94a3b8;
        }
        .nf-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 10%;
            right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(239, 68, 68, 0.5), rgba(6, 182, 212, 0.5), transparent);
        }
        .nf-footer-link {
            color: #94a3b8 !important;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nf-footer-link:hover {
            color: #ffffff !important;
            transform: translateY(-2px);
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.4);
        }
        .nf-footer-desc {
            color: #94a3b8;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .nf-footer-subtext {
            color: #64748b;
            font-size: 0.84rem;
        }
        .nf-footer-badge {
            background: rgba(34, 197, 94, 0.12);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.25);
            border-radius: 50rem;
            padding: 4px 12px;
            font-size: 0.78rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nf-pulse-dot {
            width: 7px;
            height: 7px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 8px #22c55e;
            display: inline-block;
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.85); }
        }
        .nf-footer-tech {
            color: #64748b;
            font-size: 0.82rem;
            font-family: 'Fira Code', monospace;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>

    <!-- Top Sticky Glass Navbar -->
    <nav class="navbar navbar-expand-lg nf-navbar sticky-top py-3">
        <div class="container">
            <a class="nf-brand" href="<?= BASE_URL ?>/">
                <div class="d-inline-flex align-items-center justify-content-center bg-danger rounded-3" style="width: 34px; height: 34px; box-shadow: 0 2px 10px rgba(239, 68, 68, 0.5);">
                    <i class="fas fa-bolt text-white fs-6"></i>
                </div>
                Nova<span class="highlight">Flow</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">v2.1.0</span>
            </a>

            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <li class="nav-item">
                        <a class="nf-nav-link" href="<?= BASE_URL ?>/"><i class="fas fa-home me-1"></i> <?= __('home', 'হোম') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nf-nav-link" href="<?= BASE_URL ?>/docs"><i class="fas fa-book me-1"></i> <?= __('docs', 'ডকুমেন্টেশন') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nf-nav-link" href="<?= BASE_URL ?>/api/docs"><i class="fas fa-code me-1"></i> API Docs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nf-nav-link" href="<?= BASE_URL ?>/#benchmarks"><i class="fas fa-gauge-high me-1 text-warning"></i> বেঞ্চমার্ক</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <?php if (current_lang() === 'bn'): ?>
                            <a href="?lang=en" class="lang-switch"><i class="fas fa-globe me-1"></i> English</a>
                        <?php else: ?>
                            <a href="?lang=bn" class="lang-switch"><i class="fas fa-globe me-1"></i> বাংলা</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="https://github.com/atifsoftware/NovaFlow" target="_blank" class="btn btn-glass btn-sm rounded-pill px-3 py-2">
                            <i class="fab fa-github me-1"></i> GitHub
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-glow-primary rounded-pill px-4 py-2 fw-bold text-uppercase" style="font-size: 0.88rem; letter-spacing: 0.5px;">
                            <i class="fas fa-arrow-right-to-bracket me-1"></i> <?= __('login', 'লগইন') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Injection -->
    <main>
        <?= $content ?>
    </main>

    <!-- Sleek Modern Footer -->
    <footer class="nf-footer py-5 mt-5">
        <div class="container">
            <div class="row g-4 align-items-center pb-4" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                <div class="col-md-6 text-center text-md-start">
                    <a class="nf-brand mb-2" href="<?= BASE_URL ?>/">
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger rounded-3" style="width: 30px; height: 30px; box-shadow: 0 2px 10px rgba(239, 68, 68, 0.4);">
                            <i class="fas fa-bolt text-white fs-6"></i>
                        </div>
                        Nova<span class="highlight">Flow</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">v2.1.0</span>
                    </a>
                    <p class="nf-footer-desc mb-0" style="max-width: 440px;">
                        Enterprise-Grade Modern PHP 8.2 MVC Framework. Built for speed, high scalability, and clean modular ERP development.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-inline-flex flex-wrap justify-content-center justify-content-md-end gap-3 gap-lg-4">
                        <a href="<?= BASE_URL ?>/" class="nf-footer-link">
                            <i class="fas fa-home text-muted"></i> <?= __('home', 'হোম') ?>
                        </a>
                        <a href="<?= BASE_URL ?>/docs" class="nf-footer-link">
                            <i class="fas fa-book text-danger"></i> <?= __('docs', 'ডকুমেন্টেশন') ?>
                        </a>
                        <a href="<?= BASE_URL ?>/api/docs" class="nf-footer-link">
                            <i class="fas fa-code text-info"></i> API Explorer
                        </a>
                        <a href="<?= BASE_URL ?>/#benchmarks" class="nf-footer-link">
                            <i class="fas fa-gauge-high text-warning"></i> বেঞ্চমার্ক
                        </a>
                        <a href="https://github.com/atifsoftware/NovaFlow" target="_blank" class="nf-footer-link">
                            <i class="fab fa-github text-white"></i> GitHub
                        </a>
                    </div>
                </div>
            </div>
            <div class="row pt-4 align-items-center g-3">
                <div class="col-md-6 text-center text-md-start nf-footer-subtext">
                    &copy; <?= date('Y') ?> <strong class="text-white">NovaFlow Framework</strong>. Released as open-source under <span class="text-secondary">MIT License</span>.
                </div>
                <div class="col-md-6 text-center text-md-end d-flex flex-wrap justify-content-center justify-content-md-end align-items-center gap-3">
                    <span class="nf-footer-badge">
                        <span class="nf-pulse-dot"></span> System Healthy
                    </span>
                    <span class="nf-footer-tech">
                        <i class="fas fa-microchip text-danger"></i> PHP 8.2+ • MySQL • Postgres • SQLite
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
