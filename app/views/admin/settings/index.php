<div class="adm-page-header">
    <div class="adm-page-title">
        <div class="title-icon"><i class="fas fa-cogs"></i></div>
        সাইট সেটিংস
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <form action="<?= BASE_URL ?>/admin/settings/update" method="POST">
                <?= csrf_field() ?>
                
                <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-info-circle me-2"></i>বেসিক ইনফরমেশন</h6>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">ওয়েবসাইটের নাম</label>
                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? 'NovaFlow') ?>" placeholder="যেমন: My Super App">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">সাইট ট্যাগলাইন</label>
                        <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars($settings['site_tagline'] ?? 'Modern PHP MVC Framework') ?>" placeholder="যেমন: Fast & Secure">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">কন্টাক্ট ইমেইল</label>
                        <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email'] ?? 'admin@example.com') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">ফোন নাম্বার</label>
                        <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($settings['contact_phone'] ?? '01700000000') ?>">
                    </div>
                </div>

                <h6 class="fw-bold mb-4 text-primary mt-4"><i class="fas fa-share-alt me-2"></i>সোশ্যাল মিডিয়া লিংক</h6>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">ফেসবুক পেজ ইউআরএল</label>
                    <input type="url" name="social_facebook" class="form-control" value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">ইউটিউব চ্যানেল ইউআরএল</label>
                    <input type="url" name="social_youtube" class="form-control" value="<?= htmlspecialchars($settings['social_youtube'] ?? '') ?>">
                </div>

                <hr class="opacity-10 my-4">
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">সেভিংস আপডেট করুন</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
            <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i>টিপস</h6>
            <p class="small text-muted mb-0">এই সেটিংসগুলো আপনার ওয়েবসাইটের বিভিন্ন জায়গায় (যেমন ফুটার, কন্টাক্ট পেজ) সরাসরি আপডেট হয়ে যাবে। আপনার ব্র্যান্ডিং পরিবর্তন করতে এখান থেকে সব তথ্য পরিবর্তন করুন।</p>
        </div>
    </div>
</div>
