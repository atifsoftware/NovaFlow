<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-3">
    <h1 class="h2 fw-bold text-dark">নতুন ব্যবহারকারী</h1>
</div>

<?php flash('success'); flash('error'); ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/users/store">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">নাম *</label>
                <input type="text" name="name" class="form-control" placeholder="ব্যবহারকারীর নাম" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">ইমেইল *</label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">পাসওয়ার্ড *</label>
                <input type="password" name="password" class="form-control" placeholder="পাসওয়ার্ড" required minlength="6">
                <small class="text-muted">কমপক্ষে ৬ অক্ষরের হতে হবে।</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">রোল</label>
                <select name="role" class="form-select">
                    <option value="user">User</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">স্ট্যাটাস</label>
                <select name="status" class="form-select">
                    <option value="active">সক্রিয়</option>
                    <option value="inactive">নিষ্ক্রিয়</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">সংরক্ষণ করুন</button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary">বাতিল</a>
            </div>
        </form>
    </div>
</div>