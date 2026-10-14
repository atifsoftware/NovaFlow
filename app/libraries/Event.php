<?php

namespace NovaFlow\Core;

use NovaFlow\Core\Events\Dispatcher;

/**
 * Event Facade / Static Proxy
 * Provides easy static access to the framework Event Dispatcher.
 */
class Event
{
    private static ?Dispatcher $dispatcher = null;

    /**
     * Get or initialize the event dispatcher
     */
    public static function getDispatcher(): Dispatcher
    {
        if (self::$dispatcher === null) {
            $container = Container::getInstance();
            if ($container->has(Dispatcher::class)) {
                self::$dispatcher = $container->make(Dispatcher::class);
            } else {
                self::$dispatcher = new Dispatcher();
                $container->singleton(Dispatcher::class, fn() => self::$dispatcher);
            }
        }

        return self::$dispatcher;
    }

    /**
     * Set a custom dispatcher instance (useful in tests)
     */
    public static function setDispatcher(?Dispatcher $dispatcher): void
    {
        self::$dispatcher = $dispatcher;
    }

    /**
     * Register an event listener
     */
    public static function listen(string|array $events, callable|string $listener): void
    {
        self::getDispatcher()->listen($events, $listener);
    }

    /**
     * Dispatch an event
     */
    public static function dispatch(string|object $event, mixed $payload = []): array
    {
        return self::getDispatcher()->dispatch($event, $payload);
    }

    /**
     * Check if event has listeners
     */
    public static function hasListeners(string $eventName): bool
    {
        return self::getDispatcher()->hasListeners($eventName);
    }

    /**
     * Forget listeners for an event
     */
    public static function forget(string $event): void
    {
        self::getDispatcher()->forget($event);
    }

    /**
     * Flush all listeners
     */
    public static function flush(): void
    {
        self::getDispatcher()->flush();
    }
}
