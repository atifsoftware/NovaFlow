<?php
/**
 * Enhanced Intelligent Error Handler & Suggestion System (V2)
 * Detects syntax errors, runtime errors, and provides intelligent solutions
 * with code analysis and similar variable/function name suggestions.
 * 
 * Adapted for NovaFlow MVC.
 */

namespace NovaFlow\Core;

class IntelligentErrorHandler
{
    protected static $logFile = \BASE_PATH . '/logs/intelligent_error.log';
    protected static $syntaxCheckEnabled = true;
    protected static $codeAnalysisEnabled = true;
    protected static $autoFixEnabled = false;

    // Files to exclude from syntax checking
    protected static $excludePatterns = [
        '/vendor/',
        '/node_modules/',
        '/cache/',
        '/logs/',
        '/tmp/',
        '/temp/'
    ];

    public static function register()
    {
        // Register error handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        // Enable syntax checking for included files
        if (self::$syntaxCheckEnabled) {
            spl_autoload_register([self::class, 'checkSyntaxOnLoad']);
        }

        // Check syntax of main files on startup
        self::checkSyntaxOnStartup();
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        $error = [
            'type' => self::getErrorType($errno),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            'severity' => self::getErrorSeverity($errno)
        ];

        self::logError($error);

        if (self::isApiRequest()) {
            if ($error['severity'] === 'critical' || $error['severity'] === 'high') {
                self::sendJsonError($error);
                exit; // Stop execution on critical/high API errors
            }
            return true; // Suppress non-critical errors (notices/warnings) for APIs
        }

        self::analyzeAndSuggest($error);

        // Don't suppress errors in development for web requests
        return false;
    }

