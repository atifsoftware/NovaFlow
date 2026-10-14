<?php

namespace NovaFlow\Core\Events;

use NovaFlow\Core\Container;
use Closure;

/**
 * NovaFlow Event Dispatcher
 * Provides decoupled communication between components using publish-subscribe pattern.
 */
class Dispatcher
{
    /**
     * The registered event listeners
     */
    protected array $listeners = [];

    /**
     * The wildcard listeners
     */
    protected array $wildcards = [];

    /**
     * Register an event listener with the dispatcher
     *
     * @param string|array $events
     * @param callable|string $listener
     */
    public function listen(string|array $events, callable|string $listener): void
    {
        foreach ((array) $events as $event) {
            if (str_contains($event, '*')) {
                $this->setupWildcardListener($event, $listener);
            } else {
                $this->listeners[$event][] = $listener;
            }
        }
    }

    /**
     * Setup a wildcard listener
     */
    protected function setupWildcardListener(string $event, callable|string $listener): void
    {
        $this->wildcards[$event][] = $listener;
    }

    /**
     * Determine if a given event has listeners
     */
    public function hasListeners(string $eventName): bool
    {
        return !empty($this->listeners[$eventName]) || !empty($this->getWildcardListeners($eventName));
    }

    /**
     * Fire an event and call the listeners
     *
     * @param string|object $event
     * @param mixed $payload
     * @return array Array of listener responses
     */
    public function dispatch(string|object $event, mixed $payload = []): array
    {
        $responses = [];

        if (is_object($event)) {
            $eventName = get_class($event);
            $eventPayload = $event;
        } else {
            $eventName = $event;
            $eventPayload = $payload;
        }

        $listeners = $this->getListeners($eventName);

        foreach ($listeners as $listener) {
            $response = $this->callListener($listener, $eventPayload);

            if ($response !== null) {
                $responses[] = $response;
            }

            // Stop if listener explicitly returned false
            if ($response === false) {
                break;
            }

            // Stop if event object stopped propagation
            if ($eventPayload instanceof Event && $eventPayload->isPropagationStopped()) {
                break;
            }
        }

        return $responses;
    }

    /**
     * Call the given listener with payload
     */
    protected function callListener(callable|string $listener, mixed $payload): mixed
    {
        if (is_callable($listener)) {
            return $listener($payload);
        }

        if (is_string($listener)) {
            $container = Container::getInstance();
            $instance = $container->has($listener) 
                ? $container->make($listener) 
                : new $listener();

            if (method_exists($instance, 'handle')) {
                return $instance->handle($payload);
            }

            if (is_callable($instance)) {
                return $instance($payload);
            }
        }

        return null;
    }

    /**
     * Get all listeners for a given event name
     */
    public function getListeners(string $eventName): array
    {
        $listeners = $this->listeners[$eventName] ?? [];

        return array_merge($listeners, $this->getWildcardListeners($eventName));
    }

    /**
     * Get wildcard listeners that match the event name
     */
    protected function getWildcardListeners(string $eventName): array
    {
        $wildcards = [];

        foreach ($this->wildcards as $pattern => $listeners) {
            $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';
            if (preg_match($regex, $eventName)) {
                $wildcards = array_merge($wildcards, $listeners);
            }
        }

        return $wildcards;
    }

    /**
     * Remove a set of listeners from the dispatcher
     */
    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
        unset($this->wildcards[$event]);
    }

    /**
     * Clear all registered listeners
     */
    public function flush(): void
    {
        $this->listeners = [];
        $this->wildcards = [];
    }
}
