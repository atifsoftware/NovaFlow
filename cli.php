<?php

/**
 * NovaFlow CLI Tool
 * Premium Framework Modernization Kit
 */

// Define Base Path
define('BASE_PATH', __DIR__);

// 1. Initialise Autoloader & Helpers
require_once BASE_PATH . '/app/libraries/Autoloader.php';
require_once BASE_PATH . '/app/libraries/helpers.php';
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

Autoloader::register();
Autoloader::addNamespace('NovaFlow\Core', BASE_PATH . '/app/libraries');
Autoloader::addNamespace('App\Controllers', BASE_PATH . '/app/controllers');
Autoloader::addNamespace('App\Models', BASE_PATH . '/app/models');
Autoloader::addNamespace('App\Database\Seeds', BASE_PATH . '/app/database/seeds');
Autoloader::addNamespace('App\Database\Factories', BASE_PATH . '/app/database/factories');
Autoloader::addNamespace('App\Events', BASE_PATH . '/app/events');
Autoloader::addNamespace('App\Listeners', BASE_PATH . '/app/listeners');

// 2. Load Configuration & Environment
if (class_exists('NovaFlow\Core\Dotenv')) \NovaFlow\Core\Dotenv::load(BASE_PATH . '/.env');
if (file_exists(BASE_PATH . '/config/config.php')) require_once BASE_PATH . '/config/config.php';
if (file_exists(BASE_PATH . '/config/database.php')) require_once BASE_PATH . '/config/database.php';

// 3. Include the project initialization
require_once BASE_PATH . '/app/bootstrap.php';

use NovaFlow\Core\ModelGenerator;
use NovaFlow\Core\ScaffoldGenerator;
use NovaFlow\Core\Router;
use NovaFlow\Core\Container;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\QueueWorker;
use NovaFlow\Core\Queue;
use NovaFlow\Core\Migrator;

class CLI
{
    private $generator;
    private $colors = [
        'reset' => "\033[0m",
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'gray' => "\033[90m",
        'bold' => "\033[1m",
        'white' => "\033[37m"
    ];

    private $scaffold;
    private $router;

    public function __construct()
    {
        $this->generator = new ModelGenerator();
        $this->scaffold = new ScaffoldGenerator();
        $this->router = new Router();
        
        // Load routes into router for Route List
        $router = $this->router;
        if (file_exists(BASE_PATH . '/config/routes.php')) {
            require BASE_PATH . '/config/routes.php';
        }
    }

    public function run()
    {
        $this->printHeader();
        $this->showMenu();
    }

    private function printHeader()
    {
        $this->clearScreen();
        echo $this->color("╔════════════════════════════════════════════╗\n", 'cyan');
        echo $this->color("║      ", 'cyan') . $this->color("NOVAFLOW CORE CLI TOOL", 'bold') . $this->color("        ║\n", 'cyan');
        echo $this->color("║       ", 'cyan') . "Framework Development Kit" . $this->color("      ║\n", 'cyan');
        echo $this->color("╚════════════════════════════════════════════╝\n", 'cyan');
        echo "\n";
    }

    private function showMenu()
    {
        while (true) {
            echo $this->color("\n=== MAIN MENU ===\n", 'bold');
            echo "1. " . $this->color("View All Tables", 'white') . "\n";
            echo "2. " . $this->color("Generate Model (Single Table)", 'green') . "\n";
            echo "3. " . $this->color("Generate All Models", 'green') . "\n";
            echo "4. " . $this->color("Database Summary Report", 'yellow') . "\n";
            echo "5. " . $this->color("Route List", 'cyan') . "\n";
            echo "6. " . $this->color("Create Migration", 'blue') . "\n";
            echo "7. " . $this->color("System Health Check", 'bold') . "\n";
            echo "8. " . $this->color("Clear Logs & Temp Files", 'red') . "\n";
            echo "9. " . $this->color("Generate Seeder", 'magenta') . "\n";
            echo "10. " . $this->color("Generate Unit Test", 'magenta') . "\n";
            echo "11. " . $this->color("Start Queue Worker", 'cyan') . "\n";
            echo "12. " . $this->color("Master CRUD Scaffold", 'bold') . "\n";
            echo "13. " . $this->color("Run Pending Migrations", 'magenta') . "\n";
            echo "14. " . $this->color("Run Database Seeders", 'magenta') . "\n";
            echo "15. " . $this->color("Generate Model Factory", 'green') . "\n";
            echo "16. " . $this->color("Interactive Tinker Shell (REPL)", 'cyan') . "\n";
            echo "0. " . $this->color("Exit", 'red') . "\n";
            echo "\n";

            $choice = $this->input("Select option");

            switch ($choice) {
                case '1':
                    $this->viewAllTables();
                    break;
                case '2':
                    $this->generateSingleModel();
                    break;
                case '3':
                    $this->generateAllModels();
                    break;
                case '4':
                    $this->showSummaryReport();
                    break;
                case '5':
                    $this->viewRoutes();
                    break;
                case '6':
                    $this->createMigration();
                    break;
                case '7':
                    $this->systemHealthCheck();
                    break;
                case '8':
                    $this->clearLogs();
                    break;
                case '9':
                    $this->generateSeeder();
                    break;
                case '10':
                    $this->generateTest();
                    break;
                case '11':
                    $this->startQueueWorker();
                    break;
                case '12':
                    $this->generateMasterCRUD();
                    break;
                case '13':
                    $this->runMigrations();
                    break;
                case '14':
                    $this->runSeeders();
                    break;
                case '15':
                    $this->generateFactory();
                    break;
                case '16':
                    $this->startTinker();
                    break;
                case '0':
                    echo $this->color("\n✓ Goodbye From NovaFlow!\n\n", 'green');
                    exit(0);
                default:
                    echo $this->color("✗ Invalid option!\n", 'red');
            }
        }
    }

