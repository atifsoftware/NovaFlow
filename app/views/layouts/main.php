<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'NovaFlow PHP Framework' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Hind Siliguri', 'Inter', sans-serif; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3" href="<?= BASE_URL ?>/">Nova<span class="text-danger">Flow</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link fw-semibold px-3" href="<?= BASE_URL ?>/">হোম</a></li>
                    <li class="nav-item"><a class="nav-link fw-semibold px-3" href="<?= BASE_URL ?>/docs">ডকুমেন্টেশন</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-danger rounded-pill px-4 fw-bold">লগইন</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <?= $content ?>
    </main>

    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container text-center">
            <h4 class="fw-bold mb-3">NovaFlow</h4>
            <p class="text-white-50 small mb-4">A Premium modern PHP MVC Framework for professional developers.</p>
            <div class="d-flex justify-content-center gap-3 mb-4">
                <a href="#" class="text-white opacity-50"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white opacity-50"><i class="fab fa-github"></i></a>
                <a href="#" class="text-white opacity-50"><i class="fab fa-youtube"></i></a>
            </div>
            <p class="small text-white-50 mb-0">&copy; <?= date('Y') ?> NovaFlow Framework. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
