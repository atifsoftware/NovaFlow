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

        /* Modern Footer */
        .nf-footer {
            background: #080b12;
            border-top: 1px solid var(--nf-dark-border);
            color: var(--nf-text-muted);
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
                        <a class="nf-nav-link" href="#benchmarks"><i class="fas fa-gauge-high me-1 text-warning"></i> বেঞ্চমার্ক</a>
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
            <div class="row g-4 align-items-center pb-4 border-bottom border-secondary border-opacity-25">
                <div class="col-md-6 text-center text-md-start">
                    <a class="nf-brand mb-2" href="<?= BASE_URL ?>/">
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger rounded-3" style="width: 28px; height: 28px;">
                            <i class="fas fa-bolt text-white fs-6"></i>
                        </div>
                        Nova<span class="highlight">Flow</span>
                    </a>
                    <p class="small text-muted mb-0" style="max-width: 420px;">
                        Enterprise-Grade Modern PHP 8.2 MVC Framework. Built for speed, high scalability, and clean modular ERP development.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-inline-flex gap-3">
                        <a href="<?= BASE_URL ?>/docs" class="text-secondary text-decoration-none small hover-white">Documentation</a>
                        <span class="text-secondary opacity-50">•</span>
                        <a href="<?= BASE_URL ?>/api/docs" class="text-secondary text-decoration-none small hover-white">API Explorer</a>
                        <span class="text-secondary opacity-50">•</span>
                        <a href="https://github.com/atifsoftware/NovaFlow" target="_blank" class="text-secondary text-decoration-none small hover-white">GitHub Repo</a>
                    </div>
                </div>
            </div>
            <div class="row pt-4 align-items-center">
                <div class="col-md-6 text-center text-md-start small text-secondary">
                    &copy; <?= date('Y') ?> NovaFlow Framework. Open source under MIT License.
                </div>
                <div class="col-md-6 text-center text-md-end small text-secondary">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 me-2">
                        <i class="fas fa-circle-check me-1"></i> System Healthy
                    </span>
                    <span>PHP 8.2+ • MySQL • Postgres • SQLite</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
