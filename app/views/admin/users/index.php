<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-3">
    <div class="d-flex">
        <h1 class="h2 fw-bold text-dark me-3">ব্যবহারকারী হইসেস</h1>
        <span class="badge bg-primary rounded-pill mt-2"><?= $total ?></span>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/users/create" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> নতুন ব্যবহারকারী
        </a>
    </div>
</div>

<nav class="mb-3">
    <form method="GET" action="<?= BASE_URL ?>/admin/users" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="সার্চ করুন..." value="<?= htmlspecialchars($search ?? '') ?>">
        <button type="submit" class="btn btn-outline-primary">সার্চ</button>
        <?php if ($search): ?>
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary ms-1">রিসেট</a>
        <?php endif; ?>
    </form>
</nav>

<?php flash('success'); flash('error'); ?>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>নাম</th>
                    <th>ইমেইল</th>
                    <th>রোল</th>
                    <th>স্ট্যাটাস</th>
                    <th>তৈরি</th>
                    <th>অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">কোন ব্যবহারকারী পাওয়া যায়নি।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($user->name) ?></td>
                            <td><?= htmlspecialchars($user->email) ?></td>
                            <td>
                                <span class="badge bg-<?= $user->role === 'admin' ? 'danger' : 'secondary' ?>">
                                    <?= htmlspecialchars($user->role) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $user->status === 'active' ? 'success' : 'warning' ?>">
                                    <?= $user->status === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়' ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($user->created_at)) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= BASE_URL ?>/admin/users/edit/<?= $user->id ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user->id != $_SESSION['user_id']): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/users/delete/<?= $user->id ?>" class="d-inline" onsubmit="return confirm('নিশ্চিত?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($total > $perPage): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <?php $totalPages = ceil($total / $perPage); ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/admin/users?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>