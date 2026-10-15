<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Container;
use Exception;

interface LoggerInterface
{
    public function log(string $message): string;
}

class FileLogger implements LoggerInterface
{
    public function log(string $message): string
    {
        return "Logged to file: $message";
    }
}

class DependencyLevelThree
{
    public string $name = 'Level 3 Ready';
}

class DependencyLevelTwo
{
    public DependencyLevelThree $level3;

    public function __construct(DependencyLevelThree $level3)
    {
        $this->level3 = $level3;
    }
}

class DependencyLevelOne
{
    public DependencyLevelTwo $level2;
    public string $env;

    public function __construct(DependencyLevelTwo $level2, string $env = 'production')
    {
        $this->level2 = $level2;
        $this->env = $env;
    }
}

class ServiceWithInterface
{
    public LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run(): string
    {
        return $this->logger->log('Service executed');
    }
}

class SampleActionClass
{
    public function execute(DependencyLevelThree $l3, string $actionName): string
    {
        return "Executed $actionName with {$l3->name}";
    }
}

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function testTransientBindingCreatesNewInstances(): void
    {
        $this->container->bind(DependencyLevelThree::class);

        $instance1 = $this->container->make(DependencyLevelThree::class);
        $instance2 = $this->container->make(DependencyLevelThree::class);

        $this->assertInstanceOf(DependencyLevelThree::class, $instance1);
        $this->assertInstanceOf(DependencyLevelThree::class, $instance2);
        $this->assertNotSame($instance1, $instance2, "Transient bindings must create distinct object instances");
    }

    public function testSingletonBindingReturnsSameInstance(): void
    {
        $this->container->singleton(DependencyLevelThree::class);

        $instance1 = $this->container->make(DependencyLevelThree::class);
        $instance2 = $this->container->make(DependencyLevelThree::class);

        $this->assertSame($instance1, $instance2, "Singleton bindings must return the exact same object reference");
    }

    public function testExistingInstanceRegistrationAndForget(): void
    {
        $dummy = new \stdClass();
        $dummy->app = 'NovaFlow Enterprise';

        $this->container->instance('app_config', $dummy);
        $this->assertTrue($this->container->has('app_config'));
        $this->assertSame($dummy, $this->container->make('app_config'));

        $this->container->forgetInstance('app_config');
        $this->assertFalse($this->container->has('app_config'));
    }

    public function testRecursiveAutoWiringOfDependencies(): void
    {
        // Neither Level 1, 2, nor 3 are manually bound. Container should resolve them recursively!
        $level1 = $this->container->make(DependencyLevelOne::class);

        $this->assertInstanceOf(DependencyLevelOne::class, $level1);
        $this->assertInstanceOf(DependencyLevelTwo::class, $level1->level2);
        $this->assertInstanceOf(DependencyLevelThree::class, $level1->level2->level3);
        $this->assertEquals('Level 3 Ready', $level1->level2->level3->name);
        $this->assertEquals('production', $level1->env, "Default constructor parameter should be respected");
    }

    public function testOverridingDefaultParametersDuringMake(): void
    {
        $level1 = $this->container->make(DependencyLevelOne::class, ['env' => 'staging']);
        $this->assertEquals('staging', $level1->env);
    }

    public function testInterfaceToImplementationBinding(): void
    {
        $this->container->bind(LoggerInterface::class, FileLogger::class);

        $service = $this->container->make(ServiceWithInterface::class);
        $this->assertInstanceOf(FileLogger::class, $service->logger);
        $this->assertEquals('Logged to file: Service executed', $service->run());
    }

    public function testClosureResolutionWithContainerParameter(): void
    {
        $this->container->bind('custom_factory', function ($c) {
            $l3 = $c->make(DependencyLevelThree::class);
            $l3->name = 'Factory Created';
            return $l3;
        });

        $obj = $this->container->make('custom_factory');
        $this->assertEquals('Factory Created', $obj->name);
    }

    public function testContainerCallWithMethodAndClosureDependencyInjection(): void
    {
        // 1. Call Closure with DI
        $result1 = $this->container->call(function (DependencyLevelThree $l3, $suffix) {
            return $l3->name . ' - ' . $suffix;
        }, ['suffix' => 'Success']);

        $this->assertEquals('Level 3 Ready - Success', $result1);

        // 2. Call Method on class instance
        $action = new SampleActionClass();
        $result2 = $this->container->call([$action, 'execute'], ['actionName' => 'BackupJob']);
        $this->assertEquals('Executed BackupJob with Level 3 Ready', $result2);

        // 3. Call Class@method string syntax
        $result3 = $this->container->call(SampleActionClass::class . '@execute', ['actionName' => 'CleanupTask']);
        $this->assertEquals('Executed CleanupTask with Level 3 Ready', $result3);
    }

    public function testUnresolvableNonInstantiableClassThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Class Tests\\Unit\\Core\\LoggerInterface is not instantiable");

        // Attempting to resolve an unbound interface directly
        $this->container->make(LoggerInterface::class);
    }
}
