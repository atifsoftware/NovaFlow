<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Container;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\Model;
use NovaFlow\Core\Relations\BelongsTo;
use NovaFlow\Core\Relations\HasMany;
use NovaFlow\Core\RBAC;
use NovaFlow\Core\Dotenv;
use NovaFlow\Core\Validator;
use App\Models\UserModel;

class TestAuthor extends Model
{
    protected string $table = 'test_authors';
    protected array $allowedFields = ['id', 'name'];

    public function posts()
    {
        return $this->hasMany(TestPost::class, 'author_id', 'id');
    }
}

class TestPost extends Model
{
    protected string $table = 'test_posts';
    protected array $allowedFields = ['id', 'title', 'author_id'];

    public function author()
    {
        return $this->belongsTo(TestAuthor::class, 'author_id', 'id');
    }
}

class EnterpriseFeaturesTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new SqliteConnection(['database' => ':memory:']);

        Container::getInstance()->bind(DatabaseInterface::class, function () {
            return $this->connection;
        });

        // Create tables
        $this->connection->query("CREATE TABLE test_authors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        )");

        $this->connection->query("CREATE TABLE test_posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            author_id INTEGER NOT NULL
        )");

        // RBAC tables
        $this->connection->query("CREATE TABLE roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            description TEXT NULL,
            created_at TEXT NULL,
            updated_at TEXT NULL
        )");

        $this->connection->query("CREATE TABLE permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            module TEXT DEFAULT 'general',
            created_at TEXT NULL,
            updated_at TEXT NULL
        )");

        $this->connection->query("CREATE TABLE role_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            role_id INTEGER NOT NULL,
            permission_id INTEGER NOT NULL
        )");

        $this->connection->query("CREATE TABLE user_roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            role_id INTEGER NOT NULL
        )");

        $this->connection->query("CREATE TABLE user_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            permission_id INTEGER NOT NULL
        )");

        RBAC::clearCache();
    }

    public function test_eager_loading_belongs_to_eliminates_n_plus_one()
    {
        // Insert authors
        $this->connection->query("INSERT INTO test_authors (id, name) VALUES (1, 'Alice'), (2, 'Bob')");

        // Insert posts
        $this->connection->query("INSERT INTO test_posts (id, title, author_id) VALUES 
            (1, 'Post A1', 1),
            (2, 'Post A2', 1),
            (3, 'Post B1', 2)");

        // Eager load with('author')
        $posts = TestPost::with('author')->get();
        $this->assertCount(3, $posts);

        $this->assertTrue($posts[0]->relationLoaded('author'));
        $this->assertNotNull($posts[0]->author);
        $this->assertEquals('Alice', $posts[0]->author->name);

        $this->assertTrue($posts[2]->relationLoaded('author'));
        $this->assertEquals('Bob', $posts[2]->author->name);

        // Serialization includes relation
        $arr = $posts[0]->toArray();
        $this->assertArrayHasKey('author', $arr);
        $this->assertEquals('Alice', $arr['author']['name']);
    }

    public function test_eager_loading_has_many()
    {
        $this->connection->query("INSERT INTO test_authors (id, name) VALUES (1, 'Alice'), (2, 'Bob')");
        $this->connection->query("INSERT INTO test_posts (id, title, author_id) VALUES 
            (1, 'Post 1', 1),
            (2, 'Post 2', 1),
            (3, 'Post 3', 2)");

        $authors = TestAuthor::with('posts')->get();
        $this->assertCount(2, $authors);

        $this->assertTrue($authors[0]->relationLoaded('posts'));
        $this->assertCount(2, $authors[0]->posts);
        $this->assertEquals('Post 1', $authors[0]->posts[0]->title);

        $this->assertTrue($authors[1]->relationLoaded('posts'));
        $this->assertCount(1, $authors[1]->posts);
        $this->assertEquals('Post 3', $authors[1]->posts[0]->title);
    }

    public function test_query_builder_chunk_and_chunk_by_id()
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->connection->query("INSERT INTO test_posts (id, title, author_id) VALUES ($i, 'Post $i', 1)");
        }

        // Test chunk
        $totalProcessed = 0;
        $pages = [];
        $result = TestPost::query()->chunk(3, function ($chunk, $page) use (&$totalProcessed, &$pages) {
            $totalProcessed += count($chunk);
            $pages[] = $page;
        });

        $this->assertTrue($result);
        $this->assertEquals(10, $totalProcessed);
        $this->assertEquals([1, 2, 3, 4], $pages);

        // Test chunkById
        $totalById = 0;
        $resultId = TestPost::query()->chunkById(4, function ($chunk) use (&$totalById) {
            $totalById += count($chunk);
        });

        $this->assertTrue($resultId);
        $this->assertEquals(10, $totalById);
    }

    public function test_query_builder_cursor_streams_memory_safely()
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->connection->query("INSERT INTO test_posts (id, title, author_id) VALUES ($i, 'Post $i', 1)");
        }

        $generator = TestPost::query()->cursor();
        $this->assertInstanceOf(\Generator::class, $generator);

        $titles = [];
        foreach ($generator as $post) {
            $this->assertInstanceOf(TestPost::class, $post);
            $titles[] = $post->title;
        }

        $this->assertCount(5, $titles);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3', 'Post 4', 'Post 5'], $titles);
    }

    public function test_dynamic_rbac_roles_permissions_and_wildcard()
    {
        // 1. Create Roles
        $managerRoleId = RBAC::createRole('Manager', 'manager', 'Store Manager');
        $this->assertIsInt($managerRoleId);

        // 2. Create Permissions
        $viewPermId = RBAC::createPermission('View Invoices', 'sales.invoices.view', 'sales');
        $createPermId = RBAC::createPermission('Create Invoices', 'sales.invoices.create', 'sales');
        $reportsPermId = RBAC::createPermission('Reports', 'reports.*', 'reports');

        $this->assertIsInt($viewPermId);
        $this->assertIsInt($createPermId);
        $this->assertIsInt($reportsPermId);

        // 3. Give permissions to manager role
        RBAC::givePermissionToRole('manager', ['sales.invoices.view', 'reports.*']);

        // 4. Assign role to User 42
        RBAC::assignRole(42, 'manager');

        // Check role
        $this->assertTrue(RBAC::hasRole(42, 'manager'));
        $this->assertFalse(RBAC::hasRole(42, 'editor'));

        // Check exact permission
        $this->assertTrue(RBAC::hasPermission(42, 'sales.invoices.view'));
        $this->assertFalse(RBAC::hasPermission(42, 'sales.invoices.create'));

        // Check wildcard permission: reports.* matches reports.financial
        $this->assertTrue(RBAC::hasPermission(42, 'reports.financial'));
        $this->assertTrue(RBAC::hasPermission(42, 'reports.sales.monthly'));

        // Super Admin role has access to everything
        RBAC::assignRole(99, 'admin');
        $this->assertTrue(RBAC::hasPermission(99, 'any.unassigned.permission'));
        $this->assertTrue(RBAC::can('delete.database', 99));
    }

    public function test_dotenv_boolean_null_and_inline_comment_parsing()
    {
        $envFile = sys_get_temp_dir() . '/.env_test_' . uniqid();
        file_put_contents($envFile, "
            # Comment
            APP_DEBUG=false # Inline comment
            APP_NAME=\"NovaFlow Framework\"
            FEATURE_ENABLED=true
            NULL_VAL=null
            PORT=3306 // Another comment
        ");

        Dotenv::load($envFile, true);

        $this->assertFalse(Dotenv::get('APP_DEBUG'));
        $this->assertTrue(Dotenv::get('FEATURE_ENABLED'));
        $this->assertNull(Dotenv::get('NULL_VAL'));
        $this->assertEquals('NovaFlow Framework', Dotenv::get('APP_NAME'));
        $this->assertEquals('3306', Dotenv::get('PORT'));

        @unlink($envFile);

        // Reload original application .env so other tests are unaffected
        Dotenv::load(null, true);
    }

    public function test_validator_strict_date_and_in_rule()
    {
        // 2026-02-31 is invalid calendar date (February never has 31 days)
        $invalidDate = Validator::make(['birth' => '2026-02-31'], ['birth' => 'date']);
        $this->assertTrue($invalidDate->fails());

        // Valid date
        $validDate = Validator::make(['birth' => '2026-02-28'], ['birth' => 'date']);
        $this->assertFalse($validDate->fails());

        // Strict in rule
        $inVal = Validator::make(['role' => 'editor'], ['role' => 'in:admin,editor,user']);
        $this->assertFalse($inVal->fails());

        $inValFail = Validator::make(['role' => 'superadmin'], ['role' => 'in:admin,editor,user']);
        $this->assertTrue($inValFail->fails());
    }
}
