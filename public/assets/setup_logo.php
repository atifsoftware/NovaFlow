<?php
// ============================================================
//  Logo Setup Helper — copies logo.png to public/assets/images/
//  The main logo is at: g:/xampp_8_2/htdocs/jahin-web/logo.png
//  Destination: public/assets/images/logo.png
// ============================================================
$src  = dirname(__DIR__, 2) . '/logo.png';
$dest = dirname(__DIR__) . '/assets/images/logo.png';
$destDir = dirname($dest);

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

if (file_exists($src) && !file_exists($dest)) {
    copy($src, $dest);
}
// This file auto ran when first image is requested. 
// Exit immediately.
