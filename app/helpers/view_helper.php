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
if (!function_exists('initTemplateEngine')) {
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
}

if (!function_exists('template_view')) {
    /**
     * টেমপ্লেট ভিউ রেন্ডার করা
     */
    function template_view($template, $data = [])
    {
        $engine = initTemplateEngine();
        return $engine->render($template, $data);
    }
}

if (!function_exists('view')) {
    /**
     * ভিউ রেন্ডার করা
     */
    function view($template, $data = [])
    {
        return template_view($template, $data);
    }
}

if (!function_exists('display_view')) {
    /**
     * ভিউ সরাসরি আউটপুট করা
     */
    function display_view($template, $data = [])
    {
        $engine = initTemplateEngine();
        $engine->display($template, $data);
    }
}

if (!function_exists('clear_view_cache')) {
    /**
     * ভিউ ক্যাশ ক্লিয়ার করা
     */
    function clear_view_cache()
    {
        $engine = initTemplateEngine();
        $engine->clearCache();
    }
}
