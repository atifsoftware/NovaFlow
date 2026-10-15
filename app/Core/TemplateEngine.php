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

        $template = str_replace('.', '/', $template);
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
        $output = ob_get_clean();

        // যদি প্যারেন্ট টেমপ্লেট থাকে, সেটি রেন্ডার করুন
        if ($this->parentTemplate) {
            $parent = $this->parentTemplate;
            $this->parentTemplate = null;
            return $this->render($parent, $this->data);
        }

        return $output;
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
        // Compile @extends
        $content = preg_replace('/@extends\([\'"]([^\'"]+)[\'"]\)/', '<?php $this->extend("$1"); ?>', $content);

        // Compile @section
        $content = preg_replace('/@section\([\'"]([^\'"]+)[\'"]\)/', '<?php $this->startSection("$1"); ?>', $content);

        // Compile @endsection
        $content = preg_replace('/@endsection/', '<?php $this->stopSection(); ?>', $content);

        $content = preg_replace_callback(
            '/@yield\([\'"]([^\'"]+)[\'"]\)/',
            function($matches) {
                $sectionName = $matches[1];
                return '<?php echo $this->yieldContent("' . $sectionName . '"); ?>';
            },
            $content
        );

        // {!! $variable !!} - রা আউটপুট (HTML অ্যালো করা)
        $content = preg_replace('/\{\!\!\s*(.+?)\s*\!\!\}/', '<?php echo $1; ?>', $content);

        // {{ $variable }} - এসকেপড আউটপুট (Allows expressions like $user->name, $data['key'])
        $content = preg_replace_callback(
            '/\{\{\s*(.+?)\s*\}\}/',
            function($matches) {
                $expr = $matches[1];
                return "<?php echo htmlspecialchars((string)({$expr}), ENT_QUOTES, 'UTF-8'); ?>";
            },
            $content
        );

        // @if, @else, @endif
        $content = preg_replace('/@if\((.+?)\)/', '<?php if($1): ?>', $content);
        $content = preg_replace('/@elseif\((.+?)\)/', '<?php elseif($1): ?>', $content);
        $content = preg_replace('/@else/', '<?php else: ?>', $content);
        $content = preg_replace('/@endif/', '<?php endif; ?>', $content);

        // @foreach, @endforeach
        $content = preg_replace('/@foreach\((.+?)\s+as\s+(.+?)\)/', '<?php foreach($1 as $2): ?>', $content);
        $content = preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);

        // @for, @endfor
        $content = preg_replace('/@for\((.+?)\)/', '<?php for($1): ?>', $content);
        $content = preg_replace('/@endfor/', '<?php endfor; ?>', $content);

        // @while, @endwhile
        $content = preg_replace('/@while\((.+?)\)/', '<?php while($1): ?>', $content);
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
        $content = preg_replace('/@php(.*?)@endphp/s', '<?php $1 ?>', $content);

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
     * সেকশনের কন্টেন্ট ফিরিয়ে দেওয়া
     */
    public function yieldContent($name, $default = '')
    {
        return $this->sections[$name] ?? $default;
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
