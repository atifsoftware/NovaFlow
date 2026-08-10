<?php

use App\Core\TemplateEngine;

/**
 * NovaFlow View Helper
 * কন্ট্রোলার থেকে ভিউ রেন্ডার করার জন্য হেল্পার ফাংশন
 */

// গ্লোবাল ভিউ ইনস্ট্যান্স
$templateEngine = null;

/**
 * টেমপ্লেট ইঞ্জিন ইনিশিয়ালাইজ করা
 */
function initTemplateEngine()
{
    global $templateEngine;
    
    if ($templateEngine === null) {
        $templatePath = __DIR__ . '/../views';
        $cachePath = __DIR__ . '/../storage/framework/views';
        
        $templateEngine = new TemplateEngine($templatePath, $cachePath);
    }
    
    return $templateEngine;
}

/**
 * ভিউ রেন্ডার করা
 * 
 * @param string $template টেমপ্লেট নাম (যেমন: 'home', 'admin/dashboard')
 * @param array $data ভিউতে পাস করার ডেটা
 * @return string রেন্ডারড HTML
 */
function view($template, $data = [])
{
    $engine = initTemplateEngine();
    return $engine->render($template, $data);
}

/**
 * ভিউ সরাসরি আউটপুট করা
 * 
 * @param string $template টেমপ্লেট নাম
 * @param array $data ভিউতে পাস করার ডেটা
 */
function display_view($template, $data = [])
{
    $engine = initTemplateEngine();
    $engine->display($template, $data);
}

/**
 * ভিউ ক্যাশ ক্লিয়ার করা
 */
function clear_view_cache()
{
    $engine = initTemplateEngine();
    $engine->clearCache();
}
