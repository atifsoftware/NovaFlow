<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Model;
use NovaFlow\Core\Container;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Database\Drivers\SqliteConnection;

class TestUserModel extends Model
{
    protected string $table = 'test_users';
    protected array $allowedFields = ['name', 'email', 'password', 'api_token'];
    protected array $hidden = ['password', 'api_token'];

    public function profile()
    {
        return $this->hasOne(TestProfileModel::class, 'user_id', 'id');
    }

    public function posts()
    {
        return $this->hasMany(TestArticleModel::class, 'user_id', 'id');
    }
}

class TestProfileModel extends Model
{
    protected string $table = 'test_profiles';
    protected array $allowedFields = ['user_id', 'bio', 'avatar'];

    public function user()
    {
        return $this->belongsTo(TestUserModel::class, 'user_id', 'id');
    }
}

class TestArticleModel extends Model
{
    protected string $table = 'test_articles';
    protected array $allowedFields = ['user_id', 'title', 'body'];

    public function user()
    {
        return $this->belongsTo(TestUserModel::class, 'user_id', 'id');
    }
}

class OrderItemModel extends Model
{
    // No $table specified, table name should be inferred
}

class CustomerModel extends Model
{
    // No $table specified, table name should be inferred
}

class ModelRelationsTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new SqliteConnection(['database' => ':memory:']);

        Container::getInstance()->bind(DatabaseInterface::class, function () {
            return $this->connection;
        });

        // Create schemas
        $this->connection->query("CREATE TABLE test_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            password TEXT NOT NULL,
            api_token TEXT NOT NULL
        )");

        $this->connection->query("CREATE TABLE test_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            bio TEXT,
            avatar TEXT
        )");

        $this->connection->query("CREATE TABLE test_articles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            body TEXT
        )");
    }

    public function testTableNameInference(): void
    {
        $orderItem = new OrderItemModel();
        $this->assertEquals('order_items', $orderItem->getTable());

        $customer = new CustomerModel();
        $this->assertEquals('customers', $customer->getTable());
    }

    public function testHiddenFieldsOmissionInArrayAndJson(): void
    {
        $user = new TestUserModel([
            'name' => 'Alice Admin',
            'email' => 'alice@company.com',
            'password' => 'super_secret_hash',
            'api_token' => 'top_secret_bearer_token_xyz'
        ]);

        $arrayData = $user->toArray();
        $this->assertArrayHasKey('name', $arrayData);
        $this->assertArrayHasKey('email', $arrayData);
        $this->assertArrayNotHasKey('password', $arrayData, "Password must be stripped from toArray");
        $this->assertArrayNotHasKey('api_token', $arrayData, "API token must be stripped from toArray");

        $jsonData = json_encode($user);
        $this->assertStringNotContainsString('super_secret_hash', $jsonData);
        $this->assertStringNotContainsString('top_secret_bearer_token_xyz', $jsonData);
        $this->assertStringContainsString('Alice Admin', $jsonData);
    }

    public function testHasOneRelationship(): void
    {
        $user = new TestUserModel([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'hash',
            'api_token' => 'token'
        ]);
        $user->save();

        $profile = new TestProfileModel([
            'user_id' => $user->id,
            'bio' => 'Senior Software Engineer',
            'avatar' => 'avatar.jpg'
        ]);
        $profile->save();

        // Fetch user from DB and access profile relationship dynamically
        $retrievedUser = TestUserModel::find($user->id);
        $this->assertNotNull($retrievedUser);

        $retrievedProfile = $retrievedUser->profile;
        $this->assertInstanceOf(TestProfileModel::class, $retrievedProfile);
        $this->assertEquals('Senior Software Engineer', $retrievedProfile->bio);
        $this->assertEquals('avatar.jpg', $retrievedProfile->avatar);
    }

    public function testHasManyRelationship(): void
    {
        $user = new TestUserModel([
            'name' => 'Charlie Author',
            'email' => 'charlie@writer.com',
            'password' => 'hash',
            'api_token' => 'token'
        ]);
        $user->save();

        $article1 = new TestArticleModel(['user_id' => $user->id, 'title' => 'Article One', 'body' => 'Content 1']);
        $article1->save();

        $article2 = new TestArticleModel(['user_id' => $user->id, 'title' => 'Article Two', 'body' => 'Content 2']);
        $article2->save();

        $retrievedUser = TestUserModel::find($user->id);
        $posts = $retrievedUser->posts;

        $this->assertIsArray($posts);
        $this->assertCount(2, $posts);
        $this->assertEquals('Article One', $posts[0]->title);
        $this->assertEquals('Article Two', $posts[1]->title);
    }

    public function testBelongsToRelationship(): void
    {
        $user = new TestUserModel([
            'name' => 'Diana',
            'email' => 'diana@example.com',
            'password' => 'hash',
            'api_token' => 'token'
        ]);
        $user->save();

        $article = new TestArticleModel(['user_id' => $user->id, 'title' => 'Diana Post', 'body' => 'Hello']);
        $article->save();

        $retrievedArticle = TestArticleModel::find($article->id);
        $this->assertNotNull($retrievedArticle);

        $author = $retrievedArticle->user;
        $this->assertInstanceOf(TestUserModel::class, $author);
        $this->assertEquals('Diana', $author->name);
        $this->assertEquals('diana@example.com', $author->email);
    }

    public function testDynamicGettersAndSetters(): void
    {
        $user = new TestUserModel();
        $user->name = 'Dynamic Name';
        $user->email = 'dyn@example.com';

        $this->assertTrue(isset($user->name));
        $this->assertEquals('Dynamic Name', $user->name);
        $this->assertEquals('dyn@example.com', $user->email);
        $this->assertNull($user->non_existing_attribute);
    }
}
