<?php
/**
 * 500 Internal Server Error Page
 * Intelligent layout detection (Admin vs Public)
 */

$is_admin_path = str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin');
$is_logged_in = isset($_SESSION['user_id']);

if ($is_admin_path && $is_logged_in && defined('VIEW_PATH') && file_exists(VIEW_PATH . '/layouts/admin.php')) {
    $title = "500 Server Error — NovaFlow";
    $content = '
    <div class="text-center py-5">
        <div class="display-1 fw-bold text-danger mb-4"><i class="fas fa-bug"></i> 500</div>
        <h2 class="fw-bold text-dark">সার্ভার এরর ঘটেছে!</h2>
        <p class="text-muted mb-4">একটি অপ্রত্যাশিত ত্রুটি ঘটেছে। আমাদের কারিগরি টিম বিষয়টি খতিয়ে দেখছে।</p>
        <a href="' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/dashboard" class="btn btn-danger px-4 py-2 rounded-pill">
            <i class="fas fa-home me-2"></i> ড্যাশবোর্ডে ফিরে যান
        </a>
    </div>';
    
    require_once VIEW_PATH . '/layouts/admin.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Internal Server Error | NovaFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Hind Siliguri', 'Outfit', sans-serif; background: #0b0f19; color: #f1f5f9; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .error-card { background: #111827; padding: 50px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); text-align: center; max-width: 500px; width: 90%; border: 1px solid rgba(255,255,255,0.08); }
        .error-code { font-size: 80px; font-weight: 700; color: #ef4444; line-height: 1; margin-bottom: 20px; text-shadow: 0 0 20px rgba(239, 68, 68, 0.4); }
    </style>
</head>
<body>

<div class="error-card">
    <div class="error-code"><i class="fas fa-exclamation-triangle"></i> 500</div>
    <h2 class="fw-bold mb-3">সার্ভার সাময়িক ত্রুটি!</h2>
    <p class="text-secondary mb-4">দুঃখিত, একটি অনাকাঙ্ক্ষিত সার্ভার সমস্যা দেখা দিয়েছে। কিছু সময় পর পুনরায় চেষ্টা করুন।</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-danger px-4 py-2 rounded-pill fw-semibold">হোম পেজে ফিরে যান</a>
</div>

</body>
</html>
