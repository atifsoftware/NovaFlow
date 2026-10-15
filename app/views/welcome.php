<style>
    /* Hero Mesh & Glow Effects */
    .hero-wrapper {
        position: relative;
        background: radial-gradient(circle at 50% 10%, rgba(239, 68, 68, 0.12) 0%, rgba(6, 182, 212, 0.04) 40%, #0b0f19 80%);
        overflow: hidden;
        padding-top: 5rem;
        padding-bottom: 5rem;
    }

    .hero-glow-1 {
        position: absolute;
        top: -120px;
        left: 50%;
        transform: translateX(-50%);
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(239, 68, 68, 0.18) 0%, rgba(0,0,0,0) 70%);
        filter: blur(100px);
        pointer-events: none;
        z-index: 0;
    }

    .hero-glow-2 {
        position: absolute;
        bottom: -150px;
        right: 10%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(6, 182, 212, 0.12) 0%, rgba(0,0,0,0) 70%);
        filter: blur(90px);
        pointer-events: none;
        z-index: 0;
    }

    .gradient-text {
        background: linear-gradient(135deg, #ffffff 20%, #ef4444 65%, #f97316 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    .gradient-text-cyan {
        background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* Pulsing Badge */
    .pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #fca5a5;
        text-transform: uppercase;
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #ef4444;
        box-shadow: 0 0 10px #ef4444;
        animation: pulseAnimation 1.8s infinite;
    }

    @keyframes pulseAnimation {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.35); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }

    /* Metric Cards */
    .metric-card {
        background: rgba(17, 24, 39, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 1.5rem;
        backdrop-filter: blur(12px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .metric-card:hover {
        transform: translateY(-5px);
        border-color: rgba(239, 68, 68, 0.4);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(239, 68, 68, 0.15);
    }

    .metric-value {
        font-size: 2.25rem;
        font-weight: 800;
        letter-spacing: -1px;
        line-height: 1;
        margin-bottom: 0.4rem;
        font-family: 'Fira Code', monospace;
    }

    /* Glass Cards */
    .glass-card {
        background: rgba(17, 24, 39, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 18px;
        backdrop-filter: blur(12px);
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        border-color: rgba(255, 255, 255, 0.18);
        background: rgba(22, 30, 49, 0.8);
        transform: translateY(-4px);
    }

    /* Module Cards */
    .module-card {
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.8) 100%);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 20px;
        padding: 2rem;
        transition: all 0.35s ease;
        position: relative;
    }

    .module-card:hover {
        transform: translateY(-6px);
        border-color: rgba(239, 68, 68, 0.35);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(239, 68, 68, 0.12);
    }

    .module-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 1.25rem;
    }

    /* Terminal Window */
    .terminal-window {
        background: #050811;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7);
        overflow: hidden;
    }

    .terminal-header {
        background: #0d121f;
        padding: 10px 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .terminal-dots {
        display: flex;
        gap: 6px;
    }

    .terminal-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
    }

    .code-editor {
        font-family: 'Fira Code', monospace;
        font-size: 0.88rem;
        line-height: 1.7;
        padding: 1.5rem;
        color: #e2e8f0;
        overflow-x: auto;
    }

    .code-comment { color: #64748b; }
    .code-keyword { color: #f43f5e; font-weight: 600; }
    .code-string  { color: #34d399; }
    .code-func    { color: #38bdf8; }
    .code-var     { color: #fbbf24; }

    /* Nav Tabs Custom */
    .nav-tabs-custom {
        border-bottom: none;
        gap: 8px;
    }
    .nav-tabs-custom .nav-link {
        color: #94a3b8;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 0.84rem;
        font-weight: 600;
        background: transparent;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link.active {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.12);
    }

    /* Comparison Table */
    .comparison-table {
        background: rgba(15, 23, 42, 0.6);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.07);
        overflow: hidden;
    }
    .comparison-table th {
        background: rgba(30, 41, 59, 0.5);
        color: #f8fafc;
        padding: 1rem 1.25rem;
        font-weight: 700;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .comparison-table td {
        padding: 1rem 1.25rem;
        color: #cbd5e1;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    }
</style>

<!-- Hero Section -->
<section class="hero-wrapper text-center">
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>

    <div class="container position-relative" style="z-index: 1;">
        <!-- Top Release Badge -->
        <div class="d-inline-block mb-4">
            <div class="pill-badge">
                <span class="pulse-dot"></span>
                <span>NovaFlow v2.1.0 Enterprise Edition — High Performance PHP 8.2</span>
            </div>
        </div>

        <!-- Main Title -->
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <h1 class="display-3 fw-extrabold mb-4" style="letter-spacing: -1.5px; line-height: 1.15; font-weight: 800;">
                    <?php if (current_lang() === 'bn'): ?>
                        এন্টারপ্রাইজ ERP ও আধুনিক ওয়েব অ্যাপ্লিকেশনের জন্য <br>
                        <span class="gradient-text">বিদ্যুৎগতির পিএইচপি ফ্রেমওয়ার্ক</span>
                    <?php else: ?>
                        The High-Speed PHP 8.2 MVC Framework for <br>
                        <span class="gradient-text">Modern Enterprise & ERP Systems</span>
                    <?php endif; ?>
                </h1>

                <p class="lead text-secondary mb-5 mx-auto" style="max-width: 780px; font-size: 1.2rem; line-height: 1.65;">
                    <?php if (current_lang() === 'bn'): ?>
                        কোনো ভারী ভেন্ডর ডিপেন্ডেন্সি ছাড়াই মাত্র <strong>০.০১ মিলি-সেকেন্ড রুট ক্যাশিং</strong>, <strong>মাল্টি-ডাটাবেজ ড্রাইভার</strong> (MySQL, Postgres, SQLite), <strong>ডোমেইন-ড্রাইভেন মডিউলার আর্কিটেকচার</strong> এবং ব্যাংক-গ্রেড সিকিউরিটি নিয়ে তৈরি সম্পূর্ণ প্রিমিয়াম সমাধান।
                    <?php else: ?>
                        Zero heavyweight dependencies. Built with ultra-fast <strong>0.01ms route caching</strong>, native <strong>multi-database drivers</strong> (MySQL, Postgres, SQLite), <strong>modular ERP architecture</strong>, and bank-grade security.
                    <?php endif; ?>
                </p>

                <!-- Action CTA Buttons -->
                <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
                    <a href="<?= BASE_URL ?>/login" class="btn btn-glow-primary btn-lg rounded-pill px-5 py-3 fw-bold">
                        <i class="fas fa-rocket me-2"></i> <?= __('get_started', 'শুরু করুন') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/docs" class="btn btn-glass btn-lg rounded-pill px-4 py-3 fw-semibold">
                        <i class="fas fa-book-open me-2 text-danger"></i> <?= __('documentation', 'ডকুমেন্টেশন') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/api/docs" class="btn btn-glass btn-lg rounded-pill px-4 py-3 fw-semibold">
                        <i class="fas fa-terminal me-2 text-cyan"></i> API Explorer
                    </a>
                    <a href="#benchmarks" class="btn btn-glass btn-lg rounded-pill px-4 py-3 fw-semibold">
                        <i class="fas fa-gauge-high me-2 text-warning"></i> বেঞ্চমার্ক
                    </a>
                </div>
            </div>
        </div>

        <!-- Real-Time Live Benchmark Metrics Bar -->
        <div id="benchmarks" class="row g-3 justify-content-center pt-4">
            <div class="col-6 col-md-3">
                <div class="metric-card text-center">
                    <div class="text-danger metric-value">0.069<span class="fs-5">ms</span></div>
                    <div class="small fw-bold text-white mb-1">রুট ক্যাশিং বুট টাইম</div>
                    <div class="text-secondary" style="font-size: 0.78rem;">14,350+ req/sec (route:cache)</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card text-center">
                    <div class="text-info metric-value">6.00<span class="fs-5">MB</span></div>
                    <div class="small fw-bold text-white mb-1">ন্যূনতম র‍্যাম খরচ (RAM)</div>
                    <div class="text-secondary" style="font-size: 0.78rem;">৭৫% কম মেমোরি খরচ</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card text-center">
                    <div class="text-warning metric-value">0.023<span class="fs-5">ms</span></div>
                    <div class="small fw-bold text-white mb-1">কুয়েরি বিল্ডার কম্পাইলেশন</div>
                    <div class="text-secondary" style="font-size: 0.78rem;">41,700+ জটিল SQL/sec</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-card text-center">
                    <div class="text-success metric-value">207<span class="fs-5">/207</span></div>
                    <div class="small fw-bold text-white mb-1">অটোমেটেড টেস্ট পাস</div>
                    <div class="text-secondary" style="font-size: 0.78rem;">১০০% গ্রিন CI/CD পাস</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Interactive Code & Terminal Showcase -->
<section class="py-5" style="background: #080c15;">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-danger fw-bold text-uppercase small" style="letter-spacing: 1.5px;">ELEGANT DEVELOPER EXPERIENCE</span>
            <h2 class="display-6 fw-bold text-white mt-1">ক্লিন, রিডেবল ও পাওয়ারফুল সিনট্যাক্স</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">লারাভেলের মতোই সহজ এবং পরিচিত সিনট্যাক্স, কিন্তু কোনো ভারী ফ্রেমওয়ার্ক ওভারহেড ছাড়াই।</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="terminal-window">
                    <div class="terminal-header">
                        <div class="terminal-dots">
                            <span class="terminal-dot bg-danger"></span>
                            <span class="terminal-dot bg-warning"></span>
                            <span class="terminal-dot bg-success"></span>
                        </div>
                        <ul class="nav nav-tabs nav-tabs-custom" id="codeTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="tab-routing" data-bs-toggle="tab" data-bs-target="#code-routing" type="button">
                                    <i class="fas fa-route me-1"></i> Modular Routing
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="tab-db" data-bs-toggle="tab" data-bs-target="#code-db" type="button">
                                    <i class="fas fa-database me-1"></i> Multi-DB & ORM
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="tab-cli" data-bs-toggle="tab" data-bs-target="#code-cli" type="button">
                                    <i class="fas fa-terminal me-1"></i> CLI & Route Cache
                                </button>
                            </li>
                        </ul>
                        <span class="text-secondary small font-monospace">PHP 8.2</span>
                    </div>

                    <div class="tab-content" id="codeTabsContent">
                        <!-- Code Tab 1: Modular Routing -->
                        <div class="tab-pane fade show active" id="code-routing" role="tabpanel">
                            <pre class="code-editor mb-0"><code><span class="code-comment">// 1. সেলস মডিউলের রুট তৈরি (app/Modules/Sales/routes.php)</span>
<span class="code-var">$router</span>-&gt;<span class="code-func">module</span>(<span class="code-string">'Sales'</span>, <span class="code-keyword">function</span>(<span class="code-var">$router</span>) {
    <span class="code-comment">// মাত্র ১ লাইনে ৭টি আদর্শ RESTful CRUD রুট তৈরি:</span>
    <span class="code-var">$router</span>-&gt;<span class="code-func">resource</span>(<span class="code-string">'invoices'</span>, <span class="code-string">'Sales\InvoiceController'</span>);
    <span class="code-var">$router</span>-&gt;<span class="code-func">resource</span>(<span class="code-string">'customers'</span>, <span class="code-string">'Sales\CustomerController'</span>);

    <span class="code-comment">// কাস্টম রিপোর্ট রুট</span>
    <span class="code-var">$router</span>-&gt;<span class="code-func">get</span>(<span class="code-string">'/reports/summary'</span>, <span class="code-string">'Sales\ReportController@summary'</span>);
}, [<span class="code-string">'middleware'</span> =&gt; [<span class="code-string">'auth'</span>, <span class="code-string">'rbac:sales.view'</span>]]);</code></pre>
                        </div>

                        <!-- Code Tab 2: Multi-DB -->
                        <div class="tab-pane fade" id="code-db" role="tabpanel">
                            <pre class="code-editor mb-0"><code><span class="code-comment">// 2. মাল্টি-ডাটাবেজ সাপোর্ট ও ট্রানজাকশন (MySQL, PostgreSQL, SQLite)</span>
<span class="code-keyword">use</span> <span class="code-func">NovaFlow\Core\DB</span>;

<span class="code-comment">// ফ্লুয়েন্ট কুয়েরি ও পেজিনেশন</span>
<span class="code-var">$orders</span> = <span class="code-func">DB</span>::<span class="code-func">table</span>(<span class="code-string">'orders'</span>)
    -&gt;<span class="code-func">select</span>(<span class="code-string">'orders.*'</span>, <span class="code-string">'customers.name as customer_name'</span>)
    -&gt;<span class="code-func">join</span>(<span class="code-string">'customers'</span>, <span class="code-string">'orders.customer_id'</span>, <span class="code-string">'customers.id'</span>)
    -&gt;<span class="code-func">where</span>(<span class="code-string">'orders.status'</span>, <span class="code-string">'completed'</span>)
    -&gt;<span class="code-func">whereIn</span>(<span class="code-string">'orders.currency'</span>, [<span class="code-string">'BDT'</span>, <span class="code-string">'USD'</span>])
    -&gt;<span class="code-func">paginate</span>(15);

<span class="code-comment">// নেস্টেড ট্রানজাকশন ও সেভপয়েন্ট</span>
<span class="code-func">DB</span>::<span class="code-func">transaction</span>(<span class="code-keyword">function</span>() {
    <span class="code-comment">// অটোমেটিক কমিট এবং রোলব্যাক হ্যান্ডলিং</span>
});</code></pre>
                        </div>

                        <!-- Code Tab 3: CLI -->
                        <div class="tab-pane fade" id="code-cli" role="tabpanel">
                            <pre class="code-editor mb-0"><code><span class="code-comment"># ৩. লাইভ প্রোডাকশন স্পিড ক্যাশিং ও টেস্ট</span>
$ php cli.php route:cache
<span class="code-string">✓ Routes cached successfully! (103 routes compiled in 0.01ms)</span>
<span class="code-func">  File: storage/cache/routes.php</span>

$ php cli.php routes
<span class="code-var">Registered Routes: 103 explicit endpoints across Web, Admin, API & Modules.</span>

$ php vendor/bin/phpunit
<span class="code-string">OK (207 tests, 708 assertions) — Passed in 6.2s</span></code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modular ERP Architecture Section -->
<section class="py-5" style="background: #0b0f19;">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-danger fw-bold text-uppercase small" style="letter-spacing: 1.5px;">DOMAIN-DRIVEN DESIGN</span>
            <h2 class="display-6 fw-bold text-white mt-1">সেলফ-কনটেইন্ড ERP মডিউল আর্কিটেকচার</h2>
            <p class="text-secondary mx-auto" style="max-width: 650px;">
                একটি বিশাল ERP প্ল্যাটফর্ম তৈরি করার জন্য প্রতিটি ডোমেইন নিজস্ব ফোল্ডারে স্বাধীন থাকবে। ক্লায়েন্টভেদে সহজে মডিউল অন/অফ করুন।
            </p>
        </div>

        <div class="row g-4">
            <!-- Sales Module Card -->
            <div class="col-md-4">
                <div class="module-card h-100">
                    <div class="module-icon text-danger" style="background: rgba(239, 68, 68, 0.1);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill mb-2">app/Modules/Sales/</span>
                    <h4 class="fw-bold text-white mb-3">সেলস মডিউল (Sales)</h4>
                    <p class="text-secondary small mb-4">
                        সেলস ইনভয়েস, কোটেশন, কাস্টমার প্রোফাইল, সেলস রিটার্ন এবং মাল্টি-কারেন্সি সামারি রিপোর্ট হ্যান্ডলিং।
                    </p>
                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/sales/invoices` (CRUD Resource)</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/sales/quotations`</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/sales/customers`</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/sales/reports/summary`</li>
                    </ul>
                </div>
            </div>

            <!-- Inventory Module Card -->
            <div class="col-md-4">
                <div class="module-card h-100">
                    <div class="module-icon text-cyan" style="background: rgba(6, 182, 212, 0.1);">
                        <i class="fas fa-boxes-stacked text-info"></i>
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill mb-2">app/Modules/Inventory/</span>
                    <h4 class="fw-bold text-white mb-3">ইনভেন্টরি মডিউল (Inventory)</h4>
                    <p class="text-secondary small mb-4">
                        প্রোডাক্ট/আইটেম ক্যাটালগ, মাল্টি-ওয়্যারহাউস ট্র্যাকিং, স্টক অ্যাডজাস্টমেন্ট এবং লো-স্টক রিয়েলটাইম অ্যালার্ট।
                    </p>
                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/inventory/items` (CRUD Resource)</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/inventory/adjustments`</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/inventory/warehouses`</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/inventory/stock-alert`</li>
                    </ul>
                </div>
            </div>

            <!-- Accounts Module Card -->
            <div class="col-md-4">
                <div class="module-card h-100">
                    <div class="module-icon text-warning" style="background: rgba(245, 158, 11, 0.1);">
                        <i class="fas fa-scale-balanced text-warning"></i>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill mb-2">app/Modules/Accounts/</span>
                    <h4 class="fw-bold text-white mb-3">অ্যাকাউন্টস ও ফিন্যান্স (Accounts)</h4>
                    <p class="text-secondary small mb-4">
                        চার্ট অফ অ্যাকাউন্টস, জার্নাল ও পেমেন্ট ভাউচার, স্বয়ংক্রিয় সাধারণ খতিয়ান (Ledger), ট্রায়াল ব্যালেন্স ও ব্যালেন্স শিট।
                    </p>
                    <ul class="list-unstyled text-secondary small d-flex flex-column gap-2 mb-0">
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/accounts/chart-of-accounts`</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/accounts/vouchers` (Debit/Credit)</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/accounts/ledger`</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> `/accounts/trial-balance`</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Features Grid -->
<section class="py-5" style="background: #080c15;">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-danger fw-bold text-uppercase small" style="letter-spacing: 1.5px;">FRAMEWORK CAPABILITIES</span>
            <h2 class="display-6 fw-bold text-white mt-1">পূর্ণাঙ্গ ফিচার ও প্রযুক্তিগত শক্তিমত্তা</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">একটি পরিপূর্ণ ফ্রেমওয়ার্কে যা যা প্রয়োজন, সবকিছু বিল্ট-ইন অবস্থায় পাবেন।</p>
        </div>

        <div class="row g-4">
            <!-- 1. Multi-DB -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-inline-flex p-3 rounded-3 bg-danger bg-opacity-10 text-danger mb-3 fs-4">
                        <i class="fas fa-database"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-2">মাল্টি-ডাটাবেজ ইঞ্জিন</h5>
                    <p class="text-secondary small mb-0">MySQL InnoDB, PostgreSQL, এবং SQLite-এর নিজস্ব এসকিউএল ডায়ালেক্ট, গ্র্যামার ও ট্রানজাকশন সাপোর্ট।</p>
                </div>
            </div>

            <!-- 2. Route Caching -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-inline-flex p-3 rounded-3 bg-primary bg-opacity-10 text-primary mb-3 fs-4">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-2">রুট ক্যাশিং ইঞ্জিন</h5>
                    <p class="text-secondary small mb-0">`route:cache` দিয়ে শত শত রুট মাত্র ০.০১ মিলি-সেকেন্ডে মেমোরি থেকে এক্সিকিউট করার এন্টারপ্রাইজ সুবিধা।</p>
                </div>
            </div>

            <!-- 3. Bank-Grade Security -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-inline-flex p-3 rounded-3 bg-success bg-opacity-10 text-success mb-3 fs-4">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-2">ব্যাংক-গ্রেড নিরাপত্তা</h5>
                    <p class="text-secondary small mb-0">Argon2id পাসওয়ার্ডিং, AES-256-GCM ডেটা এনক্রিপশন, JWT অথেন্টিকেশন এবং মাল্টি-টেন্যান্ট আরব্যাক (RBAC)।</p>
                </div>
            </div>

            <!-- 4. Cloudinary & S3 -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-inline-flex p-3 rounded-3 bg-info bg-opacity-10 text-info mb-3 fs-4">
                        <i class="fas fa-cloud-arrow-up"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-2">ক্লাউডিনারি ও এডব্লিউএস S3</h5>
                    <p class="text-secondary small mb-0">কোনো ভারী ভেন্ডর SDK ছাড়াই সরাসরি রিয়েল-টাইম ইমেজ রিসাইজ, অটো-ফরম্যাট ও ক্লাউড ফাইল স্টোরেজ।</p>
                </div>
            </div>

            <!-- 5. DI Container -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-inline-flex p-3 rounded-3 bg-warning bg-opacity-10 text-warning mb-3 fs-4">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-2">IoC কন্টেইনার ও অটো-ওয়্যারিং</h5>
                    <p class="text-secondary small mb-0">পিএইচপি রিফ্লেকশন দিয়ে কনস্ট্রাক্টর ও কন্ট্রোলার মেথডে অটোমেটিক সার্ভিস ইনজেকশন রেজল্যুশন।</p>
                </div>
            </div>

            <!-- 6. Queue Worker -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-inline-flex p-3 rounded-3 bg-secondary bg-opacity-25 text-white mb-3 fs-4">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h5 class="fw-bold text-white mb-2">ব্যাকগ্রাউন্ড কিউ সিস্টেম</h5>
                    <p class="text-secondary small mb-0">ভারী টাস্কগুলো (যেমন: ইনভয়েস ইমেইল বা রিপোর্ট জেনারেট) ব্যাকগ্রাউন্ডে চালানোর জন্য কিউ ওয়ার্কার।</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Framework Comparison Matrix -->
<section class="py-5" style="background: #0b0f19;">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-danger fw-bold text-uppercase small" style="letter-spacing: 1.5px;">REAL BENCHMARK METRICS</span>
            <h2 class="display-6 fw-bold text-white mt-1">NovaFlow বনাম ভারী ফ্রেমওয়ার্ক</h2>
            <p class="text-secondary mx-auto" style="max-width: 600px;">সরাসরি বাস্তব সংখ্যা দিয়ে দেখুন NovaFlow কতটা হালকা ও দ্রুতগতির।</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="comparison-table table-responsive">
                    <table class="table table-dark table-hover mb-0" style="background: transparent;">
                        <thead>
                            <tr>
                                <th>মেট্রিক / বৈশিষ্ট্য</th>
                                <th class="text-danger">🚀 NovaFlow v2.1.0</th>
                                <th class="text-secondary">🐢 Heavy Frameworks (Laravel 11 / Symfony 7)</th>
                                <th class="text-success">আমাদের সুবিধা</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">Framework Boot Time</td>
                                <td class="text-danger fw-bold">০.০৬৯ ms</td>
                                <td>৮.০০ – ২৫.০০ ms</td>
                                <td class="text-success fw-bold">১০০ গুণ বেশি দ্রুত বুট ⚡</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Base RAM Usage</td>
                                <td class="text-danger fw-bold">~৬.০০ MB</td>
                                <td>২৫.০০ – ৪০.০০ MB</td>
                                <td class="text-success fw-bold">৭৫% কম মেমোরি খরচ 🪶</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Vendor Directory Bloat</td>
                                <td class="text-danger fw-bold">জিরো ভারী ডিপেন্ডেন্সি</td>
                                <td>৮০+ মেগাবাইট ভেন্ডর ফাইল</td>
                                <td class="text-success fw-bold">সহজে পোর্টেবল ও ডেপ্লয়</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">SQL Query Compilation</td>
                                <td class="text-danger fw-bold">০.০২৩ ms</td>
                                <td>০.১৫ – ০.৪০ ms</td>
                                <td class="text-success fw-bold">মুহূর্তে এসকিউএল প্রস্তুত</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Automated Test Execution</td>
                                <td class="text-danger fw-bold">২০৭ টি টেস্ট ~৬ সেকেন্ডে</td>
                                <td>২৫ – ৫০ সেকেন্ড</td>
                                <td class="text-success fw-bold">দ্রুত CI/CD সাইকেল</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bottom CTA Banner -->
<section class="py-5 text-center" style="background: radial-gradient(circle at 50% 50%, rgba(239, 68, 68, 0.15) 0%, #080c15 80%);">
    <div class="container py-4">
        <h2 class="display-6 fw-bold text-white mb-3">আপনার পরবর্তী এন্টারপ্রাইজ সিস্টেম তৈরি করতে প্রস্তুত?</h2>
        <p class="text-secondary mx-auto mb-4" style="max-width: 600px;">
            NovaFlow-এর সাথে আজই শুরু করুন অত্যন্ত দ্রুত, নির্ভরযোগ্য ও সুরক্ষিত অ্যাপ্লিকেশন ডেভেলপমেন্ট।
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>/login" class="btn btn-glow-primary btn-lg rounded-pill px-5 fw-bold">
                <i class="fas fa-sign-in-alt me-2"></i> লগইন করুন
            </a>
            <a href="<?= BASE_URL ?>/docs" class="btn btn-glass btn-lg rounded-pill px-5 fw-bold">
                <i class="fas fa-book me-2"></i> ডকুমেন্টেশন দেখুন
            </a>
        </div>
    </div>
</section>
