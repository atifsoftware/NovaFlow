<?php

namespace NovaFlow\Core\Console;

use Throwable;

/**
 * NovaFlow Interactive Tinker Shell (REPL)
 * Allows developers to interact with models, database, cache, and framework components directly.
 */
class Tinker
{
    private bool $running = true;

    /**
     * Start the REPL loop
     */
    public function start(): void
    {
        $this->printBanner();

        while ($this->running) {
            echo "\033[36mnovaflow>\033[0m ";
            $input = fgets(STDIN);

            if ($input === false) {
                break;
            }

            $input = trim($input);

            if ($input === '') {
                continue;
            }

            if (in_array(strtolower($input), ['exit', 'quit', 'q'])) {
                echo "\033[32mExiting NovaFlow Tinker. Goodbye!\033[0m\n";
                $this->running = false;
                break;
            }

            if (strtolower($input) === 'clear') {
                system(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'cls' : 'clear');
                continue;
            }

            if (strtolower($input) === 'help') {
                $this->printHelp();
                continue;
            }

            $this->execute($input);
        }
    }

    /**
     * Execute PHP code string safely
     */
    public function execute(string $code): mixed
    {
        // Remove trailing semicolon for uniform handling
        $trimmed = rtrim($code, ';');

        // Check if it's an assignment or statement vs expression
        $isStatement = preg_match('/^\s*(echo|print|var_dump|print_r|if|foreach|while|for|switch|class|function|\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\s*=)/', $trimmed);

        if ($isStatement) {
            $evalCode = $trimmed . ';';
        } else {
            $evalCode = 'return (' . $trimmed . ');';
        }

        try {
            // Include common imports in local scope
            $result = eval(
                'use NovaFlow\Core\DB; ' .
                'use NovaFlow\Core\Container; ' .
                'use NovaFlow\Core\Cache; ' .
                'use NovaFlow\Core\DatabaseInterface; ' .
                'use NovaFlow\Core\QueryBuilder\Schema; ' .
                'use NovaFlow\Core\QueryBuilder\QueryBuilder; ' .
                $evalCode
            );

            if (!$isStatement && $result !== null) {
                echo "\033[32m=> \033[0m" . $this->formatResult($result) . "\n";
            }

            return $result;
        } catch (Throwable $e) {
            echo "\033[31m[Error] " . get_class($e) . ": " . $e->getMessage() . "\033[0m\n";
            echo "\033[90m  at " . $e->getFile() . ":" . $e->getLine() . "\033[0m\n";
            return null;
        }
    }

    /**
     * Format result for terminal display
     */
    public function formatResult(mixed $result): string
    {
        if (is_bool($result)) {
            return $result ? "\033[33mtrue\033[0m" : "\033[33mfalse\033[0m";
        }

        if (is_null($result)) {
            return "\033[33mnull\033[0m";
        }

        if (is_string($result)) {
            return '"' . addcslashes($result, "\n\r\t\"\\") . '"';
        }

        if (is_numeric($result)) {
            return "\033[35m" . $result . "\033[0m";
        }

        if (is_array($result) || is_object($result)) {
            $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json !== false) {
                return $json;
            }
            return print_r($result, true);
        }

        return (string) $result;
    }

    private function printBanner(): void
    {
        echo "\033[36m╔══════════════════════════════════════════════════════╗\033[0m\n";
        echo "\033[36m║\033[0m      \033[1mNovaFlow Interactive Shell (Tinker)\033[0m             \033[36m║\033[0m\n";
        echo "\033[36m║\033[0m      PHP " . PHP_VERSION . " | Type \033[33m'help'\033[0m or \033[33m'exit'\033[0m to quit      \033[36m║\033[0m\n";
        echo "\033[36m╚══════════════════════════════════════════════════════╝\033[0m\n\n";
    }

    private function printHelp(): void
    {
        echo "\n\033[1mNovaFlow Tinker Guide:\033[0m\n";
        echo " - Query Database:  \033[32mDB::table('users')->get()\033[0m\n";
        echo " - Use Models:      \033[32mApp\\Models\\User::all()\033[0m\n";
        echo " - Read/Set Cache:  \033[32mCache::get('my_key')\033[0m\n";
        echo " - Inspect Schema:  \033[32mSchema::hasTable('products')\033[0m\n";
        echo " - Clear Screen:    \033[32mclear\033[0m\n";
        echo " - Exit Shell:      \033[32mexit\033[0m or \033[32mquit\033[0m\n\n";
    }
}