    private function viewAllTables()
    {
        try {
            $tables = $this->generator->getAllTables();
            echo $this->color("\nDatabase Tables:\n", 'bold');
            foreach ($tables as $i => $t) echo ($i + 1) . ". $t\n";
        } catch (\Exception $e) {
            echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
        }
        $this->pause();
    }

    private function generateSingleModel()
    {
        try {
            $tables = $this->generator->getAllTables();
            foreach ($tables as $i => $t) echo ($i + 1) . ". $t\n";
            $choice = (int)$this->input("Select table number");
            $tableName = $tables[$choice - 1] ?? null;

            if ($tableName) {
                $name = $this->generator->generateModel($tableName);
                echo $this->color("\n✓ Model '{$name}' generated successfully!\n", 'green');
            } else {
                echo $this->color("\n✗ Invalid selection!\n", 'red');
            }
        } catch (\Exception $e) {
            echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
        }
        $this->pause();
    }

    private function generateAllModels()
    {
        if ($this->confirm("Are you sure you want to generate all models?")) {
            try {
                $tables = $this->generator->getAllTables();
                foreach ($tables as $table) {
                    $name = $this->generator->generateModel($table);
                    echo $this->color("✓ ", 'green') . "Generated: $name\n";
                }
                echo $this->color("\n✓ All models generated!\n", 'green');
            } catch (\Exception $e) {
                echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
            }
        }
        $this->pause();
    }

    private function createMigration()
    {
        $tableName = $this->input("Enter table name (e.g. products)");
        if ($tableName) {
            try {
                $file = $this->scaffold->createMigration($tableName);
                echo $this->color("\n✓ Migration '{$file}' created successfully!\n", 'green');
            } catch (\Exception $e) {
                echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
            }
        }
        $this->pause();
    }

    private function printCheck($label, $value, $success)
    {
        $icon = $success ? $this->color(" [PASS] ", 'green') : $this->color(" [FAIL] ", 'red');
        echo str_pad($label, 30) . ": $value" . $icon . "\n";
    }

