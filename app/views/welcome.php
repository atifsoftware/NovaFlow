<style>
    .hero-section {
        background: radial-gradient(circle at 10% 20%, rgba(255, 235, 235, 0.6) 0%, rgba(255, 255, 255, 0.9) 80.7%);
        position: relative;
        overflow: hidden;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: rgba(220, 53, 69, 0.05);
        border-radius: 50%;
        top: -50px;
        right: -50px;
        filter: blur(80px);
    }
    .hero-section::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: rgba(13, 110, 253, 0.03);
        border-radius: 50%;
        bottom: -100px;
        left: -100px;
        filter: blur(100px);
    }
    .feat-card {
        border: 1px solid rgba(0, 0, 0, 0.04);
        border-radius: 20px;
        background: #ffffff;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .feat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(220, 53, 69, 0.08) !important;
        border-color: rgba(220, 53, 69, 0.15);
    }
    .feat-icon-box {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        font-size: 24px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .feat-card:hover .feat-icon-box {
        transform: scale(1.1) rotate(5deg);
    }
    .bullet-point {
        background: #ffffff;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
</style>

<!-- Hero Section -->
<section class="py-5 text-center hero-section">
    <div class="container py-5 position-relative">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <span class="badge bg-danger rounded-pill px-3 py-2 mb-3 text-uppercase fw-bold shadow-sm" style="font-size: 11px; letter-spacing: 1px;"><?= __('version', 'Version 1.0.0') ?></span>
                <h1 class="display-3 fw-bold text-dark mb-4" style="letter-spacing: -1px; line-height: 1.1;">
                    <?= current_lang() === 'bn' ? 'স্বাগতম <span class="text-danger">NovaFlow</span> ফ্রেমওয়ার্কে' : 'Welcome to <span class="text-danger">NovaFlow</span> Framework' ?>
                </h1>
                <p class="lead text-muted mb-5 px-lg-5" style="font-size: 1.15rem; line-height: 1.6;">
                    <?= __('welcome_subtitle', 'An ultra-fast, modern, and feature-rich PHP MVC Framework. Built to make your web development dynamic, structured, and professional.') ?>
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>/login" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold shadow-md">
                        <?= __('get_started', 'শুরু করুন') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/docs" class="btn btn-outline-dark btn-lg rounded-pill px-5 fw-bold">
                        <?= __('documentation', 'ডকুমেন্টেশন') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid Section -->
<section class="py-5" style="background-color: #fafbfc;">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2 text-dark"><?= __('features_title', 'NovaFlow ফ্রেমওয়ার্কের মূল সুবিধাসমূহ') ?></h2>
            <p class="text-muted mx-auto" style="max-width: 600px;"><?= __('features_subtitle', 'আধুনিক ওয়েব অ্যাপ্লিকেশনের সব জটিল ফিচার সহজে ইমপ্লিমেন্ট করার জন্য NovaFlow-তে রয়েছে রেডিমেড সলিউশন।') ?></p>
        </div>

        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-danger" style="background: rgba(220, 53, 69, 0.08);">
                        <i class="fas fa-route"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_routing_title', 'দ্রুতগতির রাউটিং ইঞ্জিন') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_routing_desc', 'কাস্টম মিডলওয়্যার, রাউট গ্রুপিং এবং প্যারামিটার বাইন্ডিং সমর্থিত সুপার-ফাস্ট রাউটিং সিস্টেম।') ?></p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-primary" style="background: rgba(13, 110, 253, 0.08);">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_container_title', 'ডিপেন্ডেন্সি ইনজেকশন (DI)') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_container_desc', 'মডার্ন IoC/DI কন্টেইনার আর্কিটেকচার যা অ্যাপ্লিকেশনের অবজেক্ট লাইফসাইকেল ও কাপলিং সহজ করে।') ?></p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-success" style="background: rgba(25, 135, 84, 0.08);">
                        <i class="fas fa-database"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_orm_title', 'অ্যাক্টিভ রেকর্ড ওআরএম (ORM)') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_orm_desc', 'ডাটাবেস টেবিল ও রিলেশনশিপ (One-to-One, One-to-Many) হ্যান্ডল করার জন্য শক্তিশালী ওআরএম মডেল।') ?></p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-info" style="background: rgba(13, 202, 240, 0.08);">
                        <i class="fas fa-filter"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_qb_title', 'কুয়েরি বিল্ডার') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_qb_desc', 'SQL ইনজেকশন থেকে শতভাগ নিরাপদ এবং সহজে জটিল কুয়েরি লেখার জন্য ফ্লুয়েন্ট বিল্ডার এপিআই।') ?></p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-warning" style="background: rgba(255, 193, 7, 0.08);">
                        <i class="fas fa-terminal"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_cli_title', 'CLI কোড জেনারেটর') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_cli_desc', 'এক ক্লিকে ডাটাবেস মাইগ্রেশন, মডেল, কন্ট্রোলার এবং অ্যাডমিন ক্রুড (CRUD) তৈরির শক্তিশালী CLI টুল।') ?></p>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-secondary" style="background: rgba(108, 117, 125, 0.08);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_queue_title', 'বিল্ট-ইন কিউ (Queue) সিস্টেম') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_queue_desc', 'ভারী কাজগুলো (যেমন: ইমেইল পাঠানো) ব্যাকগ্রাউন্ডে করার জন্য প্রফেশনাল কিউ ড্রাইভার ও ওয়ার্কার।') ?></p>
                </div>
            </div>

            <!-- Feature 7 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-primary" style="background: rgba(13, 110, 253, 0.08);">
                        <i class="fas fa-memory"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_cache_title', 'স্মার্ট ক্যাশিং ইঞ্জিন') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_cache_desc', 'ফাইল ও মেমরি ক্যাশ মেকানিজম যা ডাটাবেস লোড কমিয়ে সাইটের স্পিড দ্বিগুণ করে।') ?></p>
                </div>
            </div>

            <!-- Feature 8 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-dark" style="background: rgba(33, 37, 41, 0.08);">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_security_title', 'নিরাপত্তা ও অথেন্টিকেশন') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_security_desc', 'CSRF সুরক্ষা, পাসওয়ার্ড হ্যাশিং, সেশন সিকিউরিটি এবং এপিআই-এর জন্য বিল্ট-ইন JWT টোকেন সাপোর্ট।') ?></p>
                </div>
            </div>

            <!-- Feature 9 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-danger" style="background: rgba(220, 53, 69, 0.08);">
                        <i class="fas fa-bug"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_errors_title', 'ইন্টেলিজেন্ট এরর স্ক্রিন') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_errors_desc', 'ডেভেলপমেন্টে কোড লেভেলে এরর ট্র্যাকিং ও বিস্তারিত কলস্ট্যাক দেখানোর জন্য আকর্ষণীয় ডিবাগ উইন্ডো।') ?></p>
                </div>
            </div>

            <!-- Feature 10 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-success" style="background: rgba(25, 135, 84, 0.08);">
                        <i class="fas fa-file-image"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_image_title', 'ইমেজ প্রসেসিং লাইব্রেরি') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_image_desc', 'ছবির সাইজ পরিবর্তন, ক্রপ এবং ওয়াটারমার্ক যোগ করার চমৎকার লাইব্রেরি মডিউল।') ?></p>
                </div>
            </div>

            <!-- Feature 11 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-warning" style="background: rgba(255, 193, 7, 0.08);">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_validator_title', 'ইনপুট ভ্যালিডেটর ইঞ্জিন') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_validator_desc', 'ইউজার ইনপুট যাচাই করার জন্য ৩০টিরও বেশি বিল্ট-ইন রুলস সহ শক্তিশালী ভ্যালিডেটর।') ?></p>
                </div>
            </div>

            <!-- Feature 12 -->
            <div class="col-md-6 col-lg-4">
                <div class="p-4 feat-card shadow-sm h-100">
                    <div class="feat-icon-box text-info" style="background: rgba(13, 202, 240, 0.08);">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3"><?= __('feat_api_title', 'API এক্সপ্লোরার ও ডক্স') ?></h5>
                    <p class="text-muted small mb-0"><?= __('feat_api_desc', 'FastAPI-র মতো ইন্টারেক্টিভ API ডকুমেন্টেশন ও টেস্ট করার জন্য চমৎকার Swagger-স্টাইল ভিউ।') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why NovaFlow Section -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <h2 class="fw-bold mb-5 text-center text-dark"><?= __('why_novaflow', 'কেন NovaFlow ব্যবহার করবেন?') ?></h2>
        <div class="row align-items-center justify-content-center">
            <div class="col-md-8 col-lg-6 text-start">
                <div class="d-flex gap-4 mb-4 align-items-start">
                    <div class="bullet-point text-danger"><i class="fas fa-desktop"></i></div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><?= __('why_desc_1_title', 'প্রিমিয়াম অ্যাডমিন প্যানেল') ?></h5>
                        <p class="small text-muted mb-0"><?= __('why_desc_1_body', 'সম্পূর্ণ রেডিমেড, আকর্ষণীয় এবং ডার্ক/লাইট থিম সমর্থিত অ্যাডমিন ড্যাশবোর্ড প্যানেল।') ?></p>
                    </div>
                </div>
                <div class="d-flex gap-4 mb-4 align-items-start">
                    <div class="bullet-point text-primary"><i class="fas fa-clock-rotate-left"></i></div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><?= __('why_desc_2_title', 'অ্যাক্টিভিটি লগ ও সিকিউরিটি') ?></h5>
                        <p class="small text-muted mb-0"><?= __('why_desc_2_body', 'ডিফল্ট অডিট ট্রেইল যা ইউজার লগইন, অ্যাকশন ও আইপি অ্যাড্রেস অটো ট্র্যাকিং করে।') ?></p>
                    </div>
                </div>
                <div class="d-flex gap-4 align-items-start">
                    <div class="bullet-point text-success"><i class="fas fa-rocket"></i></div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><?= __('why_desc_3_title', 'মডার্ন আর্কিটেকচার') ?></h5>
                        <p class="small text-muted mb-0"><?= __('why_desc_3_body', 'সম্পূর্ণ PSR compliant কোড এবং সহজে স্কেল করার মতো ডিজাইন প্যাটার্ন।') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
