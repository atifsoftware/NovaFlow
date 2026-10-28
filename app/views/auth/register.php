<div style="background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d); min-height: 100vh; width: 100%; position: absolute; top: 0; left: 0; display: flex; align-items: center; justify-content: center;">
    <div class="container py-4">
        <div class="row justify-content-center m-0">
            <div class="col-md-5">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-5 bg-white">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark">Nova<span class="text-danger">Flow</span></h2>
                            <p class="text-muted small">নতুন অ্যাকাউন্ট তৈরি করুন</p>
                        </div>

                        <?php flash('error'); flash('success'); ?>

                        <form action="<?= BASE_URL ?>/register" method="POST">
                            <?= csrf_field() ?>
                            
                            <div class="mb-3">
                                <label class="form-label text-dark small fw-semibold">আপনার নাম</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="আপনার পূর্ণ নাম" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark small fw-semibold">ইমেইল এড্রেস</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control bg-light border-0 py-2" placeholder="user@example.com" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-dark small fw-semibold">পাসওয়ার্ড (কমপক্ষে ৮ অক্ষর)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control bg-light border-0 py-2" placeholder="••••••••" required minlength="8">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-dark small fw-semibold">পাসওয়ার্ড নিশ্চিত করুন</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-check-circle text-muted"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control bg-light border-0 py-2" placeholder="••••••••" required minlength="8">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold text-uppercase shadow-sm">নিবন্ধন সম্পন্ন করুন</button>
                            
                            <div class="text-center mt-4">
                                <p class="small text-muted mb-1">ইতিমধ্যেই অ্যাকাউন্ট আছে? <a href="<?= BASE_URL ?>/login" class="text-danger fw-semibold text-decoration-none">লগইন করুন</a></p>
                                <a href="<?= BASE_URL ?>/" class="text-muted small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> ওয়েবসাইটে ফিরে যান</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
