<?php

namespace NovaFlow\Core;

/**
 * Base Service Provider
 */
abstract class ServiceProvider
{
    /**
     * The service container instance
     * @var Container
     */
    protected $container;

    /**
     * Create a new service provider instance
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register any application services
     */
    abstract public function register();

    /**
     * Bootstrap any application services
     */
    public function boot()
    {
        //
    }
}
