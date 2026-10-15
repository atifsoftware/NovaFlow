<?php
/**
 * NovaFlow v2.1.0 Documentation View
 * Full Dark Mode & High-Aesthetic Glassmorphism Interface
 */
$currentLang = current_lang();
?>

<style>
    /* Custom Scrollbar for Docs Sidebar */
    .docs-nav::-webkit-scrollbar {
        width: 4px;
    }
    .docs-nav::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
    }
    .docs-nav::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 4px;
    }
    .docs-nav::-webkit-scrollbar-thumb:hover {
        background: rgba(239, 68, 68, 0.4);
    }

    /* Sticky Sidebar Navigation */
    .docs-sidebar-wrapper {
        position: sticky;
        top: 90px;
        max-height: calc(100vh - 110px);
        overflow-y: auto;
        background: rgba(17, 24, 39, 0.7);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 22px 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .docs-section-heading {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #64748b;
        font-weight: 700;
        margin-top: 1.4rem;
        margin-bottom: 0.5rem;
        padding-left: 12px;
    }
    .docs-section-heading:first-child {
        margin-top: 0;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 10px;
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
        transition: all 0.25s ease;
        margin-bottom: 3px;
        border-left: 3px solid transparent;
    }
    .sidebar-link:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.05);
        transform: translateX(3px);
    }
    .sidebar-link.active {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.12);
        font-weight: 600;
        border-left-color: #ef4444;
    }
    .sidebar-badge {
        font-size: 0.65rem;
        padding: 2px 7px;
        border-radius: 6px;
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
        margin-left: auto;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    /* Content Area & Cards */
    .docs-section {
        scroll-margin-top: 100px;
        margin-bottom: 48px;
    }

    .doc-card {
        background: rgba(17, 24, 39, 0.65);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        padding: 32px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    .doc-card:hover {
        border-color: rgba(239, 68, 68, 0.3);
        box-shadow: 0 14px 40px rgba(0, 0, 0, 0.45);
    }

    .doc-title {
        font-size: 1.65rem;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .doc-lead {
        color: #94a3b8;
        font-size: 1.05rem;
        line-height: 1.65;
        margin-bottom: 24px;
    }

    /* Code Terminal Display */
    .code-terminal {
        background: #0d1117;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        overflow: hidden;
        margin: 20px 0;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
    }
    .code-header {
        background: #161b22;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }
    .code-dots {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .code-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .dot-red { background: #ff5f56; }
    .dot-yellow { background: #ffbd2e; }
    .dot-green { background: #27c93f; }
    
    .code-title {
        font-family: 'Fira Code', monospace;
        font-size: 0.78rem;
        color: #8b949e;
        font-weight: 500;
    }

    .copy-btn {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #cbd5e1;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .copy-btn:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.25);
    }

    pre.code-body {
        margin: 0;
        padding: 20px 22px;
        background: transparent;
        border: none;
        color: #e6edf3;
        font-family: 'Fira Code', monospace;
        font-size: 0.88rem;
        line-height: 1.65;
        overflow-x: auto;
    }
    code {
        font-family: 'Fira Code', monospace;
    }
    :not(pre) > code {
        background: rgba(255, 255, 255, 0.08);
        color: #f87171;
        padding: 2px 7px;
        border-radius: 6px;
        font-size: 0.88em;
        border: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* Dark Mode Tables */
    .table-dark-custom {
        width: 100%;
        background: rgba(13, 17, 23, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        overflow: hidden;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table-dark-custom th {
        background: rgba(255, 255, 255, 0.04);
        color: #94a3b8;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 14px 18px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .table-dark-custom td {
        padding: 14px 18px;
        color: #cbd5e1;
        font-size: 0.9rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .table-dark-custom tr:last-child td {
        border-bottom: none;
    }
    .table-dark-custom tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }

    /* Sub-cards within sections */
    .mini-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 12px;
        padding: 18px;
        height: 100%;
        transition: all 0.25s ease;
    }
    .mini-card:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.12);
        transform: translateY(-2px);
    }

    /* Language Switcher */
    .lang-btn {
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.25s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .lang-btn.active {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
    }
    .lang-btn.inactive {
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
        border-color: rgba(255, 255, 255, 0.1);
    }
    .lang-btn.inactive:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    /* Search input inside sidebar */
    .docs-search {
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #f1f5f9;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.85rem;
        width: 100%;
        margin-bottom: 16px;
        transition: all 0.2s ease;
    }
    .docs-search:focus {
        outline: none;
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
    }

    /* Language display toggle */
    .lang-en { display: <?= $currentLang === 'bn' ? 'none' : 'block' ?>; }
    .lang-bn { display: <?= $currentLang === 'bn' ? 'block' : 'none' ?>; }
</style>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 col-md-4 d-none d-md-block">
            <div class="docs-sidebar-wrapper docs-nav">
                <!-- Search Filter -->
                <div class="position-relative">
                    <input type="text" id="docsSearch" class="docs-search" placeholder="<?= $currentLang === 'bn' ? 'টপিক খুঁজুন...' : 'Search docs...' ?>" onkeyup="filterDocsNav()">
                </div>

                <div class="docs-section-heading">Getting Started / শুরু করুন</div>
                <a href="#intro" class="sidebar-link active">
                    <i class="fas fa-bolt text-danger"></i>
                    <span>Introduction / ভূমিকা</span>
                </a>
                <a href="#benchmarks" class="sidebar-link">
                    <i class="fas fa-gauge-high text-info"></i>
                    <span>Benchmarks / পারফরম্যান্স</span>
                    <span class="sidebar-badge">0.069ms</span>
                </a>
                <a href="#install" class="sidebar-link">
                    <i class="fas fa-download text-warning"></i>
                    <span>Installation / সেটআপ</span>
                </a>

                <div class="docs-section-heading">Routing Architecture (v2.1.0)</div>
                <a href="#modular-routing" class="sidebar-link">
                    <i class="fas fa-cubes text-danger"></i>
                    <span>Modular ERP / মডুলার রাউট</span>
                    <span class="sidebar-badge">NEW</span>
                </a>
                <a href="#resource-routing" class="sidebar-link">
                    <i class="fas fa-layer-group text-primary"></i>
                    <span>Resource & Spoofing</span>
                    <span class="sidebar-badge">REST</span>
                </a>
                <a href="#route-caching" class="sidebar-link">
                    <i class="fas fa-forward-fast text-success"></i>
                    <span>Route Caching / ক্যাশিং</span>
                    <span class="sidebar-badge">HIGH SPEED</span>
                </a>

                <div class="docs-section-heading">Core Engine / কোর ইঞ্জিন</div>
                <a href="#querybuilder" class="sidebar-link">
                    <i class="fas fa-database text-primary"></i>
                    <span>Multi-DB & QueryBuilder</span>
                </a>
                <a href="#services-di" class="sidebar-link">
                    <i class="fas fa-plug text-info"></i>
                    <span>DI Container & Services</span>
                </a>
                <a href="#controllers-api" class="sidebar-link">
                    <i class="fas fa-network-wired text-success"></i>
                    <span>Controllers & REST API</span>
                </a>
                <a href="#middleware-sec" class="sidebar-link">
                    <i class="fas fa-shield-halved text-warning"></i>
                    <span>Middleware & Security</span>
                </a>

                <div class="docs-section-heading">Enterprise & Utilities</div>
                <a href="#queues" class="sidebar-link">
                    <i class="fas fa-clock-rotate-left text-danger"></i>
                    <span>Async Background Queues</span>
                </a>
                <a href="#storage" class="sidebar-link">
                    <i class="fas fa-cloud-arrow-up text-info"></i>
                    <span>Cloudinary & S3 Storage</span>
                </a>
                <a href="#cli-tools" class="sidebar-link">
                    <i class="fas fa-terminal text-warning"></i>
                    <span>CLI PowerPack & CRUD</span>
                </a>
                <a href="#testing" class="sidebar-link">
                    <i class="fas fa-vial-circle-check text-success"></i>
                    <span>PHPUnit Suite (207 Tests)</span>
                </a>
            </div>
        </div>

        <!-- Documentation Content -->
        <div class="col-lg-9 col-md-8">
            <!-- Top Bar Header -->
            <div class="doc-card mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3" style="padding: 20px 28px;">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-danger rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">v2.1.0 STABLE</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">PHP 8.2+ READY</span>
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">MODULAR ERP</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-white" style="letter-spacing: -0.5px;">NovaFlow Documentation</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button id="btnEn" class="lang-btn <?= $currentLang === 'en' ? 'active' : 'inactive' ?>" onclick="setLang('en')">
                        <i class="fas fa-globe me-1"></i> English
                    </button>
                    <button id="btnBn" class="lang-btn <?= $currentLang === 'bn' ? 'active' : 'inactive' ?>" onclick="setLang('bn')">
                        <i class="fas fa-language me-1"></i> বাংলা
                    </button>
                </div>
            </div>

            <!-- SECTION 1: Introduction -->
            <section id="intro" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-bolt text-danger"></i> Introduction & Architecture
                        </div>
                        <p class="doc-lead">
                            <strong>NovaFlow v2.1.0</strong> is an enterprise-grade, ultra-fast PHP 8.2 MVC framework crafted specifically for scalable business applications, ERP systems, and RESTful APIs. It combines microsecond performance with rich developer ergonomics.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-bolt text-danger"></i> ভূমিকা ও আর্কিটেকচার (Introduction)
                        </div>
                        <p class="doc-lead">
                            <strong>NovaFlow v2.1.0</strong> হলো এন্টারপ্রাইজ-গ্রেড, উচ্চ ক্ষমতাসম্পন্ন আধুনিক পিএইচপি ৮.২ এমভিসি (MVC) ফ্রেমওয়ার্ক। এটি মূলত বৃহৎ বিজনেস অ্যাপ্লিকেশন, মডুলার ইআরপি (ERP) সিস্টেম এবং হাই-থ্রুপুট রেস্ট এপিআই (REST API) তৈরির জন্য সুচারুভাবে আর্কিটেক্ট করা হয়েছে।
                        </p>
                    </div>

                    <!-- Core Architectural Highlights Grid -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <div class="mini-card">
                                <i class="fas fa-gauge-high text-danger fs-4 mb-2"></i>
                                <h6 class="fw-bold text-white mb-1">Sub-Millisecond Engine</h6>
                                <p class="small text-muted mb-0">Compiled route cache boots in <strong>0.069ms</strong> serving 14,350+ req/sec.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mini-card">
                                <i class="fas fa-cubes text-info fs-4 mb-2"></i>
                                <h6 class="fw-bold text-white mb-1">Modular ERP Routes</h6>
                                <p class="small text-muted mb-0">Isolated modules (Sales, Inventory, Accounts) under <code>app/Modules/</code>.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mini-card">
                                <i class="fas fa-shield-halved text-success fs-4 mb-2"></i>
                                <h6 class="fw-bold text-white mb-1">Bank-Grade Security</h6>
                                <p class="small text-muted mb-0">Strict CSRF, IP rate-limiting, timing-safe crypto, and SQL protection.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 2: Benchmarks -->
            <section id="benchmarks" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-gauge-high text-info"></i> Real-World Performance Benchmarks
                        </div>
                        <p class="doc-lead">
                            Measured on standard developer hardware (AMD Ryzen 7, PHP 8.2 CLI, 10,000 iterations). NovaFlow maintains an ultra-lean memory footprint and near-zero framework overhead.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-gauge-high text-info"></i> লাইভ পারফরম্যান্স ও বেঞ্চমার্ক (Benchmarks)
                        </div>
                        <p class="doc-lead">
                            স্ট্যান্ডার্ড হার্ডওয়্যারে (PHP 8.2 CLI, ১০,০০০ ইটারেশন) রিয়েল-টাইম পরীক্ষিত রেজাল্ট। ফ্রেমওয়ার্কটির মেমোরি খরচ অবিশ্বাস্য রকমের কম এবং রেসপন্স গতি মাইক্রোসেকেন্ড পর্যায়ের।
                        </p>
                    </div>

                    <div class="table-responsive">
                        <table class="table-dark-custom">
                            <thead>
                                <tr>
                                    <th>Benchmark Metric</th>
                                    <th>Result</th>
                                    <th>Throughput Capacity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold"><i class="fas fa-bolt text-warning me-2"></i> Cached Route Dispatch</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">0.069 ms</span></td>
                                    <td><strong>14,354 req/sec</strong></td>
                                    <td><span class="badge bg-success">Ultra Fast</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold"><i class="fas fa-server text-info me-2"></i> Dynamic Route Matching</td>
                                    <td><span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">0.141 ms</span></td>
                                    <td><strong>7,078 req/sec</strong></td>
                                    <td><span class="badge bg-success">Optimal</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold"><i class="fas fa-memory text-danger me-2"></i> Framework Memory Baseline</td>
                                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">6.00 MB</span></td>
                                    <td><strong>Zero Memory Leaks</strong></td>
                                    <td><span class="badge bg-success">Minimal</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold"><i class="fas fa-database text-primary me-2"></i> Query Compilation (PDO)</td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">0.023 ms</span></td>
                                    <td><strong>43,478 queries/sec</strong></td>
                                    <td><span class="badge bg-success">Blazing</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">Terminal &mdash; Run Live Benchmarks</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>php benchmarks/run.php</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: Installation -->
            <section id="install" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-download text-warning"></i> Requirements & Installation
                        </div>
                        <p class="doc-lead">
                            NovaFlow is built on modern PHP standards with zero bloated third-party dependencies in the core routing engine.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-download text-warning"></i> সিস্টেম রিকোয়ারমেন্টস ও ইনস্টলেশন
                        </div>
                        <p class="doc-lead">
                            NovaFlow পিএইচপির আধুনিক স্ট্যান্ডার্ড অনুসরণ করে তৈরি। এতে কোর রাউটিং বা ডিপেন্ডেন্সি ইঞ্জিনে কোনো অপ্রয়োজনীয় ভারী প্যাকেজ নেই।
                        </p>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="mini-card">
                                <h6 class="fw-bold text-white mb-2"><i class="fas fa-check-circle text-success me-2"></i> Server Prerequisites</h6>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li>PHP >= 8.2 (8.2, 8.3 supported)</li>
                                    <li>PDO Extensions: <code>pdo_mysql</code>, <code>pdo_pgsql</code>, or <code>pdo_sqlite</code></li>
                                    <li>Extensions: <code>mbstring</code>, <code>openssl</code>, <code>curl</code>, <code>json</code></li>
                                    <li>Composer 2.x</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mini-card">
                                <h6 class="fw-bold text-white mb-2"><i class="fas fa-server text-info me-2"></i> Web Servers</h6>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li>Apache 2.4+ with <code>mod_rewrite</code> enabled</li>
                                    <li>Nginx with PHP-FPM</li>
                                    <li>PHP Built-in CLI Server (for rapid local testing)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">Terminal &mdash; Setup in 3 Commands</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code># 1. Clone repository
git clone https://github.com/atifsoftware/NovaFlow.git
cd NovaFlow

# 2. Install dependencies & generate autoloaders
composer install

# 3. Setup Environment
cp .env.example .env

# 4. Check system health & migrations
php cli.php 1</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 4: Modular ERP Routing Architecture -->
            <section id="modular-routing" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-cubes text-danger"></i> Modular ERP Routing Architecture (v2.1.0)
                        </div>
                        <p class="doc-lead">
                            NovaFlow v2.1.0 introduces full support for <strong>Modular Architecture</strong>, empowering you to build massive ERP applications without cluttering your global route files.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-cubes text-danger"></i> মডুলার ইআরপি রাউটিং আর্কিটেকচার (v2.1.0)
                        </div>
                        <p class="doc-lead">
                            NovaFlow v2.1.0-এ সম্পূর্ণ <strong>মডুলার আর্কিটেকচার (Modular Architecture)</strong> সাপোর্ট যুক্ত করা হয়েছে। ফলে বৃহৎ ERP বা মাল্টি-মডিউল প্রজেক্টে রাউট ফাইলগুলো আলাদা আলাদা মডিউলে গুছিয়ে রাখা যায়।
                        </p>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="mini-card">
                                <h6 class="fw-bold text-white mb-2"><i class="fas fa-folder-tree text-danger me-2"></i> Dynamic Route Auto-Discovery</h6>
                                <p class="small text-muted mb-0">
                                    Every PHP file in <code>routes/*.php</code> (e.g. <code>web.php</code>, <code>admin.php</code>, <code>api.php</code>, <code>inventory.php</code>) is automatically detected and loaded with <code>web.php</code> prioritized first.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mini-card">
                                <h6 class="fw-bold text-white mb-2"><i class="fas fa-box-open text-info me-2"></i> ERP Module Isolation</h6>
                                <p class="small text-muted mb-0">
                                    Place independent ERP modules in <code>app/Modules/{Module}/routes.php</code>. Each module maintains its own isolated controllers, views, and routes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">routes/web.php &mdash; Registering ERP Modules</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>// Auto-load all active ERP modules in app/Modules/*/routes.php
$router->loadModules();

// Or explicitly mount individual modules with dedicated prefixes
$router->module('Sales');      // Loaded under /sales
$router->module('Inventory');  // Loaded under /inventory
$router->module('Accounts');   // Loaded under /accounts</code></pre>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">app/Modules/Sales/routes.php &mdash; Module-level Routes</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>&lt;?php
/** @var \NovaFlow\Core\Router $router */

$router->group(['prefix' => 'sales', 'middleware' => 'auth'], function($router) {
    // RESTful Resource for Orders & Invoices
    $router->resource('invoices', 'Sales\InvoiceController');
    $router->resource('customers', 'Sales\CustomerController');
    
    $router->get('/analytics', 'Sales\SalesAnalyticsController@index');
});</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 5: Resource Routing & Method Spoofing -->
            <section id="resource-routing" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-layer-group text-primary"></i> RESTful Resource Routing & Method Spoofing
                        </div>
                        <p class="doc-lead">
                            With <code>$router->resource('resource', 'Controller')</code>, NovaFlow automatically wires all 7 standard CRUD endpoints in a single concise declaration.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-layer-group text-primary"></i> রিসোর্স রাউটিং ও মেথড স্পুফিং (Resource Routing)
                        </div>
                        <p class="doc-lead">
                            <code>$router->resource('name', 'Controller')</code> এক লাইনে লিখেই ৭টি স্ট্যান্ডার্ড RESTful CRUD রাউট স্বয়ংক্রিয়ভাবে তৈরি হয়ে যায়।
                        </p>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table-dark-custom">
                            <thead>
                                <tr>
                                    <th>HTTP Verb</th>
                                    <th>URI Path</th>
                                    <th>Controller Action</th>
                                    <th>Route Purpose</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">GET</span></td>
                                    <td><code>/products</code></td>
                                    <td><code>index()</code></td>
                                    <td>Display a listing of products</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">GET</span></td>
                                    <td><code>/products/create</code></td>
                                    <td><code>create()</code></td>
                                    <td>Show product creation form</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary">POST</span></td>
                                    <td><code>/products</code></td>
                                    <td><code>store()</code></td>
                                    <td>Store newly created product in DB</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">GET</span></td>
                                    <td><code>/products/:id</code></td>
                                    <td><code>show($id)</code></td>
                                    <td>Display a specific product detail</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">GET</span></td>
                                    <td><code>/products/:id/edit</code></td>
                                    <td><code>edit($id)</code></td>
                                    <td>Show edit form for product</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning text-dark">PUT / PATCH</span></td>
                                    <td><code>/products/:id</code></td>
                                    <td><code>update($id)</code></td>
                                    <td>Update product in database</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">DELETE</span></td>
                                    <td><code>/products/:id</code></td>
                                    <td><code>destroy($id)</code></td>
                                    <td>Delete product from database</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">HTML Form &mdash; PUT/PATCH/DELETE Method Spoofing</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>&lt;!-- HTML forms only support GET & POST natively. Use _method to spoof PUT / DELETE --&gt;
&lt;form action="&lt;?= BASE_URL ?&gt;/products/42" method="POST"&gt;
    &lt;input type="hidden" name="csrf_token" value="&lt;?= csrf_token() ?&gt;"&gt;
    &lt;input type="hidden" name="_method" value="PUT"&gt;
    
    &lt;input type="text" name="name" value="Enterprise Server" class="form-control"&gt;
    &lt;button type="submit" class="btn btn-primary"&gt;Update Product&lt;/button&gt;
&lt;/form&gt;

&lt;!-- For JavaScript fetch / AJAX clients, send the header: --&gt;
// X-HTTP-Method-Override: PUT</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 6: High-Speed Route Caching -->
            <section id="route-caching" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-forward-fast text-success"></i> High-Speed Route Caching Engine (v2.1.0)
                        </div>
                        <p class="doc-lead">
                            In production environments, scanning dozens of route files on every HTTP request consumes disk I/O. NovaFlow's built-in Route Caching compiles all routes into a high-speed serialized snapshot in <code>storage/cache/routes.cache.php</code>.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-forward-fast text-success"></i> হাই-স্পিড রাউট ক্যাশিং ইঞ্জিন (Route Caching)
                        </div>
                        <p class="doc-lead">
                            প্রোডাকশন সার্ভারে প্রতি রিকোয়েস্টে ফাইল স্ক্যানিং ও রেজেক্স কম্পাইলেশন এড়িয়ে চলার জন্য NovaFlow সব রাউটকে কম্পাইল করে <code>storage/cache/routes.cache.php</code> ফাইলে ক্যাশ করে রাখে, যা মাত্র <strong>০.০৬৯ মিলি-সেকেন্ডে</strong> রেসপন্স দেয়।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">Terminal &mdash; CLI Route Cache Commands</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code># 1. Compile and cache all routes for high-throughput production
php cli.php route:cache

# Output:
# [SUCCESS] Cached 24 routes to storage/cache/routes.cache.php!

# 2. Clear route cache when developing or adding new routes
php cli.php route:clear

# 3. View all registered routes in a table
php cli.php route:list</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 7: Multi-Database & Query Builder -->
            <section id="querybuilder" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-database text-primary"></i> Multi-Database Grammars & Query Builder
                        </div>
                        <p class="doc-lead">
                            NovaFlow provides a unified fluent Query Builder that automatically adapts its SQL dialect for <strong>MySQL (InnoDB)</strong>, <strong>PostgreSQL</strong>, and <strong>SQLite</strong>.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-database text-primary"></i> মাল্টি-ডাটাবেজ ও ফ্লুয়েন্ট কুয়েরি বিল্ডার (Query Builder)
                        </div>
                        <p class="doc-lead">
                            NovaFlow-তে ডাটাবেজ অপারেশনের জন্য শক্তিশালী ফ্লুয়েন্ট কুয়েরি বিল্ডার দেওয়া হয়েছে যা <strong>MySQL</strong>, <strong>PostgreSQL</strong> এবং <strong>SQLite</strong> এর জন্য স্বয়ংক্রিয়ভাবে এসকিউএল সিনট্যাক্স অ্যাডাপ্ট করে।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">PHP &mdash; Advanced Query Operations</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>use NovaFlow\Core\DB;

// 1. Fetching Records with Conditions & Joins
$orders = DB::table('orders')
    ->join('users', 'orders.user_id', '=', 'users.id')
    ->where('orders.status', 'completed')
    ->whereIn('orders.payment_method', ['stripe', 'bank_transfer'])
    ->select('orders.*', 'users.email', 'users.name as customer_name')
    ->orderBy('orders.id', 'DESC')
    ->paginate(15); // Automatically generates pagination metadata

// 2. Direct Aggregations
$totalRevenue = DB::table('orders')->where('status', 'paid')->sum('total_amount');
$activeCount  = DB::table('users')->where('status', 'active')->count();

// 3. Atomic Database Transactions
DB::beginTransaction();
try {
    DB::table('accounts')->where('id', $fromId)->decrement('balance', 500);
    DB::table('accounts')->where('id', $toId)->increment('balance', 500);
    
    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
    throw $e;
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 8: Services & DI -->
            <section id="services-di" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-plug text-info"></i> Dependency Injection & Service Layer
                        </div>
                        <p class="doc-lead">
                            Controllers stay lean and testable by delegating all business logic to dedicated Service classes. NovaFlow's reflection-based Container auto-wires dependencies recursively.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-plug text-info"></i> ডিপেন্ডেন্সি ইনজেকশন ও সার্ভিস লেয়ার (DI & Services)
                        </div>
                        <p class="doc-lead">
                            কন্ট্রোলারকে স্লিম এবং টেস্টেবল রাখতে বিজনেস লজিকগুলো <code>app/Services/</code>-এ লিখুন। NovaFlow-এর রিফ্লেকশন কন্টেইনার স্বয়ংক্রিয়ভাবে ডিপেন্ডেন্সি ইনজেকশন পরিচালনা করে।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">app/Services/InvoiceService.php</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>namespace App\Services;

use NovaFlow\Core\DB;

class InvoiceService {
    public function createInvoice(array $data): int {
        return DB::table('invoices')->insertGetId([
            'customer_id'  => $data['customer_id'],
            'total_amount' => $data['total_amount'],
            'created_at'   => date('Y-m-d H:i:s')
        ]);
    }
}

// Controller Constructor Auto-Wiring:
namespace App\Controllers;

use NovaFlow\Core\Controller;
use App\Services\InvoiceService;

class InvoiceController extends Controller {
    // Injected automatically by NovaFlow Container
    public function __construct(protected InvoiceService $invoiceService) {
        parent::__construct();
    }

    public function store() {
        $id = $this->invoiceService->createInvoice(request()->all());
        return redirect('/invoices/' . $id);
    }
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 9: Controllers & REST API -->
            <section id="controllers-api" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-network-wired text-success"></i> Building High-Performance REST APIs
                        </div>
                        <p class="doc-lead">
                            Extend <code>ApiController</code> to automatically inherit standardized JSON responses, HTTP status code bindings, and JWT Bearer token authentication.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-network-wired text-success"></i> রেস্ট এপিআই ডেভেলপমেন্ট (REST API)
                        </div>
                        <p class="doc-lead">
                            <code>ApiController</code> এক্সটেন্ড করে মুহূর্তেই স্ট্যান্ডার্ড JSON রেসপন্স, এইচটিটিপি স্ট্যাটাস কোড এবং বিয়ারার টোকেন নির্ভর এপিআই তৈরি করা যায়।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">app/Controllers/Api/V1/ProductApiController.php</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>namespace App\Controllers\Api\V1;

use NovaFlow\Core\ApiController;
use NovaFlow\Core\DB;

class ProductApiController extends ApiController {
    public function index() {
        $products = DB::table('products')->limit(20)->get();
        return $this->success($products, 'Products retrieved successfully');
    }

    public function show($id) {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return $this->error('Product not found', 404);
        }
        return $this->json($product);
    }
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 10: Middleware & Security -->
            <section id="middleware-sec" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-shield-halved text-warning"></i> Middleware & Enterprise Security
                        </div>
                        <p class="doc-lead">
                            NovaFlow enforces defence-in-depth protection out of the box, including CSRF validation, IP Rate-Limiting, Content Security Policies, and secure session management.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-shield-halved text-warning"></i> মিডলওয়্যার ও এন্টারপ্রাইজ সিকিউরিটি (Security)
                        </div>
                        <p class="doc-lead">
                            NovaFlow-তে রয়েছে ব্যাংক-গ্রেড নিরাপত্তা ব্যবস্থা: স্বয়ংক্রিয় CSRF ভ্যালিডেশন, আইপি রেট-লিমিটিং, সিকিউরিটি হেডার্স (HSTS, CSP), এবং নিরাপদ সেশন এনক্রিপশন।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">app/Middleware/RateLimitMiddleware.php</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;

class CustomAuthMiddleware extends Middleware {
    public function handle(Request $request, \Closure $next) {
        if (!auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect('/login');
        }

        return $next($request);
    }
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 11: Async Queues -->
            <section id="queues" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-clock-rotate-left text-danger"></i> Async Background Queues & Workers
                        </div>
                        <p class="doc-lead">
                            Offload heavy operations such as emailing, PDF generation, and webhook dispatches to asynchronous background workers.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-clock-rotate-left text-danger"></i> ব্যাকগ্রাউন্ড কিউ সিস্টেম (Async Queues)
                        </div>
                        <p class="doc-lead">
                            ভারী কাজগুলো (যেমন: বাল্ক ইমেইল পাঠানো, পিডিএফ জেনারেশন বা থার্ড-পার্টি ওয়েবহুক) ব্যাকগ্রাউন্ড প্রসেসে রেখে ব্যবহারকারীর ওয়েব রিকোয়েস্টকে সর্বদা সুপারফাস্ট রাখুন।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">PHP &mdash; Dispatching & Running Jobs</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>use NovaFlow\Core\Queue;
use App\Jobs\SendWelcomeEmailJob;

// 1. Dispatch asynchronous job into queue
Queue::push(new SendWelcomeEmailJob($user->email, $user->name));

// 2. Start the worker daemon from terminal (or Supervisor in production)
php cli.php --work</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 12: Cloud Storage -->
            <section id="storage" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-cloud-arrow-up text-info"></i> Cloudinary & S3 Cloud Storage
                        </div>
                        <p class="doc-lead">
                            Seamlessly store user uploads on <strong>Cloudinary</strong> or <strong>AWS S3</strong> with local disk fallback when running offline.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-cloud-arrow-up text-info"></i> ক্লাউড স্টোরেজ (Cloudinary & S3)
                        </div>
                        <p class="doc-lead">
                            ব্যবহারকারীর আপলোড করা ফাইলগুলো ক্লাউডিনারি (Cloudinary) অথবা অ্যামাজন S3-তে সরাসরি আপলোড করার ফুল ড্রাইভার সাপোর্ট রয়েছে।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">PHP &mdash; File Upload Handling</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>use App\Services\CloudinaryService;

class MediaController extends Controller {
    public function upload(CloudinaryService $cloudinary) {
        $file = request()->file('avatar');
        
        // Uploads to cloud with automatic image optimization
        $secureUrl = $cloudinary->upload($file['tmp_name'], 'avatars');
        
        DB::table('users')->where('id', auth()->id())->update(['avatar' => $secureUrl]);
        return redirect()->back()->with('success', 'Avatar updated!');
    }
}</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 13: CLI Tools & Master CRUD -->
            <section id="cli-tools" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-terminal text-warning"></i> CLI PowerPack & Master CRUD Scaffold
                        </div>
                        <p class="doc-lead">
                            The <code>cli.php</code> console gives you instant scaffolding, database migrations, route caches, and diagnostic health checks.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-terminal text-warning"></i> সিএলআই পাওয়ারপ্যাক ও মাস্টার ক্রাড (CLI Tools)
                        </div>
                        <p class="doc-lead">
                            <code>cli.php</code> হলো একটি অল-ইন-ওয়ান কনসোল টুল। মাত্র ১ ক্লিকে ফুল ডাটাবেজ মডেল, মাইগ্রেশন, সিডার এবং কন্ট্রোলার তৈরি করা যায়।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">Terminal &mdash; CLI Menu Options</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code>php cli.php

=========================================
  NovaFlow v2.1.0 Enterprise CLI Console
=========================================
 [1]  System Health & Environment Doctor
 [2]  Database Migration Runner (up)
 [3]  Database Migration Rollback (down)
 [4]  Database Seeder Runner
 [5]  Generate Migration File
 [6]  Generate Model
 [7]  Generate Controller
 [8]  Generate Service Class
 [9]  Route Cache (High-Performance Snapshot)
 [10] Route Clear (Reset Route Snapshot)
 [11] Start Background Queue Worker (--work)
 [12] Master CRUD Scaffold Generator (All-in-one)
 [0]  Exit</code></pre>
                    </div>
                </div>
            </section>

            <!-- SECTION 14: Automated Testing -->
            <section id="testing" class="docs-section">
                <div class="doc-card">
                    <div class="lang-en">
                        <div class="doc-title">
                            <i class="fas fa-vial-circle-check text-success"></i> Automated PHPUnit Testing Suite
                        </div>
                        <p class="doc-lead">
                            NovaFlow v2.1.0 ships with <strong>207 automated tests and 708 assertions</strong> covering Modular Routing, Multi-DB QueryBuilder, Security, and Session Integrity.
                        </p>
                    </div>
                    <div class="lang-bn">
                        <div class="doc-title">
                            <i class="fas fa-vial-circle-check text-success"></i> অটোমেটেড টেস্ট স্যুট (PHPUnit Testing)
                        </div>
                        <p class="doc-lead">
                            NovaFlow v2.1.0-এ রয়েছে <strong>২০৭টি স্বয়ংক্রিয় টেস্ট ও ৭০৮টি অ্যাসর্শন</strong> যা ফ্রেমওয়ার্কের প্রতিটি কম্পোনেন্টের ১০০% নির্ভরযোগ্যতা নিশ্চিত করে।
                        </p>
                    </div>

                    <div class="code-terminal">
                        <div class="code-header">
                            <div class="code-dots"><span class="code-dot dot-red"></span><span class="code-dot dot-yellow"></span><span class="code-dot dot-green"></span></div>
                            <span class="code-title">Terminal &mdash; Execute Test Suite</span>
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                        </div>
                        <pre class="code-body"><code># Run full test suite
php vendor/bin/phpunit

# Output:
# OK (207 tests, 708 assertions) - Memory: 10.50 MB</code></pre>
                    </div>
                </div>
            </section>

            <!-- Bottom Call to Action Card -->
            <div class="doc-card text-center my-4" style="background: radial-gradient(circle at center, rgba(239, 68, 68, 0.12) 0%, rgba(17, 24, 39, 0.7) 100%);">
                <h4 class="fw-bold text-white mb-2">Ready to Build Enterprise ERPs with NovaFlow?</h4>
                <p class="text-muted small mb-4" style="max-width: 520px; margin: 0 auto;">
                    Explore the interactive API Explorer, generate your first modular routes, or inspect the open-source GitHub repository.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>/" class="btn btn-glow-primary rounded-pill px-4 py-2">
                        <i class="fas fa-arrow-left me-2"></i> Back to Homepage
                    </a>
                    <a href="<?= BASE_URL ?>/api/docs" class="btn btn-glass rounded-pill px-4 py-2">
                        <i class="fas fa-network-wired me-2"></i> API Explorer
                    </a>
                    <a href="https://github.com/atifsoftware/NovaFlow" target="_blank" class="btn btn-glass rounded-pill px-4 py-2">
                        <i class="fab fa-github me-2"></i> GitHub Repository
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Language Switcher Function
    function setLang(lang) {
        if (lang === 'bn') {
            $('.lang-en').hide();
            $('.lang-bn').fadeIn(200);
            $('#btnBn').removeClass('inactive').addClass('active');
            $('#btnEn').removeClass('active').addClass('inactive');
            $('#docsSearch').attr('placeholder', 'টপিক খুঁজুন...');
        } else {
            $('.lang-bn').hide();
            $('.lang-en').fadeIn(200);
            $('#btnEn').removeClass('inactive').addClass('active');
            $('#btnBn').removeClass('active').addClass('inactive');
            $('#docsSearch').attr('placeholder', 'Search docs...');
        }
    }

    // Copy Code Block Handler
    function copyCode(btn) {
        const pre = btn.closest('.code-terminal').querySelector('pre.code-body');
        if (!pre) return;
        
        const text = pre.innerText;
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success"></i> Copied!';
            btn.style.borderColor = '#22c55e';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.style.borderColor = '';
            }, 2000);
        });
    }

    // Docs Sidebar Search Filter
    function filterDocsNav() {
        const query = $('#docsSearch').val().toLowerCase();
        $('.sidebar-link').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.includes(query) || query === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Smooth Scroll Spy & Active State Tracking
    $(document).ready(function() {
        $('.sidebar-link').on('click', function(e) {
            $('.sidebar-link').removeClass('active');
            $(this).addClass('active');
        });

        // Intersection Observer for ScrollSpy
        const sections = document.querySelectorAll('.docs-section');
        const navLinks = document.querySelectorAll('.sidebar-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 140;
                if (window.scrollY >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });

            if (current) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active');
                    }
                });
            }
        });
    });
</script>
