<?php

namespace NovaFlow\Core;

/**
 * App Service Provider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register()
    {
        // Register ProductService
        $this->container->singleton(\App\Services\ProductService::class, function () {
            return new \App\Services\ProductService();
        });

        // Register AuthService
        $this->container->singleton(\App\Services\AuthService::class, function () {
            return new \App\Services\AuthService();
        });

        // Bind Database Driver based on config
        $this->container->singleton(DatabaseInterface::class, function () {
            // JahinMart primarily uses PDO, so we default to PDODriver
            $driver = defined('DB_DRIVER') ? DB_DRIVER : 'pdo';

            if ($driver === 'pdo') {
                return new PDODriver();
            }

            // Fallback to default mysqli if specifically requested (though PDODriver is preferred here)
            return new PDODriver(); 
        });
    }

    /**
     * Bootstrap any application services
     */
    public function boot()
    {
        // Example: Global model setup or observer registration
    }
}
