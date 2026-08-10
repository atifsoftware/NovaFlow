<?php

namespace App\Core;

/**
 * NovaFlow Template Engine
 * একটি হালকা, দ্রুত এবং নিরাপদ টেমপ্লেট ইঞ্জিন
 * 
 * ফিচার:
 * - ক্যাশিং সাপোর্ট (পারফরম্যান্সের জন্য)
 * - টেমপ্লেট ইনহেরিটেন্স (@extends, @section, @yield)
 * - ডেটা এসকেপিং (XSS প্রতিরোধ)
 * - সহজ সিনট্যাক্স
 */
class TemplateEngine
{
    private $templatePath;
    private $cachePath;
    private $data = [];
    private $sections = [];
    private $yieldSection = null;
    private $parentTemplate = null;

    public function __construct($templatePath, $cachePath)
    {
        $this->templatePath = rtrim($templatePath, '/');
        $this->cachePath = rtrim($cachePath, '/');
        
        // ক্যাশ ডিরেক্টরি তৈরি করা
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
    }

    /**
     * ভিউতে ডেটা পাস করা
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * ভিউ রেন্ডার করা
     */
    public function render($template, $data = [])
    {
        // ডেটা মার্জ করা
        if (!empty($data)) {
            $this->with($data);
        }

        $templateFile = $this->templatePath . '/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new \Exception("Template not found: {$templateFile}");
        }

        // ক্যাশ ফাইল নাম তৈরি
        $cacheFile = $this->getCacheFile($templateFile);

        // ক্যাশ এক্সপায়ারড বা না থাকলে রিকম্পাইল করুন
        if (!file_exists($cacheFile) || filemtime($templateFile) > filemtime($cacheFile)) {
            $compiledContent = $this->compile(file_get_contents($templateFile));
            file_put_contents($cacheFile, $compiledContent);
        }

        // ক্যাশড ফাইল থেকে আউটপুট
        extract($this->data);
        ob_start();
        include $cacheFile;
        return ob_get_clean();
    }

    /**
     * সরাসরি আউটপুট করা
     */
    public function display($template, $data = [])
    {
        echo $this->render($template, $data);
    }

    /**
     * টেমপ্লেট কম্পাইল করা
     */
    private function compile($content)
    {
        // @extends('layout')
        $content = preg_replace_callback(
            '/@extends\([\'"]([^\'"]+)[\'"]\)/',
            function($matches) {
                $this->parentTemplate = $matches[1];
                return '';
            },
            $content
        );

        // @section('name')
        $content = preg_replace_callback(
            '/@section\([\'"]([^\'"]+)[\'"]\)(.*?)@endsection/s',
            function($matches) {
                $this->sections[$matches[1]] = $matches[2];
                return '';
            },
            $content
        );

        // @yield('section')
        $content = preg_replace_callback(
            '/@yield\([\'"]([^\'"]+)[\'"]\)/',
            function($matches) {
                $sectionName = $matches[1];
                return "<?php echo isset(\$this->sections['{$sectionName}']) ? \$this->sections['{$sectionName}'] : ''; ?>";
            },
            $content
        );

        // {{ $variable }} - এসকেপড আউটপুট
        $content = preg_replace_callback(
            '/\{\{\s*(\$?[a-zA-Z0-9_]+)\s*\}\}/',
            function($matches) {
                $var = $matches[1];
                if (strpos($var, '$') !== 0) {
                    $var = '$' . $var;
                }
                return "<?php echo htmlspecialchars({$var}, ENT_QUOTES, 'UTF-8'); ?>";
            },
            $content
        );

        // {!! $variable !!} - রা আউটপুট (HTML অ্যালো করা)
        $content = preg_replace_callback(
            '/\{\!\!\s*(\$?[a-zA-Z0-9_]+)\s*\!\!\}/',
            function($matches) {
                $var = $matches[1];
                if (strpos($var, '$') !== 0) {
                    $var = '$' . $var;
                }
                return "<?php echo {$var}; ?>";
            },
            $content
        );

        // @if, @else, @endif
        $content = preg_replace('/@if\((.+?)\)/', '<?php if(\1): ?>', $content);
        $content = preg_replace('/@elseif\((.+?)\)/', '<?php elseif(\1): ?>', $content);
        $content = preg_replace('/@else/', '<?php else: ?>', $content);
        $content = preg_replace('/@endif/', '<?php endif; ?>', $content);

        // @foreach, @endforeach
        $content = preg_replace('/@foreach\((.+?)\s+as\s+(.+?)\)/', '<?php foreach(\1 as \2): ?>', $content);
        $content = preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);

        // @for, @endfor
        $content = preg_replace('/@for\((.+?)\)/', '<?php for(\1): ?>', $content);
        $content = preg_replace('/@endfor/', '<?php endfor; ?>', $content);

        // @while, @endwhile
        $content = preg_replace('/@while\((.+?)\)/', '<?php while(\1): ?>', $content);
        $content = preg_replace('/@endwhile/', '<?php endwhile; ?>', $content);

        // @include('template')
        $content = preg_replace_callback(
            '/@include\([\'"]([^\'"]+)[\'"]\)/',
            function($matches) {
                $includeTemplate = $matches[1];
                return "<?php echo \$this->render('{$includeTemplate}', \$this->data); ?>";
            },
            $content
        );

        // @php ... @endphp
        $content = preg_replace('/@php(.*?)@endphp/s', '<?php \1 ?>', $content);

        // কমেন্ট <!-- {{-- comment --}} -->
        $content = preg_replace('/\{\{\-\-\s*(.*?)\s*\-\-\}\}/s', '', $content);

        return $content;
    }

    /**
     * ক্যাশ ফাইলের পথ তৈরি করা
     */
    private function getCacheFile($templateFile)
    {
        $hash = md5($templateFile);
        return $this->cachePath . '/' . $hash . '.php';
    }

    /**
     * পেয়ারেন্ট টেমপ্লেট লোড করা
     */
    public function extend($parentTemplate)
    {
        $this->parentTemplate = $parentTemplate;
    }

    /**
     * সেকশন সেট করা
     */
    public function startSection($name)
    {
        ob_start();
        $this->yieldSection = $name;
    }

    /**
     * সেকশন শেষ করা
     */
    public function stopSection()
    {
        if ($this->yieldSection) {
            $this->sections[$this->yieldSection] = ob_get_clean();
            $this->yieldSection = null;
        }
    }

    /**
     * ক্লিয়ার ক্যাশ
     */
    public function clearCache()
    {
        $files = glob($this->cachePath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
