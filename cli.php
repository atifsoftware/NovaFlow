<?php

/**
 * NovaFlow CLI Tool
 * Premium Framework Modernization Kit
 */

// Define Base Path
define('BASE_PATH', __DIR__);

// 1. Initialise Autoloader & Helpers
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}
require_once BASE_PATH . '/app/libraries/helpers.php';

// 2. Load Environment first so it is available to Config
if (class_exists('NovaFlow\Core\Dotenv')) {
    \NovaFlow\Core\Dotenv::load(BASE_PATH . '/.env');
}

// 3. Load Configuration
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

    private function viewRoutes()
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

    private function systemHealthCheck()
    {
        echo $this->color("\nNovaFlow System Health Check:\n", 'bold');

        // 1. PHP Version
        $phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
        $this->printCheck("PHP Version (>= 8.1)", PHP_VERSION, $phpOk);

        // 2. Extensions
        $reqExts = ['pdo', 'mysqlnd', 'curl', 'openssl', 'mbstring', 'json'];
        foreach ($reqExts as $ext) {
            $this->printCheck("Extension: $ext", extension_loaded($ext) ? "Loaded" : "Missing", extension_loaded($ext));
        }

        // 3. Writable Dirs
        $dirs = ['logs', 'public/uploads'];
        foreach ($dirs as $dir) {
            $path = BASE_PATH . '/' . $dir;
            $isWritable = is_dir($path) && is_writable($path);
            $this->printCheck("Writable: $dir", $isWritable ? "Yes" : "No", $isWritable);
        }

        // 4. Database Connection
        try {
            $db = Container::getInstance()->make(DatabaseInterface::class);
            $db->query("SELECT 1");
            $this->printCheck("Database Connection", "Connected", true);
        } catch (\Exception $e) {
            $this->printCheck("Database Connection", "Failed: " . $e->getMessage(), false);
        }

        // 5. Environment
        $this->printCheck(".env File", file_exists(BASE_PATH . '/.env') ? "Exists" : "Missing", file_exists(BASE_PATH . '/.env'));

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

    private function runMigrations()
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
        $this->pause();
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
    $cli->run();
} else {
    echo "This script must be run from command line.\n";
}
