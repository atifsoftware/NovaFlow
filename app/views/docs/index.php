<style>
    .docs-nav { position: sticky; top: 100px; height: calc(100vh - 120px); overflow-y: auto; }
    .docs-section { scroll-margin-top: 100px; margin-bottom: 60px; }
    .lang-switcher { cursor: pointer; padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 14px; }
    .lang-en { display: block; }
    .lang-bn { display: none; }
    pre { background: #1e1e1e; color: #dcdcdc; padding: 20px; border-radius: 12px; font-size: 14px; border: 1px solid #333; position: relative; }
    .copy-btn { position: absolute; top: 10px; right: 10px; font-size: 12px; background: rgba(255,255,255,0.1); color: #ccc; border: none; padding: 4px 10px; border-radius: 6px; cursor: pointer; }
    code { font-family: 'Fira Code', 'Consolas', monospace; }
    .doc-card { border-radius: 16px; border: 1px solid #eee; transition: all 0.3s; }
    .doc-card:hover { border-color: #ff4757; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .sidebar-link { display: block; padding: 8px 15px; border-radius: 8px; color: #555; text-decoration: none; transition: all 0.2s; font-size: 14px; }
    .sidebar-link:hover, .sidebar-link.active { background: #fff1f2; color: #ff4757; font-weight: 600; }
</style>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 d-none d-md-block">
            <div class="docs-nav">
                <h6 class="text-uppercase small fw-bold text-muted mb-3">Getting Started</h6>
                <a href="#intro" class="sidebar-link active">Introduction / ভূমিকা</a>
                <a href="#install" class="sidebar-link">Installation / সেটআপ</a>
                <a href="#services" class="sidebar-link">Services & DI / সার্ভিস</a>
                
                <h6 class="text-uppercase small fw-bold text-muted mt-4 mb-3">Core Features</h6>
                <a href="#routing" class="sidebar-link">Routing / রাউটিং</a>
                <a href="#controllers" class="sidebar-link">Controllers & DI / কন্ট্রোলার</a>
                <a href="#querybuilder" class="sidebar-link">Query Builder / ডাটাবেজ</a>
                <a href="#middleware" class="sidebar-link">Middleware / মিডলওয়্যার</a>
                <a href="#helpers" class="sidebar-link">Global Helpers / হেল্পার ফাংশন</a>
                
                <h6 class="text-uppercase small fw-bold text-muted mt-4 mb-3">Enterprise & Tools</h6>
                <a href="#api" class="sidebar-link">REST API / রেস্ট এপিআই</a>
                <a href="#cli" class="sidebar-link">CLI PowerPack / সিএলআই টুল</a>
                <a href="#queue" class="sidebar-link">Async Queues / কিউ সিস্টেম</a>
                <a href="#crud" class="sidebar-link">Master CRUD / অটোমেশন</a>
                
                <h6 class="text-uppercase small fw-bold text-muted mt-4 mb-3">Security & Features</h6>
                <a href="#auth" class="sidebar-link">Authentication / প্রমাণীকরণ</a>
                <a href="#validation" class="sidebar-link">Validation / ভ্যালিডেশন</a>
                <a href="#upload" class="sidebar-link">File Upload / ফাইল আপলোড</a>
                <a href="#pagination" class="sidebar-link">Pagination / পেজিনেশন</a>
                <a href="#rate-limit" class="sidebar-link">Rate Limiting / রেট লিমিট</a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-5 bg-light p-3 rounded-4 border">
                <h4 class="fw-bold mb-0 text-dark">NovaFlow Documentation</h4>
                <div class="btn-group">
                    <button id="btnEn" class="btn btn-dark rounded-pill me-2 px-4 shadow-sm" onclick="setLang('en')">English</button>
                    <button id="btnBn" class="btn btn-outline-dark rounded-pill px-4" onclick="setLang('bn')">বাংলা</button>
                </div>
            </div>

            <!-- Introduction -->
            <section id="intro" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark">Introduction</h2>
                    <p class="text-muted lead">NovaFlow is a lightweight, high-performance PHP MVC framework designed for speed, security, and developer happiness. It features a robust Dependency Injection container, a powerful routing engine, and a premium administrative panel out of the box.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark">ভূমিকা (Introduction)</h2>
                    <p class="text-muted lead">NovaFlow হলো একটি লাইটওয়েট এবং উচ্চ মেফিশিয়েন্ট পিএইচপি এমভিসি (MVC) ফ্রেমওয়ার্ক। এটি ডেভেলপারদের কাজের গতি বৃদ্ধি, নিরাপত্তা এবং কোড ম্যানেজমেন্টকে সহজ করার জন্য ডিজাইন করা হয়েছে। এতে রয়েছে ডিপেন্ডেন্সি ইনজেকশন কন্টেইনার, শক্তিশালী রাউটিং এবং একটি প্রিমিয়াম অ্যাডমিন প্যানেল।</p>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="p-3 doc-card bg-white">
                            <i class="fas fa-bolt text-danger fs-3 mb-2"></i>
                            <h6 class="fw-bold mb-1">Fast Performance</h6>
                            <p class="small text-muted mb-0">Engineered for sub-millisecond core overhead.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 doc-card bg-white">
                            <i class="fas fa-plug text-primary fs-3 mb-2"></i>
                            <h6 class="fw-bold mb-1">DI Container</h6>
                            <p class="small text-muted mb-0">Effortless Dependency Injection support.</p>
                        </div>
                    </div>
                </div>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Routing -->
            <section id="routing" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Routing System</h2>
                    <p>Routes are defined in <code>config/routes.php</code>. NovaFlow supports explicit routes, groups, and middleware protection.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">রাউটিং সিস্টেম (Routing)</h2>
                    <p>রাউটগুলো <code>config/routes.php</code> ফাইলে ডিফাইন করা হয়। এটি এক্সপ্লিসিট রাউট, রাউট গ্রুপ এবং মিডলওয়্যার প্রোটেকশন সাপোর্ট করে।</p>
                </div>

                <pre><code>// Simple GET Route
$router->get('/home', 'HomeController@index');

// Protected Group with Prefix
$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function($router) {
    $router->get('/dashboard', 'AdminDashboardController@index');
});</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Query Builder -->
            <section id="querybuilder" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Database: Query Builder</h2>
                    <p>NovaFlow provides an expressive, fluent interface to build and run database queries. It uses PDO under the hood to ensure security against SQL injection.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">ডাটাবেজ: কুয়েরি বিল্ডার (Query Builder)</h2>
                    <p>NovaFlow-তে ডাটাবেজ কুয়েরি চালানোর জন্য একটি সহজ এবং সাবলীল ইন্টারফেস দেওয়া হয়েছে। এটি SQL ইনজেকশন থেকে সুরক্ষা নিশ্চিত করতে PDO ড্রাইভার ব্যবহার করে।</p>
                </div>

                <pre><code>use NovaFlow\Core\DB;

// Fetch all active products
$products = DB::table('products')->where('status', 'active')->get();

// Single record
$user = DB::table('users')->where('id', 1)->first();

// Conditional Sum
$total = DB::table('orders')->sum('total_amount');

// Advanced Joins
$orders = DB::table('orders')
    ->join('users', 'orders.user_id', '=', 'users.id')
    ->select('orders.*', 'users.name as customer_name')
    ->get();</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Services & DI -->
            <section id="services" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Dependency Injection & Services</h2>
                    <p>NovaFlow uses a reflection-based Container for automatic dependency resolution. You can register your business logic in <code>app/services</code>.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">ডিপেন্ডেন্সি ইনজেকশন এবং সার্ভিসেস (DI & Services)</h2>
                    <p>NovaFlow স্বয়ংক্রিয় ডিপেন্ডেন্সি রেজল্যুশনের জন্য রিফ্লেকশন-ভিত্তিক কন্টেইনার ব্যবহার করে। আপনি আপনার বিজনেস লজিকগুলো <code>app/services</code>-এ রেজিস্টার করতে পারেন।</p>
                </div>

<pre><code>// Define a Service
namespace App\Services;

class ProductService {
    public function getLatest() {
        return DB::table('products')->limit(5)->get();
    }
}</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Controllers & DI -->
            <section id="controllers" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Controllers & Dependency Injection</h2>
                    <p>NovaFlow controllers support automatic dependency injection via constructor. The Container resolves dependencies automatically using PHP Reflection.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">কন্ট্রোলার এবং ডিপেন্ডেন্সি ইনজেকশন (Controllers)</h2>
                    <p>NovaFlow কন্ট্রোলারগুলো constructor-এর মাধ্যমে স্বয়ংক্রিয় ডিপেন্ডেন্সি ইনজেকশন সাপোর্ট করে। Container PHP Reflection ব্যবহার করে dependencies resolve করে।</p>
                </div>

                <pre><code>// Basic Controller
namespace App\Controllers;

use NovaFlow\Core\Controller;

class HomeController extends Controller {
    public function index() {
        $this->view('welcome', ['title' => 'Welcome']);
    }
}

// Controller with DI
class ProductController extends Controller {
    protected ProductService $productService;
    protected Request $request;
    protected Response $response;

    // Dependencies automatically injected
    public function __construct(
        ProductService $productService,
        Request $request,
        Response $response
    ) {
        $this->productService = $productService;
        $this->request = $request;
        $this->response = $response;
        parent::__construct();
    }

    public function index() {
        $products = $this->productService->getAll();
        $this->view('products.index', [
            'products' => $products
        ]);
    }

    public function show($id) {
        $product = $this->productService->find($id);
        if (!$product) {
            $this->json(['error' => 'Not found'], 404);
        }
        $this->json($product);
    }
}

// API Controller
class ProductApiController extends ApiController {
    public function index() {
        return $this->success($data, 'Products fetched');
    }

    public function store() {
        return $this->created($product, 'Created successfully');
    }
}</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Middleware -->
            <section id="middleware" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Middleware System</h2>
                    <p>Middleware provides a way to filter HTTP requests before they reach the controller. NovaFlow includes auth, rate limiting, CORS, and custom middleware support.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">মিডলওয���্যার সিস্টেম (Middleware)</h2>
                    <p>মিডলওয়্যার HTTP requests গুলো কন্ট্রোলারে পৌঁছানোর আগে ফিল্টার করার উপায় দেয়। NovaFlow-তে auth, rate limiting, CORS এবং কাস্টম মিডলওয়্যার সাপোর্ট আছে।</p>
                </div>

                <h5 class="mt-4 mb-3">Built-in Middleware</h5>
                <div class="table-responsive">
                    <table class="table table-bordered bg-white">
                        <thead class="bg-light">
                            <tr><th>Middleware</th><th>Description / বর্ণনা</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>auth</code></td><td>Session-based authentication / সেশন প্রমাণীকরণ</td></tr>
                            <tr><td><code>api</code></td><td>JWT API authentication / JWT এপিআই প্রমাণীকরণ</td></tr>
                            <tr><td><code>admin</code></td><td>Admin role check / অ্যাডমিন রোল চেক</td></tr>
                            <tr><td><code>guest</code></td><td>Redirect if logged in / লগইন থাকলে রিডাইরেক্ট</td></tr>
                            <tr><td><code>rate</code></td><td>Rate limiting / রেট লিমিটিং</td></tr>
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-4 mb-3">Create Custom Middleware</h5>
                <pre><code>// app/middleware/CheckAgeMiddleware.php
namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

class CheckAgeMiddleware implements Middleware {
    public function handle(Request $request, Response $response, array $args = []): bool {
        $age = $request->input('age', 0);
        
        if ($age < 18) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Must be 18+']);
            return false;
        }
        
        return true;
    }
}

// Register in Kernel
class Kernel {
    public static array $aliases = [
        'auth' => AuthMiddleware::class,
        'age' => CheckAgeMiddleware::class,
        // ...
    ];
}</code></pre>

                <h5 class="mt-4 mb-3">Use in Routes</h5>
                <pre><code>// Single route
$router->get('/adults', 'AdultController@index')->middleware('age');

// Route group
$router->group(['middleware' => 'auth'], function($router) {
    $router->get('/dashboard', 'AdminController@index');
});

// Multiple middleware
$router->post('/upload', 'UploadController@store')
    ->middleware('auth', 'rate:upload');</code></pre>
            </section>
            <hr class="opacity-10 my-5">

            <!-- Global Helpers -->
            <section id="helpers" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Global Helper Functions</h2>
                    <p>NovaFlow includes several shorthand functions to make development faster. These are available anywhere in your application.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">গ্লোবাল হেল্পার ফাংশন (Helpers)</h2>
                    <p>ডেভেলপমেন্টের গতি বাড়াতে NovaFlow-তে কিছু শর্টহ্যান্ড ফাংশন দেওয়া হয়েছে, যা অ্যাপ্লিকেশনের যেকোনো জায়গা থেকে ব্যবহার করা যায়।</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered bg-white">
                        <thead class="bg-light">
                            <tr><th>Function</th><th>Description / বর্ণনা</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>view($name, $data)</code></td><td>Render a view directly / সরাসরি ভিউ রেন্ডার করা।</td></tr>
                            <tr><td><code>request($key)</code></td><td>Get input data / ইউজার ইনপুট পাওয়া।</td></tr>
                            <tr><td><code>url($path)</code></td><td>Generate full URL / পূর্ণাঙ্গ ইউআরএল তৈরি।</td></tr>
                            <tr><td><code>db()</code></td><td>Access DB driver / ডাটাবেজ ড্রাইভার অ্যাক্সেস।</td></tr>
                            <tr><td><code>dd($var)</code></td><td>Dump and Die for debugging / ডিবাগিং এর জন্য।</td></tr>
                        </tbody>
                    </table>
                </div>

                <pre><code>// Example Usage
$user = db()->fetchOne("SELECT * FROM users WHERE id = ?", [1]);
if(!$user) redirect('/login');

dd(request('email')); // Debug specific input</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- REST API -->
            <section id="api" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Building REST APIs</h2>
                    <p>To build an API, create a controller extending <code>ApiController</code>. It includes helper methods like <code>json()</code>, <code>success()</code>, and <code>error()</code>.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">রেস্ট এপিআই (REST API)</h2>
                    <p>এপিআই তৈরির জন্য <code>ApiController</code> ক্লাসটি এক্সটেন্ড করতে হয়। এতে <code>json()</code>, <code>success()</code> এবং <code>error()</code>-এর মতো প্রয়োজনীয় হেল্পার মেথড রয়েছে।</p>
                </div>

                <pre><code>namespace App\Controllers;

use NovaFlow\Core\ApiController;
use App\Models\ProductModel;

class ProductApiController extends ApiController {
    public function list() {
        $products = ProductModel::all();
        return $this->success($products, 'Products fetched successfully');
    }

    public function detail($id) {
        $product = ProductModel::find($id);
        if (!$product) return $this->error('Product not found', 404);
        return $this->json($product);
    }
}</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- CLI PowerPack -->
            <section id="cli" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">CLI PowerPack</h2>
                    <p>The <code>cli.php</code> tool is your command-center for development. It includes diagnostics, scaffolding, and maintenance tools.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">সিএলআই টুল (CLI PowerPack)</h2>
                    <p><code>cli.php</code> হলো আপনার ডেভেলপমেন্টের কমান্ড-সেন্টার। এতে ডায়াগনস্টিকস, স্ক্যাফোল্ডিং এবং মেইনটেন্যান্স টুলস রয়েছে।</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-white">
                            <h6 class="fw-bold"><i class="fas fa-microchip text-danger me-2"></i> System Health</h6>
                            <p class="small text-muted">Check PHP version, extensions, and folder permissions in one click.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-white">
                            <h6 class="fw-bold"><i class="fas fa-route text-primary me-2"></i> Route List</h6>
                            <p class="small text-muted">View all registered routes, methods, and middlewares in a table.</p>
                        </div>
                    </div>
                </div>
                
                <pre class="mt-3"><code># Run the CLI tool
php cli.php</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Async Queues -->
            <section id="queue" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Async Queue System</h2>
                    <p>Process heavy tasks like emails or generating PDFs in the background to keep the UI fast.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">কিউ সিস্টেম (Async Queues)</h2>
                    <p>ভারী কাজগুলো (যেমন: ইমেইল পাঠানো) ব্যাকগ্রাউন্ডে করার জন্য কিউ সিস্টেম ব্যবহার করুন।</p>
                </div>

                <pre><code>// 1. Create a Job Class
class SendEmailJob extends Job {
    public function handle() {
        // Your logic here
    }
}

// 2. Dispatch to Queue
Queue::push(new SendEmailJob());

// 3. Start Worker (CLI Option 11)
php cli.php --work</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Master CRUD -->
            <section id="crud" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Master CRUD Scaffold</h2>
                    <p>Generate a complete feature set for a database table in seconds. This includes the Model, Controller, Migration, Seeder, and Unit Test.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">মাস্টার ক্রাড (Master CRUD)</h2>
                    <p>ডাটাবেজ টেবিলের জন্য মডেল, কন্ট্রোলার, মাইগ্রেশন, সিডার এবং টেস্ট—সব একসাথে অটোমেটিক তৈরি করুন।</p>
                </div>

                <div class="bg-dark text-white p-3 rounded-3 mb-3">
                    <code>CLI Option 12: Master CRUD Scaffold</code>
                </div>
                
                <p class="small text-muted">Benefit: Saves hours of boilerplate coding by ensuring all related files follow the PSR-4 standard and NovaFlow architecture.</p>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Authentication -->
            <section id="auth" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Authentication System</h2>
                    <p>NovaFlow provides a complete AuthService for user authentication with session management, JWT tokens, and registration.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">প্রমাণীকরণ সিস্টেম (Authentication)</h2>
                    <p>NovaFlow-তে সেশন ম্যানেজমেন্ট, JWT টোকেন এবং রেজিস্ট্রেশন সহ সম্পূর্ণ AuthService আছে।</p>
                </div>

                <pre><code>use App\Services\AuthService;

// Login with Session
$auth = Container::make(AuthService::class);
$result = $auth->login('email', 'password', $remember = false);

if ($result['success']) {
    // Redirect to dashboard
}

// Login via API (returns JWT)
$result = $auth->loginApi('email', 'password');
$token = $result['token']; // Use this token

// Register new user
$auth->register([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secure123'
]);

// Check if logged in
if ($auth->isLoggedIn()) {
    $user = $auth->getUser();
}

// Generate JWT Token
$token = $auth->generateToken($user);</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Validation -->
            <section id="validation" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Input Validation</h2>
                    <p>NovaFlow includes a powerful Validator class for form and API input validation with customizable rules.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">ইনপুট ভ্যালিডেশন (Validation)</h2>
                    <p>ফর্ম এবং API ইনপুট ভ্যালিডেশনের জন্য শক্তিশালী Validator ক্লাস আছে।</p>
                </div>

                <pre><code>use NovaFlow\Core\Validator;

$validator = Validator::make($data = [
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => '123456'
], $rules = [
    'name' => 'required|min:3|max:50',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:6'
]);

if ($validator->hasErrors()) {
    $errors = $validator->errors();
    return;
}

// In Controller (automatic redirect with flash)
$this->validate([
    'name' => 'required|min:3',
    'email' => 'required|email'
]);</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- File Upload -->
            <section id="upload" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">File Upload Handler</h2>
                    <p>Secure file upload with type validation, size limits, and automatic image processing.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">ফাইল আপলোড হ্যান��ডলার (File Upload)</h2>
                    <p>টাইপ ভ্যালিডেশন, সাইজ লিমিট এবং অটোমেটিক ইমেজ প্রসেসিং সহ নিরাপদ ফাইল আপলোড।</p>
                </div>

                <pre><code>use NovaFlow\Core\UploadHandler;

// Single file upload
$upload = new UploadHandler();
$result = $upload->upload('avatar');

// Configure upload
$upload = new UploadHandler('/public/uploads')
    ->allowedTypes(['image/jpeg', 'image/png', 'image/webp'])
    ->maxSize(2097152) // 2MB
    ->generateNewName(true)
    ->processImage(800, 600, 85); // Resize, max width/height, quality

$result = $upload->upload('avatar');
if ($result['success']) {
    $filename = $result['filename'];
    $path = $result['path'];
}

// Multiple file upload
$results = $upload->uploadMultiple('attachments');</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Pagination -->
            <section id="pagination" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Pagination</h2>
                    <p>Easy pagination helper for lists and API responses.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">পেজিনেশন (Pagination)</h2>
                    <p>লিস্ট এবং API রেসপন্সের জন্য সহজ পেজিনেশন হেল্পার।</p>
                </div>

                <pre><code>use NovaFlow\Core\Pagination;

// In Controller
$page = (int) request('page', 1);
$perPage = 20;
$total = UserModel::query()->count();
$users = UserModel::query()
    ->orderBy('id', 'DESC')
    ->limit($perPage, ($page - 1) * $perPage)
    ->get();

$pagination = Pagination::make($total, $perPage, $page, '/admin/users');
$html = $pagination->render();

// Pass to view
$this->view('admin.users', [
    'users' => $users,
    'pagination' => $html
]);

// API Response with Pagination
ApiResponse::success([
    'data' => $users,
    'pagination' => $pagination->toArray()
]);</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <!-- Rate Limiting -->
            <section id="rate-limit" class="docs-section">
                <div class="lang-en">
                    <h2 class="fw-bold text-dark mb-4">Rate Limiting</h2>
                    <p>Protect your API from brute force attacks with rate limiting middleware.</p>
                </div>
                <div class="lang-bn">
                    <h2 class="fw-bold text-dark mb-4">রেট লিমিটিং (Rate Limiting)</h2>
                    <p>রেট লিমিটিং মিডলওয়্যার দিয়ে ব্রুট ফোর্স অ্যাটাক থেকে সুরক্ষা।</p>
                </div>

                <pre><code>// In routes.php
$router->group(['middleware' => 'rate:login'], function($router) {
    $router->post('/login', 'AuthApiController@login');
});

// Rate limit options: default, login, api, upload
// default: 60 req/min
// login: 5 attempts/5min
// api: 100 req/min
// upload: 10/hour

// Custom rate limit
$router->middleware('rate:default');</code></pre>
            </section>

            <hr class="opacity-10 my-5">

            <div class="bg-light p-4 rounded-4 text-center">
                <h5 class="fw-bold mb-3">Need more help?</h5>
                <p class="text-muted small">Check the <code>cli.php</code> tool in your root to generate models and manage your database effortlessly.</p>
                <a href="<?= BASE_URL ?>/" class="btn btn-danger rounded-pill px-5">Back to Home</a>
            </div>
        </div>
    </div>
</div>

<script>
    function setLang(lang) {
        if (lang === 'bn') {
            $('.lang-en').hide();
            $('.lang-bn').show();
            $('#btnBn').removeClass('btn-outline-dark').addClass('btn-dark');
            $('#btnEn').removeClass('btn-dark').addClass('btn-outline-dark');
        } else {
            $('.lang-bn').hide();
            $('.lang-en').show();
            $('#btnEn').removeClass('btn-outline-dark').addClass('btn-dark');
            $('#btnBn').removeClass('btn-dark').addClass('btn-outline-dark');
        }
    }

    // Smooth scroll for sidebar links
    $(document).ready(function() {
        $('.sidebar-link').on('click', function(e) {
            $('.sidebar-link').removeClass('active');
            $(this).addClass('active');
        });
    });
</script>