    private function clearLogs()
    {
        if ($this->confirm("Clear all logs?")) {
            $files = glob(BASE_PATH . '/logs/*');
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }
            echo $this->color("\n✓ Logs cleared!\n", 'green');
        }
        $this->pause();
    }

    private function generateSeeder()
    {
        $name = $this->input("Enter seeder name (e.g. UserSeeder)");
        if ($name) {
            try {
                $file = $this->scaffold->createSeeder($name);
                echo $this->color("\n✓ Seeder '{$file}' created successfully!\n", 'green');
            } catch (\Exception $e) {
                echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
            }
        }
        $this->pause();
    }

    private function generateTest()
    {
        $name = $this->input("Enter test name (e.g. UserTest)");
        if ($name) {
            try {
                $file = $this->scaffold->createTest($name);
                echo $this->color("\n✓ Test file '{$file}' created successfully!\n", 'green');
            } catch (\Exception $e) {
                echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
            }
        }
        $this->pause();
    }

    private function startQueueWorker()
    {
        echo $this->color("\nStarting NovaFlow Queue Worker...\n", 'cyan');
        try {
            $worker = new QueueWorker();
            $worker->work();
        } catch (\Exception $e) {
            echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
        }
        $this->pause();
    }

    private function generateMasterCRUD()
    {
        $tableName = $this->input("Enter table name for full scaffold (e.g. products)");
        if ($tableName) {
            if ($this->confirm("This will generate Model, Controller, Migration, Seeder, and Test. Proceed?")) {
                try {
                    // 1. Model
                    $modelName = $this->generator->generateModel($tableName);
                    echo $this->color("✓ ", 'green') . "Model: $modelName\n";

                    // 2. Controller
                    $controllerName = $this->scaffold->createController($modelName);
                    echo $this->color("✓ ", 'green') . "Controller: $controllerName\n";

                    // 3. Migration
                    $migrationFile = $this->scaffold->createMigration($tableName);
                    echo $this->color("✓ ", 'green') . "Migration: $migrationFile\n";

                    // 4. Seeder
                    $seederName = $this->scaffold->createSeeder($modelName);
                    echo $this->color("✓ ", 'green') . "Seeder: $seederName\n";

                    // 5. Test
                    $testName = $this->scaffold->createTest($modelName);
                    echo $this->color("✓ ", 'green') . "Test: $testName\n";

                    echo $this->color("\n✓ Master CRUD Scaffold complete for '{$tableName}'!\n", 'green');
                } catch (\Exception $e) {
                    echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
                }
            }
        }
        $this->pause();
    }

    private function showSummaryReport()
    {
        try {
            $tables = $this->generator->getAllTables();
            echo "\nTotal Tables Found: " . count($tables) . "\n";
        } catch (\Exception $e) {
            echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
        }
        $this->pause();
    }

    public function runMigrations(bool $pause = true)
    {
        echo $this->color("\nChecking for pending migrations...\n", 'bold');
        try {
            $db = Container::getInstance()->make(DatabaseInterface::class);
            $migrator = new Migrator($db);
            $ran = $migrator->run();

            if (empty($ran)) {
                echo $this->color("\n✓ Everything is up to date. No pending migrations.\n", 'green');
            } else {
                echo $this->color("\n✓ " . count($ran) . " migration(s) executed successfully!\n", 'green');
            }
        } catch (\Exception $e) {
            echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
        }
        if ($pause) $this->pause();
    }

    public function runSeeders(?string $class = null, bool $pause = true)
    {
        echo $this->color("\nSeeding database...\n", 'bold');
        $startTime = microtime(true);
        try {
            $targetClass = $class ?: 'App\\Database\\Seeds\\DatabaseSeeder';

            if (!class_exists($targetClass)) {
                // Try short name without namespace
                $altClass = 'App\\Database\\Seeds\\' . $class;
                if (class_exists($altClass)) {
                    $targetClass = $altClass;
                } else {
                    echo $this->color("✗ Seeder class '{$targetClass}' not found!\n", 'red');
                    if ($pause) $this->pause();
                    return;
                }
            }

            $seeder = new $targetClass();
            $seeder->run();
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            echo $this->color("✓ Database seeded successfully with {$targetClass} ({$duration}ms)!\n", 'green');
        } catch (\Throwable $e) {
            echo $this->color("✗ Seeder failed: " . $e->getMessage() . "\n", 'red');
        }
        if ($pause) $this->pause();
    }

    private function generateFactory()
    {
        $name = $this->input("Enter model name for factory (e.g. Product)");
        if ($name) {
            try {
                $file = $this->scaffold->createFactory($name);
                echo $this->color("\n✓ Factory '{$file}' created in app/database/factories/\n", 'green');
            } catch (\Exception $e) {
                echo $this->color("\n✗ Error: " . $e->getMessage() . "\n", 'red');
            }
        }
        $this->pause();
    }

    public function startTinker()
    {
        $tinker = new \NovaFlow\Core\Console\Tinker();
        $tinker->start();
    }

    public function handleArgs(array $argv)
    {
        $cmd = $argv[1] ?? null;
        if (!$cmd) {
            $this->run();
            return;
        }

        switch ($cmd) {
            case 'tinker':
                $this->startTinker();
                break;
            case 'migrate':
                $this->runMigrations(false);
                break;
            case 'db:seed':
                $class = null;
                foreach (array_slice($argv, 2) as $arg) {
                    if (str_starts_with($arg, '--class=')) {
                        $class = substr($arg, 8);
                    } elseif (!str_starts_with($arg, '-')) {
                        $class = $arg;
                    }
                }
                $this->runSeeders($class, false);
                break;
            case 'make:seeder':
                $name = $argv[2] ?? null;
                if (!$name) {
                    echo $this->color("Usage: php cli.php make:seeder <Name>\n", 'yellow');
                    exit(1);
                }
                $res = $this->scaffold->createSeeder($name);
                echo $this->color("✓ Seeder '{$res}' created in app/database/seeds/\n", 'green');
                break;
            case 'make:factory':
                $name = $argv[2] ?? null;
                if (!$name) {
                    echo $this->color("Usage: php cli.php make:factory <ModelName>\n", 'yellow');
                    exit(1);
                }
                $res = $this->scaffold->createFactory($name);
                echo $this->color("✓ Factory '{$res}' created in app/database/factories/\n", 'green');
                break;
            case 'make:migration':
                $table = $argv[2] ?? null;
                if (!$table) {
                    echo $this->color("Usage: php cli.php make:migration <TableName>\n", 'yellow');
                    exit(1);
                }
                $res = $this->scaffold->createMigration($table);
                echo $this->color("✓ Migration '{$res}' created in app/database/migrations/\n", 'green');
                break;
            case 'make:model':
                $table = $argv[2] ?? null;
                if (!$table) {
                    echo $this->color("Usage: php cli.php make:model <TableName>\n", 'yellow');
                    exit(1);
                }
                $res = $this->generator->generateModel($table);
                echo $this->color("✓ Model '{$res}' generated in app/models/\n", 'green');
                break;
            case 'routes':
                $this->viewRoutes(false);
                break;
            case 'health':
                $this->systemHealthCheck(false);
                break;
            case 'queue:work':
                $this->startQueueWorker();
                break;
            case 'cache:clear':
                \NovaFlow\Core\Cache::clear();
                echo $this->color("✓ Cache cleared successfully!\n", 'green');
                break;
            default:
                echo $this->color("Unknown command: {$cmd}\n", 'red');
                echo "Available commands:\n";
                echo "  tinker, migrate, db:seed, make:seeder, make:factory, make:migration, make:model, routes, health, queue:work, cache:clear\n";
                exit(1);
        }
    }

    public function viewRoutes(bool $pause = true)
    {
        echo $this->color("\nRegistered Routes:\n", 'bold');
        echo str_pad("Method", 10) . str_pad("Path", 40) . "Middlewares\n";
        echo str_repeat("-", 80) . "\n";

        foreach ($this->router->getRoutes() as $method => $paths) {
            foreach ($paths as $path => $info) {
                $mw = !empty($info['middleware']) ? implode(', ', $info['middleware']) : 'None';
                echo str_pad(strtoupper($method), 10) . str_pad($path, 40) . $mw . "\n";
            }
        }
        if ($pause) $this->pause();
    }

    public function systemHealthCheck(bool $pause = true)
    {
        echo $this->color("\nNovaFlow System Health Check:\n", 'bold');

        $phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
        $this->printCheck("PHP Version (>= 8.1)", PHP_VERSION, $phpOk);

        $reqExts = ['pdo', 'mysqlnd', 'curl', 'openssl', 'mbstring', 'json'];
        foreach ($reqExts as $ext) {
            $this->printCheck("Extension: $ext", extension_loaded($ext) ? "Loaded" : "Missing", extension_loaded($ext));
        }

        $dirs = ['logs', 'public/uploads'];
        foreach ($dirs as $dir) {
            $path = BASE_PATH . '/' . $dir;
            $isWritable = is_dir($path) && is_writable($path);
            $this->printCheck("Writable: $dir", $isWritable ? "Yes" : "No", $isWritable);
        }

        try {
            $db = Container::getInstance()->make(DatabaseInterface::class);
            $db->query("SELECT 1");
            $this->printCheck("Database Connection", "Connected", true);
        } catch (\Exception $e) {
            $this->printCheck("Database Connection", "Failed: " . $e->getMessage(), false);
        }

        $this->printCheck(".env File", file_exists(BASE_PATH . '/.env') ? "Exists" : "Missing", file_exists(BASE_PATH . '/.env'));

        if ($pause) $this->pause();
    }

    private function input($prompt)
    {
        echo $this->color($prompt . ": ", 'yellow');
        return trim(fgets(STDIN));
    }

    private function confirm($prompt)
    {
        echo $this->color("{$prompt} (y/N): ", 'yellow');
        $input = strtolower(trim(fgets(STDIN)));
        return ($input === 'y' || $input === 'yes');
    }

    private function pause()
    {
        echo "\n" . $this->color("Press Enter to continue...", 'cyan');
        fgets(STDIN);
        $this->printHeader();
    }

    private function clearScreen()
    {
        system(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'cls' : 'clear');
    }

    private function color($text, $color)
    {
        return $this->colors[$color] . $text . $this->colors['reset'];
    }
}

if (php_sapi_name() === 'cli') {
    $cli = new CLI();
    $cli->handleArgs($argv ?? []);
} else {
    echo "This script must be run from command line.\n";
}
