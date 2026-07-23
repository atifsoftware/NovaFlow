<p align="center">
    <img src="https://raw.githubusercontent.com/novaflow/novaflow/main/logo.png" alt="NovaFlow Logo" width="200">
</p>

<h1 align="center">NovaFlow PHP Framework</h1>

<p align="center">
    <a href="https://packagist.org/packages/novaflow/framework">
        <img src="https://img.shields.io/packagist/v/novaflow/framework.svg" alt="Version">
    </a>
    <a href="https://packagist.org/packages/novaflow/framework">
        <img src="https://img.shields.io/packagist/dt/novaflow/framework.svg" alt="Downloads">
    </a>
    <a href="https://opensource.org/licenses/MIT">
        <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
    </a>
    <a href="https://php.net">
        <img src="https://img.shields.io/badge/PHP-8.1+-777BB4.svg" alt="PHP Version">
    </a>
</p>

<p align="center">
    🚀 A lightweight, high-performance PHP MVC framework with premium admin panel
</p>

---

## ✨ Features

- **MVC Architecture** - Clean separation of concerns
- **Dependency Injection** - Built-in IoC Container
- **RESTful API** - Built-in API controller with JWT support
- **Authentication** - Session & JWT based auth
- **Rate Limiting** - Built-in brute force protection
- **Query Builder** - Secure database operations with PDO
- **Active Record Models** - Eloquent-like model support
- **Middleware System** - Flexible request filtering
- **CLI Tools** - Code generation & database migrations
- **Bilingual Docs** - English & Bengali documentation

---

## 📋 Requirements

- PHP 8.1 or higher
- MySQL 5.7+ / MariaDB 10.2+
- Apache/Nginx with mod_rewrite

---

## 🛠️ Installation

### Via Composer (Recommended)

```bash
composer create-project novaflow/framework myproject
cd myproject
```

### Manual Installation

```bash
# Clone the repository
git clone https://github.com/yourusername/NovaFlow.git myproject
cd myproject

# Install dependencies
composer install

# Configure environment
cp .env.example .env

# Import database
mysql -u root -p novaflow_db < novaflow_db.sql
```

---

## ⚙️ Configuration

### Database Setup (.env)

```env
DB_HOST=localhost
DB_NAME=novaflow_db
DB_USER=root
DB_PASS=
DB_DRIVER=pdo

JWT_SECRET=your-secret-key-here
APP_ENV=local
```

### Routes (`config/routes.php`)

```php
// Simple route
$router->get('/', 'HomeController@index');

// Protected routes
$router->group(['prefix' => 'admin', 'middleware' => 'auth'], function($router) {
    $router->get('/dashboard', 'AdminDashboardController@index');
});

// API routes
$router->group(['prefix' => 'api/v1'], function($router) {
    $router->post('/login', 'AuthApiController@login');
});
```

---

## 📖 Documentation

- [English Documentation](https://novaflow.dev/docs)
- [বাংলা ডকুমেন্টেশন](https://novaflow.dev/docs?lang=bn)

### Quick Examples

#### Create a Controller

```php
namespace App\Controllers;

use NovaFlow\Core\Controller;

class ProductController extends Controller {
    public function index() {
        $products = ProductModel::all();
        $this->view('products.index', ['products' => $products]);
    }
}
```

#### Database Query

```php
use NovaFlow\Core\DB;

// Get all active products
$products = DB::table('products')->where('status', 'active')->get();

// Get single record
$user = DB::table('users')->where('email', $email)->first();
```

#### Authentication

```php
use App\Services\AuthService;

$auth = Container::make(AuthService::class);
$result = $auth->login('email', 'password');

if ($result['success']) {
    // Redirect to dashboard
}

// API Login (returns JWT)
$result = $auth->loginApi('email', 'password');
$token = $result['token'];
```

#### File Upload

```php
use NovaFlow\Core\UploadHandler;

$upload = new UploadHandler();
$upload->allowedTypes(['image/jpeg', 'image/png'])
       ->maxSize(2097152)
       ->processImage(800, 600, 85);

$result = $upload->upload('avatar');
```

---

## 🗂️ Project Structure

```
NovaFlow/
├── app/
│   ├── controllers/      # Controllers
│   ├── models/          # Database models
│   ├── services/        # Business logic
│   ├── middleware/      # Request filters
│   ├── views/           # Templates
│   └── libraries/       # Core classes
├── config/              # Configuration
├── public/              # Public assets
├── storage/             # Logs, cache
├── tests/               # Unit tests
├── cli.php              # CLI tools
└── composer.json        # Dependencies
```

---

## 🧪 Testing

```bash
# Run tests
composer test

# Run specific test
./vendor/bin/phpunit tests/Unit/SecurityTest.php
```

---

## 🔧 CLI Commands

```bash
# Show help
php cli.php

# System health check
php cli.php --health

# List routes
php cli.php --routes

# Run queue worker
php cli.php --work
```

---

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) first.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- Laravel (for inspiration)
- Bootstrap 5
- Font Awesome

---

<p align="center">
    Made with ❤️ by <a href="https://github.com/yourusername">Your Name</a>
</p>
