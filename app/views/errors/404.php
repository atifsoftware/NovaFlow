<?php
/**
 * Intelligent 404 Error Page
 * Automatically detects context (Admin vs Site)
 */

$is_admin_path = str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin');
$is_logged_in = isset($_SESSION['user_id']);

// If we are in admin area and logged in, show inside admin layout
if ($is_admin_path && $is_logged_in) {
    $title = "404 Not Found — NovaFlow";
    $content = '
    <div class="text-center py-5">
        <div class="display-1 fw-bold text-danger mb-4"><i class="fas fa-exclamation-triangle"></i> 404</div>
        <h2 class="fw-bold text-dark">পেজটি পাওয়া যায়নি!</h2>
        <p class="text-muted mb-4">দুঃখিত, আপনি যে ফাইল বা পেজটি খুঁজছেন তা আমাদের সিস্টেমে নেই।</p>
        <a href="' . BASE_URL . '/admin/dashboard" class="btn btn-danger px-4 py-2 rounded-pill">
            <i class="fas fa-home me-2"></i> ড্যাশবোর্ডে ফিরে যান
        </a>
    </div>';
    
    require_once VIEW_PATH . '/layouts/admin.php';
    exit;
}

// Fallback for standard 404
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found | NovaFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { background: white; padding: 50px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; max-width: 500px; width: 100%; }
        .error-code { font-size: 80px; font-weight: 700; color: #ef4444; line-height: 1; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="error-card">
    <div class="error-code">404</div>
    <h2 class="fw-bold mb-3">পেজটি পাওয়া যায়নি!</h2>
    <p class="text-muted mb-4">দুঃখিত, আপনি ভুল পথে চলে এসেছেন। অনুগ্রহ করে নীচের বাটনে ক্লিক করে মূল পাতায় ফিরে যান।</p>
    <a href="<?= BASE_URL ?>/" class="btn btn-danger px-4 py-2 rounded-pill">হোম পেজে ফিরে যান</a>
</div>

</body>
</html>