    protected static function isApiRequest()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return (
            strpos($uri, '/api/') !== false ||
            strpos($uri, 'api/docs') !== false ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        );
    }

    protected static function sendJsonError($error)
    {
        if (ob_get_length()) ob_clean(); // Clear any previous output

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'success' => false,
            'status'  => 'error',
            'error' => [
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => basename($error['file']),
                'line' => $error['line']
            ]
        ]);
    }

    public static function handleException($exception)
    {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'severity' => 'high',
            'exception_class' => get_class($exception)
        ];

        self::logError($error);

        if (self::isApiRequest()) {
            self::sendJsonError($error);
            exit;
        }

        self::analyzeAndSuggest($error);
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            $error['severity'] = 'critical';
            self::logError($error);

            if (self::isApiRequest()) {
                self::sendJsonError($error);
                return;
            }

            self::analyzeAndSuggest($error);
        }
    }

    protected static function checkSyntaxOnLoad($className)
    {
        // Convert class name to file path
        $file = self::classToFile($className);
        if ($file && file_exists($file)) {
            self::checkFileSyntax($file);
        }
    }

    protected static function checkSyntaxOnStartup()
    {
        // Check main NovaFlow application files
        $mainFiles = [
            \BASE_PATH . '/config/config.php',
            \BASE_PATH . '/app/bootstrap.php',
            \BASE_PATH . '/config/routes.php',
        ];

        foreach ($mainFiles as $file) {
            if (file_exists($file)) {
                self::checkFileSyntax($file);
            }
        }
    }

    protected static function checkFileSyntax($file)
    {
        // Skip excluded patterns
        foreach (self::$excludePatterns as $pattern) {
            if (strpos($file, $pattern) !== false) {
                return;
            }
        }

        // Only check PHP files
        if (!preg_match('/\.php$/i', $file)) {
            return;
        }

        // Use pure PHP syntax checking without shell commands
        $result = self::checkSyntaxWithTokenizer($file);

        if ($result !== true) {
            $error = [
                'type' => 'Syntax Error',
                'message' => $result,
                'file' => $file,
                'line' => 0,
                'severity' => 'critical'
            ];
            self::logError($error);
            self::analyzeAndSuggest($error);
        }
    }

    protected static function analyzeAndSuggest($error)
    {
        $analysis = self::analyzeError($error);
        $suggestions = self::getSuggestions($error, $analysis);

        self::displayError($error, $analysis, $suggestions);

        // Attempt auto-fix if enabled
        if (self::$autoFixEnabled && isset($analysis['auto_fix'])) {
            self::attemptAutoFix($error, $analysis);
        }
    }

    protected static function analyzeError($error)
    {
        $analysis = [
            'category' => self::categorizeError($error),
            'possible_causes' => [],
            'code_context' => null,
            'auto_fix' => null
        ];

        // Get code context
        if (isset($error['file']) && file_exists($error['file'])) {
            $analysis['code_context'] = self::getCodeContext($error['file'], $error['line'] ?? 0);
        }

        // Analyze based on error type and message
        $message = strtolower($error['message']);

        // Database errors
        if (strpos($message, 'mysql') !== false || strpos($message, 'pdo') !== false) {
            $analysis['category'] = 'database';
            $analysis['possible_causes'] = [
                'Database connection failed',
                'Invalid SQL query',
                'Missing table or column',
                'Incorrect credentials'
            ];
        }

        // Variable errors
        elseif (strpos($message, 'undefined variable') !== false) {
            $analysis['category'] = 'variable';
            $analysis['possible_causes'] = [
                'Variable not declared',
                'Typo in variable name',
                'Variable scope issue',
            ];

            // Try to suggest variable name fix
            if (preg_match('/undefined variable:?\s*\$?(\w+)/i', $error['message'], $matches)) {
                $varName = $matches[1];
                $similar = self::findSimilarVariables($error['file'], $varName);
                if (!empty($similar)) {
                    $analysis['auto_fix'] = [
                        'type' => 'variable_name',
                        'suggestion' => "Did you mean: " . implode(', ', $similar) . "?",
                        'similar_vars' => $similar
                    ];
                }
            }
        }

        // Function errors
        elseif (strpos($message, 'undefined function') !== false) {
            $analysis['category'] = 'function';
            $analysis['possible_causes'] = [
                'Function not defined',
                'Missing include/require',
                'Typo in function name',
            ];

            // Try to suggest function name fix
            if (preg_match('/undefined function:?\s*(\w+)/i', $error['message'], $matches)) {
                $funcName = $matches[1];
                $similar = self::findSimilarFunctions($funcName);
                if (!empty($similar)) {
                    $analysis['auto_fix'] = [
                        'type' => 'function_name',
                        'suggestion' => "Did you mean: " . implode(', ', $similar) . "?",
                        'similar_funcs' => $similar
                    ];
                }
            }
        }

        return $analysis;
    }

    protected static function getSuggestions($error, $analysis)
    {
        $suggestions = [];

        // Basic suggestions based on error message
        $basicSuggestions = self::getBasicSuggestions($error['message']);
        if (!empty($basicSuggestions)) {
            $suggestions = array_merge($suggestions, $basicSuggestions);
        }

        // Category-specific suggestions
        switch ($analysis['category']) {
            case 'database':
                $suggestions[] = "Check database credentials in config/database.php";
                $suggestions[] = "Verify table and column names exist in your database";
                break;
            case 'variable':
                $suggestions[] = "Declare variables before use or check spelling";
                break;
            case 'syntax':
                $suggestions[] = "Check for missing semicolons (;) or unclosed brackets";
                break;
        }

        // Code analysis suggestions
        if (self::$codeAnalysisEnabled && $analysis['code_context']) {
            $codeSuggestions = self::analyzeCode($error, $analysis);
            if (!empty($codeSuggestions)) {
                $suggestions = array_merge($suggestions, $codeSuggestions);
            }
        }

        return array_unique($suggestions);
    }

    protected static function getBasicSuggestions($message)
    {
        $suggestions = [
            'mysql' => 'Check database connection and credentials',
            'pdo' => 'Verify PDO configuration',
            'undefined variable' => 'Declare variable before use or check spelling',
            'undefined index' => 'Check if array key exists using isset()',
            'undefined function' => 'Include required files or check function name',
            'class not found' => 'Check namespace and autoloader configuration',
            'syntax error' => 'Check PHP syntax: missing brackets/quotes/semicolons',
        ];

        $result = [];
        $lowerMessage = strtolower($message);

        foreach ($suggestions as $key => $suggestion) {
            if (strpos($lowerMessage, $key) !== false) {
                $result[] = $suggestion;
            }
        }

        return $result;
    }

    protected static function analyzeCode($error, $analysis)
    {
        $suggestions = [];
        if (!$analysis['code_context']) return $suggestions;

        $code = $analysis['code_context'];
        
        // Check for missing semicolons
        if (!preg_match('/;\s*$/', $code)) {
            $suggestions[] = "Missing semicolon (;) at the end of the line";
        }

        // Check for unclosed brackets
        $openBrackets = substr_count($code, '(');
        $closeBrackets = substr_count($code, ')');
        if ($openBrackets > $closeBrackets) {
            $suggestions[] = "Unclosed parenthesis ( detected";
        }

        // Check quotes
        $singleQuotes = substr_count($code, "'");
        if ($singleQuotes % 2 !== 0) {
            $suggestions[] = "Unclosed single quote ' detected";
        }

        return $suggestions;
    }

    protected static function findSimilarVariables($file, $varName)
    {
        if (!file_exists($file)) return [];
        $content = file_get_contents($file);
        preg_match_all('/\$(\w+)/', $content, $matches);
        $variables = array_unique($matches[1]);
        $similar = [];
        foreach ($variables as $var) {
            $distance = levenshtein($varName, $var);
            if ($distance <= 2 && $distance > 0) {
                $similar[] = '$' . $var;
            }
        }
        return $similar;
    }

    protected static function findSimilarFunctions($funcName)
    {
        $functions = get_defined_functions();
        $allFunctions = array_merge($functions['internal'], $functions['user']);
        $similar = [];
        foreach ($allFunctions as $func) {
            $distance = levenshtein($funcName, $func);
            if ($distance <= 2 && $distance > 0) {
                $similar[] = $func;
            }
        }
        return array_slice($similar, 0, 5); 
    }

    protected static function getCodeContext($file, $line, $contextLines = 3)
    {
        if (!file_exists($file)) return null;
        $lines = file($file);
        $start = max(0, $line - $contextLines - 1);
        $end = min(count($lines), $line + $contextLines);

        $context = [];
        for ($i = $start; $i < $end; $i++) {
            $marker = ($i + 1 == $line) ? ' >>> ' : '     ';
            $context[] = $marker . ($i + 1) . ': ' . rtrim($lines[$i]);
        }
        return implode("\n", $context);
    }

    protected static function displayError($error, $analysis, $suggestions)
    {
        if (php_sapi_name() === 'cli' || headers_sent()) return;

        $severityColors = [
            'low' => '#fff3cd',
            'medium' => '#ffeaa7',
            'high' => '#fab1a0',
            'critical' => '#e17055'
        ];
        $color = $severityColors[$error['severity']] ?? '#fab1a0';

        $links = self::getSearchLinks($error);

        echo "<!-- NovaFlow Intelligent Error View -->";
        echo "<div style='background:{$color};padding:25px;border:3px solid #d63031;border-radius:12px;margin:30px;font-family:monospace;box-shadow: 0 10px 25px rgba(0,0,0,0.2);color:#2d3436;'>\n";
        
        echo "<div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;'>\n";
        echo "<h2 style='margin:0;color:#d63031;font-size:24px;'>🚨 " . ($error['type'] ?? 'Error') . " Detected</h2>";
        echo "<div>";
        echo "<a href='{$links['google']}' target='_blank' style='margin-right:10px;text-decoration:none;background:#4285F4;color:white;padding:5px 12px;border-radius:4px;font-size:12px;'>🔍 Google</a>";
        echo "<a href='{$links['stackoverflow']}' target='_blank' style='text-decoration:none;background:#F48024;color:white;padding:5px 12px;border-radius:4px;font-size:12px;'>🥞 StackOverflow</a>";
        echo "</div>";
        echo "</div>\n";

        echo "<div style='margin-bottom:20px;font-size:16px;'>";
        echo "<strong>Message:</strong> {$error['message']}<br>";
        echo "<strong>File:</strong> {$error['file']} (Line {$error['line']})";
        echo "</div>";

        if ($analysis['code_context']) {
            echo "<div style='background:#2d3436;color:#dfe0e0;padding:20px;border-radius:8px;border-left:8px solid #d63031;overflow:auto;'>";
            echo "<strong style='color:#fab1a0;'>Code Context:</strong><pre style='margin:10px 0;font-size:14px;'>{$analysis['code_context']}</pre>";
            echo "</div><br>";
        }

        if (!empty($suggestions) || isset($analysis['auto_fix'])) {
            echo "<div style='background:#f1f9f1;padding:20px;border-radius:8px;border-left:8px solid #28a745;'>";
            echo "<strong style='color:#1e7e34;font-size:18px;'>💡 Intelligent Suggestions:</strong><ul style='margin:10px 0;padding-left:20px;'>";
            
            if (isset($analysis['auto_fix'])) {
                echo "<li style='color:#d63031;font-weight:bold;margin-bottom:10px;'>🚀 " . $analysis['auto_fix']['suggestion'] . "</li>";
            }

            foreach ($suggestions as $s) echo "<li>$s</li>";
            echo "</ul></div>";
        }

        // Stack Trace
        if (isset($error['trace']) && count($error['trace']) > 0) {
            echo "<details style='margin-top:20px;'>";
            echo "<summary style='cursor:pointer;font-weight:bold;color:#444;'>🔍 Stack Trace</summary>";
            echo "<pre style='background:rgba(255,255,255,0.5);padding:15px;margin:5px 0;font-size:12px;max-height:250px;overflow:auto;border:1px solid rgba(0,0,0,0.1);'>";
            foreach ($error['trace'] as $i => $frame) {
                echo "#$i " . (isset($frame['file']) ? "{$frame['file']}({$frame['line']}): " : "") . (isset($frame['class']) ? "{$frame['class']}{$frame['type']}" : "") . "{$frame['function']}()\n";
            }
            echo "</pre></details>";
        }

        self::displayEnvironment();

        echo "</div>";
    }

    protected static function getSearchLinks($error)
    {
        $query = urlencode('PHP ' . $error['message']);
        return [
            'google' => "https://www.google.com/search?q=$query",
            'stackoverflow' => "https://stackoverflow.com/search?q=$query"
        ];
    }

    protected static function displayEnvironment()
    {
        echo "<div style='margin-top:25px;border-top:1px solid rgba(0,0,0,0.1);padding-top:15px;'>";
        echo "<strong style='display:block;margin-bottom:10px;font-size:14px;'>🌍 Environment Snapshot</strong>";
        
        self::displayEnvSection('$_GET', $_GET);
        self::displayEnvSection('$_POST', $_POST);
        self::displayEnvSection('$_SESSION', $_SESSION ?? []);
        self::displayEnvSection('$_SERVER', $_SERVER, true);
        
        echo "</div>";
    }

    protected static function displayEnvSection($title, $data, $mask = false)
    {
        if (empty($data)) return;
        echo "<details style='margin-bottom:8px;'>";
        echo "<summary style='cursor:pointer;background:rgba(255,255,255,0.3);padding:8px;border-radius:4px;font-size:13px;'>$title (" . count($data) . ")</summary>";
        echo "<div style='padding:10px;background:#fff;border:1px solid #eee;margin-top:4px;max-height:200px;overflow:auto;'>";
        echo "<table style='width:100%;border-collapse:collapse;font-size:12px;'>";
        foreach ($data as $key => $val) {
            $isSensitive = $mask && preg_match('/(pass|token|key|secret|auth)/i', $key);
            $valClean = $isSensitive ? '******** (Masked)' : (is_array($val) ? 'Array' : htmlspecialchars((string)$val));
            echo "<tr><td style='padding:4px;border-bottom:1px solid #f9f9f9;width:30%;color:#666;'>$key</td>";
            echo "<td style='padding:4px;border-bottom:1px solid #f9f9f9;font-family:monospace;word-break:break-all;'>$valClean</td></tr>";
        }
        echo "</table></div></details>";
    }

    protected static function logError($error)
    {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
        $msg = "[" . date('Y-m-d H:i:s') . "] [" . $error['severity'] . "] " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . "\n";
        file_put_contents(self::$logFile, $msg, FILE_APPEND);
    }

    protected static function getErrorType($errno)
    {
        $types = [E_ERROR => 'Fatal Error', E_WARNING => 'Warning', E_PARSE => 'Parse Error', E_NOTICE => 'Notice', E_USER_ERROR => 'User Error'];
        return $types[$errno] ?? 'Error';
    }

    protected static function getErrorSeverity($errno)
    {
        return ($errno & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR)) ? 'critical' : 'medium';
    }

    protected static function categorizeError($error)
    {
        $message = strtolower($error['message']);
        if (strpos($message, 'mysql') !== false || strpos($message, 'pdo') !== false) return 'database';
        if (strpos($message, 'syntax') !== false || strpos($message, 'parse') !== false) return 'syntax';
        if (strpos($message, 'variable') !== false) return 'variable';
        return 'general';
    }

    protected static function classToFile($className)
    {
        $file = str_replace(['NovaFlow\\Core\\', 'App\\', '\\'], ['', '', '/'], $className) . '.php';
        $possiblePaths = [
            \BASE_PATH . '/app/libraries/' . $file,
            \BASE_PATH . '/app/controllers/' . $file,
            \BASE_PATH . '/app/models/' . $file,
            \BASE_PATH . '/app/services/' . $file,
        ];
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) return $path;
        }
        return null;
    }

    protected static function checkSyntaxWithTokenizer($file)
    {
        try {
            $source = file_get_contents($file);
            token_get_all($source);
        } catch (\ParseError $e) {
            return $e->getMessage();
        }
        return true;
    }

    protected static function attemptAutoFix($error, $analysis)
    {
        // For visual display only in this version
    }
}
