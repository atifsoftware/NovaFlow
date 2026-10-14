<?php

namespace NovaFlow\Core;

use NovaFlow\Core\Database\DatabaseManager;
use NovaFlow\Core\Database\Contracts\ConnectionInterface;

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
        // Register Database Manager singleton
        $this->container->singleton(DatabaseManager::class, function () {
            return new DatabaseManager();
        });

        // Bind Database Driver and Connection interface to default connection
        $this->container->singleton(DatabaseInterface::class, function () {
            return $this->container->make(DatabaseManager::class)->connection();
        });

        $this->container->singleton(ConnectionInterface::class, function () {
            return $this->container->make(DatabaseManager::class)->connection();
        });

        // Register ProductService if present
        if (class_exists(\App\Services\ProductService::class)) {
            $this->container->singleton(\App\Services\ProductService::class, function () {
                return new \App\Services\ProductService();
            });
        }
    }

    /**
     * Bootstrap any application services
     */
    public function boot()
    {
        // Example: Global model setup or observer registration
    }
}
