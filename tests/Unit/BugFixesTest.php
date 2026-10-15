<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Container;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\Model;
use NovaFlow\Core\Relations\BelongsTo;
use NovaFlow\Core\Security;
use NovaFlow\Core\Request;
use NovaFlow\Core\Validator;
use App\Models\UserModel;

class TestPostModel extends Model
{
    protected string $table = 'posts';
    protected array $allowedFields = ['title', 'content', 'author_id'];
    protected $useSoftDelete = true;
    protected $deletedAtColumn = 'deleted_at';

    public function author()
    {
        return $this->belongsTo(TestAuthorModel::class, 'author_id', 'custom_id');
    }
}

class TestAuthorModel extends Model
{
    protected string $table = 'authors';
    protected string $primaryKey = 'custom_id';
    protected array $allowedFields = ['custom_id', 'name'];
}

class BugFixesTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new SqliteConnection(['database' => ':memory:']);

        // Bind DB to Container
        Container::getInstance()->bind(DatabaseInterface::class, function () {
            return $this->connection;
        });

        // Create tables for testing
        $this->connection->query("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'user'
        )");

        $this->connection->query("CREATE TABLE posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT,
            author_id INTEGER,
            is_published INTEGER DEFAULT 0,
            deleted_at TEXT NULL
        )");

        $this->connection->query("CREATE TABLE authors (
            custom_id INTEGER PRIMARY KEY,
            name TEXT NOT NULL
        )");
    }

    public function test_bug01_user_model_static_query_and_authenticate()
    {
        $hashed = Security::hashPassword('secret123');
        $this->connection->query("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@example.com', '$hashed', 'admin')");

        // Test static query method on Model
        $builder = UserModel::query();
        $this->assertInstanceOf(\NovaFlow\Core\QueryBuilder\QueryBuilder::class, $builder);

        // Test UserModel::authenticate
        $user = UserModel::authenticate('admin@example.com', 'secret123');
        $this->assertNotNull($user);
        $this->assertEquals('Admin', $user->name);

        // Test UserModel::findByEmail
        $found = UserModel::findByEmail('admin@example.com');
        $this->assertNotNull($found);
        $this->assertEquals('admin@example.com', $found->email);

        // Invalid credentials
        $invalid = UserModel::authenticate('admin@example.com', 'wrongpassword');
        $this->assertNull($invalid);
    }

    public function test_bug02_model_soft_delete_and_restore()
    {
        $post = new TestPostModel([
            'title' => 'Soft Delete Test',
            'content' => 'Some content'
        ]);
        $post->save();
        $this->assertTrue($post->exists);
        $id = $post->id;

        // Perform soft delete
        $result = $post->delete();
        $this->assertTrue($result);
        $this->assertNotNull($post->deleted_at);

        // Verify in database that deleted_at is set, row still exists
        $row = $this->connection->fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
        $this->assertNotNull($row);
        $this->assertNotNull($row['deleted_at']);

        // Restore
        $restored = $post->restore();
        $this->assertTrue($restored);
        $this->assertNull($post->deleted_at);

        $rowAfterRestore = $this->connection->fetchOne("SELECT * FROM posts WHERE id = ?", [$id]);
        $this->assertNull($rowAfterRestore['deleted_at']);
    }

    public function test_bug04_and_13_model_save_allowed_fields_and_insert_id()
    {
        $post = new TestPostModel();
        // Set both allowed and disallowed fields directly on attributes
        $post->setAttributes([
            'title' => 'Allowed Title',
            'content' => 'Allowed Content',
            'is_published' => 1 // not in allowedFields!
        ]);

        $post->save();
        $this->assertTrue($post->exists);
        $this->assertNotEmpty($post->id);

        // Check in DB that is_published defaulted to 0 because it was excluded by allowedFields mass assignment
        $row = $this->connection->fetchOne("SELECT * FROM posts WHERE id = ?", [$post->id]);
        $this->assertEquals('Allowed Title', $row['title']);
        $this->assertEquals(0, (int)$row['is_published']);
    }

    public function test_bug09_validator_unique_and_exists_use_field_as_default_column()
    {
        $this->connection->query("INSERT INTO users (name, email, password) VALUES ('Alice', 'alice@test.com', '1234')");

        // unique:users without explicit column should check 'email'
        $v1 = Validator::make(['email' => 'alice@test.com'], ['email' => 'unique:users']);
        $this->assertFalse($v1->validate());

        $v2 = Validator::make(['email' => 'bob@test.com'], ['email' => 'unique:users']);
        $this->assertTrue($v2->validate());

        // exists:users without explicit column should check 'email'
        $v3 = Validator::make(['email' => 'alice@test.com'], ['email' => 'exists:users']);
        $this->assertTrue($v3->validate());

        $v4 = Validator::make(['email' => 'nobody@test.com'], ['email' => 'exists:users']);
        $this->assertFalse($v4->validate());
    }

    public function test_bug10_belongs_to_uses_owner_key()
    {
        $this->connection->query("INSERT INTO authors (custom_id, name) VALUES (999, 'Famous Author')");

        $post = new TestPostModel([
            'title' => 'Post with custom owner key',
            'author_id' => 999
        ]);

        $author = $post->author;
        $this->assertNotNull($author);
        $this->assertEquals('Famous Author', $author->name);
        $this->assertEquals(999, $author->custom_id);
    }

    public function test_bug11_query_builder_count_with_group_by()
    {
        $this->connection->query("INSERT INTO posts (title, author_id) VALUES ('P1', 1), ('P2', 1), ('P3', 2)");

        $builder = (new \NovaFlow\Core\QueryBuilder\QueryBuilder($this->connection))->table('posts');
        $builder->groupBy('author_id');

        // Without fix, count on group by would count items in first group (2), but with fix, counts number of groups (2)
        $totalGroups = $builder->count();
        $this->assertEquals(2, $totalGroups);
    }

    public function test_bug12_request_and_helper_old()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['_old_input'] = ['username' => 'john_doe', 'role' => 'editor'];

        $this->assertEquals('john_doe', Request::old('username'));
        $this->assertEquals('default_val', Request::old('non_existing', 'default_val'));
        $this->assertEquals('editor', old('role'));
        $this->assertEquals('fallback', old('missing', 'fallback'));
    }

    public function test_bug15_security_hash_password_without_threads()
    {
        $hash = Security::hashPassword('supersecret');
        $this->assertNotEmpty($hash);
        $this->assertTrue(Security::verifyPassword('supersecret', $hash));
        $this->assertFalse(Security::verifyPassword('wrong', $hash));
    }
}
