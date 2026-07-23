<?php

namespace NovaFlow\Core;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunction;
use ReflectionNamedType;

/**
 * Service Container (IoC)
 * Manages object instantiation and dependency resolution
 */
class Container
{
    protected static $instance;
    protected $bindings = [];
    protected $instances = [];
    protected $aliases = [];

    /**
     * Get singleton instance of the container
     */
    public static function getInstance(): Container
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Register a binding with the container
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Register a singleton binding
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Register an existing instance as shared
     */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
        return $instance;
    }

    /**
     * Alias a type to a different name
     */
    public function alias($abstract, $alias)
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Resolve the given type from the container
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        // If it's a singleton and already instantiated, return it
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);

        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->make($concrete, $parameters);
        }

        // If it's a singleton, store the instance
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get an instance from the container (PSR-11)
     */
    public function get($id)
    {
        try {
            return $this->make($id);
        } catch (Exception $e) {
            throw new Exception("Target [$id] is not resolvable. " . $e->getMessage());
        }
    }

    /**
     * Check if the given abstract type has been bound or aliased (PSR-11)
     */
    public function has($abstract)
    {
        return isset($this->bindings[$abstract]) || 
               isset($this->instances[$abstract]) || 
               isset($this->aliases[$abstract]);
    }

    /**
     * Check if a type is shared
     */
    protected function isShared($abstract)
    {
        return isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared'] === true;
    }

    /**
     * Get the concrete type for an abstract
     */
    protected function getConcrete($abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    /**
     * Get the abstract for an alias
     */
    protected function getAlias($abstract)
    {
        return isset($this->aliases[$abstract]) ? $this->getAlias($this->aliases[$abstract]) : $abstract;
    }

    /**
     * Check if the concrete is buildable
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * Instantiate a concrete instance of the given type
     */
    public function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies, $parameters);

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all dependencies for the constructor
     */
    protected function resolveDependencies(array $dependencies, array $parameters)
    {
        $results = [];
        $index = 0;

        foreach ($dependencies as $dependency) {
            // Check if the dependency is provided by name in parameters
            if (isset($parameters[$dependency->name])) {
                $results[] = $parameters[$dependency->name];
                continue;
            }

            // Fallback: Use positional parameter if available
            if (isset($parameters[$index])) {
                $results[] = $parameters[$index];
                $index++;
                continue;
            }

            $results[] = $this->resolve($dependency);
        }

        return $results;
    }

    /**
     * Resolve a single dependency
     */
    protected function resolve(ReflectionParameter $parameter)
    {
        $type = $parameter->getType();

        if (is_null($type) || $type->isBuiltin() || !$type instanceof ReflectionNamedType) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            if ($parameter->allowsNull()) {
                return null;
            }

            throw new Exception("Unresolvable dependency [{$parameter->name}] in class {$parameter->getDeclaringClass()->getName()}");
        }

        return $this->make($type->getName());
    }

    /**
     * Call the given Closure / class@method and inject its dependencies
     */
    public function call($callback, array $parameters = [])
    {
        if (is_string($callback) && strpos($callback, '@') !== false) {
            $callback = explode('@', $callback);
        }

        if (is_array($callback)) {
            return $this->callMethod($callback[0], $callback[1], $parameters);
        }

        return $this->callClosure($callback, $parameters);
    }

    /**
     * Call a method on an object with DI
     */
    protected function callMethod($instance, $method, array $parameters = [])
    {
        if (is_string($instance)) {
            $instance = $this->make($instance);
        }

        $reflector = new ReflectionMethod($instance, $method);
        $dependencies = $reflector->getParameters();
        $instances = $this->resolveDependencies($dependencies, $parameters);

        return $reflector->invokeArgs($instance, $instances);
    }

    /**
     * Call a closure with DI
     */
    protected function callClosure(Closure $callback, array $parameters = [])
    {
        $reflector = new ReflectionFunction($callback);
        $dependencies = $reflector->getParameters();
        $instances = $this->resolveDependencies($dependencies, $parameters);

        return $callback(...$instances);
    }
}
