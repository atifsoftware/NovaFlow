<div class="row g-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #2e7d32, #43a047); color: white;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-uppercase small fw-bold opacity-75 mb-1">মোট ইউজার</h6>
                    <h2 class="mb-0 fw-bold"><?= $stats['users'] ?? 0 ?></h2>
                </div>
                <div class="fs-1 opacity-25"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #1565c0, #1e88e5); color: white;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-uppercase small fw-bold opacity-75 mb-1">সার্ভার স্ট্যাটাস</h6>
                    <h2 class="mb-0 fw-bold">Active</h2>
                </div>
                <div class="fs-1 opacity-25"><i class="fas fa-server"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #e65100, #ff9800); color: white;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-uppercase small fw-bold opacity-75 mb-1">PHP ভার্সন</h6>
                    <h2 class="mb-0 fw-bold"><?= $stats['php_version'] ?></h2>
                </div>
                <div class="fs-1 opacity-25"><i class="fab fa-php"></i></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #4527a0, #673ab7); color: white;">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-uppercase small fw-bold opacity-75 mb-1">অনলাইন ইউজার</h6>
                    <h2 class="mb-0 fw-bold"><?= $stats['online'] ?></h2>
                </div>
                <div class="fs-1 opacity-25"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-4">NovaFlow স্বাগতম!</h5>
            <p class="text-muted">আপনার ফ্রেমওয়ার্ক এখন পুরোপুরি প্রস্তুত। এই প্রিমিয়াম অ্যাডমিন প্যানেলটি ব্যবহার করে আপনি আপনার প্রজেক্টের সব রিসোর্স সহজেই নিয়ন্ত্রণ করতে পারবেন।</p>
            <hr class="opacity-10">
            <div class="d-flex gap-2 mt-3">
                <a href="<?= BASE_URL ?>/admin/settings" class="btn btn-primary rounded-pill px-4">সাইট সেটিংস</a>
                <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary rounded-pill px-4" target="_blank">ওয়েবসাইট দেখুন</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h6 class="fw-bold mb-3">সিস্টেম ইনফো</h6>
            <div class="small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">আর্কিটেকচার</span>
                    <span class="fw-semibold">MVC (PSR-4)</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">ডিআই কন্টেইনার</span>
                    <span class="text-success fw-semibold">ব্যাস ইমপ্লিমেন্টেড</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">মিডলওয়্যার</span>
                    <span class="text-success fw-semibold">অ্যাক্টিভ</span>
                </div>
            </div>
        </div>
    </div>
</div>
