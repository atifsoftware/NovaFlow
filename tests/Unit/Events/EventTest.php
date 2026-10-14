<?php

namespace Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Events\Event;
use NovaFlow\Core\Events\Dispatcher;
use NovaFlow\Core\Event as EventFacade;

// Test Event classes
class OrderShippedEvent extends Event
{
    public function __construct(public string $orderId, public float $amount)
    {
    }
}

class CancellableEvent extends Event
{
    public bool $cancelled = false;
}

// Test Listener class
class LogShipmentListener
{
    public static array $logs = [];

    public function handle(OrderShippedEvent $event): string
    {
        self::$logs[] = "Shipped: " . $event->orderId;
        return "Logged " . $event->orderId;
    }
}

class EventTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EventFacade::flush();
        LogShipmentListener::$logs = [];
    }

    protected function tearDown(): void
    {
        EventFacade::flush();
        parent::tearDown();
    }

    public function testClosureListenerReceivesEvent()
    {
        $received = null;

        EventFacade::listen('user.registered', function ($payload) use (&$received) {
            $received = $payload;
            return 'processed';
        });

        $this->assertTrue(EventFacade::hasListeners('user.registered'));

        $responses = EventFacade::dispatch('user.registered', ['user_id' => 42]);

        $this->assertEquals(['user_id' => 42], $received);
        $this->assertEquals(['processed'], $responses);
    }

    public function testClassListenerResolution()
    {
        EventFacade::listen(OrderShippedEvent::class, LogShipmentListener::class);

        $event = new OrderShippedEvent('ORD-1001', 49.99);
        $responses = EventFacade::dispatch($event);

        $this->assertCount(1, LogShipmentListener::$logs);
        $this->assertEquals('Shipped: ORD-1001', LogShipmentListener::$logs[0]);
        $this->assertEquals(['Logged ORD-1001'], $responses);
    }

    public function testWildcardListeners()
    {
        $wildcardFired = false;
        $allFired = false;

        EventFacade::listen('order.*', function ($payload) use (&$wildcardFired) {
            $wildcardFired = true;
        });

        EventFacade::listen('*', function ($payload) use (&$allFired) {
            $allFired = true;
        });

        EventFacade::dispatch('order.created', ['order' => 123]);

        $this->assertTrue($wildcardFired);
        $this->assertTrue($allFired);
    }

    public function testStopPropagationFromEventObject()
    {
        $listener1Ran = false;
        $listener2Ran = false;

        EventFacade::listen(CancellableEvent::class, function (CancellableEvent $e) use (&$listener1Ran) {
            $listener1Ran = true;
            $e->stopPropagation();
        });

        EventFacade::listen(CancellableEvent::class, function (CancellableEvent $e) use (&$listener2Ran) {
            $listener2Ran = true;
        });

        $event = new CancellableEvent();
        EventFacade::dispatch($event);

        $this->assertTrue($listener1Ran);
        $this->assertFalse($listener2Ran);
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testListenerReturningFalseStopsPropagation()
    {
        $ran = [];

        EventFacade::listen('test.chain', function () use (&$ran) {
            $ran[] = 1;
            return false; // Stop chain
        });

        EventFacade::listen('test.chain', function () use (&$ran) {
            $ran[] = 2;
        });

        EventFacade::dispatch('test.chain');

        $this->assertEquals([1], $ran);
    }

    public function testForgetAndFlushListeners()
    {
        EventFacade::listen('notify.email', fn() => 'email');
        EventFacade::listen('notify.sms', fn() => 'sms');

        $this->assertTrue(EventFacade::hasListeners('notify.email'));
        $this->assertTrue(EventFacade::hasListeners('notify.sms'));

        EventFacade::forget('notify.email');
        $this->assertFalse(EventFacade::hasListeners('notify.email'));
        $this->assertTrue(EventFacade::hasListeners('notify.sms'));

        EventFacade::flush();
        $this->assertFalse(EventFacade::hasListeners('notify.sms'));
    }

    public function testEventHelperFunction()
    {
        $called = false;
        EventFacade::listen('global.test', function () use (&$called) {
            $called = true;
        });

        event('global.test');
        $this->assertTrue($called);
    }
}
