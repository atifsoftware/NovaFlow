<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-3">
    <h1 class="h2 fw-bold text-dark">ব্যবহারকারী সম্পাদনা</h1>
</div>

<?php flash('success'); flash('error'); ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/users/update/<?= $user->id ?>">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">নাম *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user->name) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">ইমেইল *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">নতুন পাসওয়ার্ড (অপশional)</label>
                <input type="password" name="password" class="form-control" placeholder="নতুন পাসওয়ার্ড দিন">
                <small class="text-muted">রাখতে চাইলে খালি রাখুন।</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">রোল</label>
                <select name="role" class="form-select">
                    <option value="user" <?= $user->role === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="manager" <?= $user->role === 'manager' ? 'selected' : '' ?>>Manager</option>
                    <option value="admin" <?= $user->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">স্ট্যাটাস</label>
                <select name="status" class="form-select">
                    <option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>>সক্রিয়</option>
                    <option value="inactive" <?= $user->status === 'inactive' ? 'selected' : '' ?>>নিষ্ক্রিয়</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">আপডেট করুন</button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary">বাতিল</a>
            </div>
        </form>
    </div>
</div>