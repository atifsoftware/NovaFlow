<?php

namespace NovaFlow\Core;

/**
 * ScaffoldGenerator
 * Handles creation of Migrations, Controllers, and other boilerplate files
 */
class ScaffoldGenerator
{
    private $migrationsPath = BASE_PATH . '/app/database/migrations/';
    private $seedersPath = BASE_PATH . '/app/database/seeds/';
    private $testsPath = BASE_PATH . '/tests/';

    public function __construct()
    {
        foreach ([$this->migrationsPath, $this->seedersPath, $this->testsPath] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Create a new migration file
     */
    public function createMigration(string $tableName): string
    {
        $timestamp = date('Y_m_d_His');
        $className = 'Create' . str_replace('_', '', ucwords($tableName, '_')) . 'Table';
        $fileName = "{$timestamp}_create_{$tableName}_table.php";
        $targetFile = $this->migrationsPath . $fileName;

        $content = "<?php\n\n";
        $content .= "use NovaFlow\Core\QueryBuilder\Schema;\n";
        $content .= "use NovaFlow\Core\QueryBuilder\Blueprint;\n\n";
        $content .= "class {$className}\n";
        $content .= "{\n";
        $content .= "    /**\n     * Run the migrations.\n     */\n";
        $content .= "    public function up()\n";
        $content .= "    {\n";
        $content .= "        Schema::create('{$tableName}', function (Blueprint \$table) {\n";
        $content .= "            \$table->id();\n";
        $content .= "            \$table->timestamps();\n";
        $content .= "        });\n";
        $content .= "    }\n\n";
        $content .= "    /**\n     * Reverse the migrations.\n     */\n";
        $content .= "    public function down()\n";
        $content .= "    {\n";
        $content .= "        Schema::dropIfExists('{$tableName}');\n";
        $content .= "    }\n";
        $content .= "}\n";

        file_put_contents($targetFile, $content);
        return $fileName;
    }

    /**
     * Create a new seeder file
     */
    public function createSeeder(string $name): string
    {
        $name = ucfirst(str_replace('Seeder', '', $name)) . 'Seeder';
        $targetFile = $this->seedersPath . $name . '.php';

        $content = "<?php\n\n";
        $content .= "use NovaFlow\Core\DB;\n\n";
        $content .= "class {$name}\n";
        $content .= "{\n";
        $content .= "    /**\n     * Run the database seeds.\n     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";
        $content .= "        // Example: DB::table('users')->insert(['name' => 'Admin', 'email' => 'admin@example.com']);\n";
        $content .= "    }\n";
        $content .= "}\n";

        file_put_contents($targetFile, $content);
        return $name;
    }

    /**
     * Create a new controller file
     */
    public function createController(string $name): string
    {
        $name = ucfirst(str_replace('Controller', '', $name)) . 'Controller';
        $targetFile = BASE_PATH . "/app/controllers/{$name}.php";

        if (file_exists($targetFile)) {
            throw new \Exception("Controller already exists!");
        }

        $content = "<?php\n\n";
        $content .= "namespace App\Controllers;\n\n";
        $content .= "use NovaFlow\Core\Controller;\n";
        $content .= "use NovaFlow\Core\DB;\n\n";
        $content .= "class {$name} extends Controller\n";
        $content .= "{\n";
        $content .= "    public function index()\n";
        $content .= "    {\n";
        $content .= "        return \$this->view('" . strtolower(str_replace('Controller', '', $name)) . ".index', [\n";
        $content .= "            'title' => '{$name}'\n";
        $content .= "        ]);\n";
        $content .= "    }\n";
        $content .= "}\n";

        file_put_contents($targetFile, $content);
        return $name;
    }

    /**
     * Create a new test file
     */
    public function createTest(string $name): string
    {
        $name = ucfirst(str_replace('Test', '', $name)) . 'Test';
        $targetFile = $this->testsPath . $name . '.php';

        $content = "<?php\n\n";
        $content .= "use PHPUnit\Framework\TestCase;\n\n";
        $content .= "class {$name} extends TestCase\n";
        $content .= "{\n";
        $content .= "    public function testExample()\n";
        $content .= "    {\n";
        $content .= "        \$this->assertTrue(true);\n";
        $content .= "    }\n";
        $content .= "}\n";

        file_put_contents($targetFile, $content);
        return $name;
    }
}
