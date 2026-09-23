<?php

namespace Livewire\Tests;

use Livewire\EventBus;

class EventBusUnitTest extends \Tests\TestCase
{
    public function test_empty_listener_buckets_use_fast_path()
    {
        $bus = new EventBus;

        $forward = 'original';
        $finisher = $bus->trigger('unhandled.event', 'foo');

        $result = $finisher($forward);

        $this->assertSame('original', $result);
        $this->assertSame('original', $forward);
    }

    public function test_before_listener_removes_main_listener_during_same_dispatch()
    {
        $bus = new EventBus;
        $invocations = [];

        $mainListener = function () use (&$invocations) {
            $invocations[] = 'main';
        };

        $bus->before('event', function () use ($bus, $mainListener, &$invocations) {
            $invocations[] = 'before';
            // Unregister main listener while dispatch is in progress
            $bus->off('event', $mainListener);
        });

        $bus->on('event', $mainListener);

        // During the current dispatch, the snapshot must still execute main listener
        $bus->trigger('event');

        $this->assertSame(['before', 'main'], $invocations);

        // On the next trigger, main listener should no longer be called
        $invocations = [];
        $bus->trigger('event');

        $this->assertSame(['before'], $invocations);
    }

    public function test_listener_removes_sibling_listener_during_same_dispatch()
    {
        $bus = new EventBus;
        $invocations = [];

        $secondListener = function () use (&$invocations) {
            $invocations[] = 'second';
        };

        $bus->on('event', function () use ($bus, $secondListener, &$invocations) {
            $invocations[] = 'first';
            $bus->off('event', $secondListener);
        });

        $bus->on('event', $secondListener);

        // The snapshot ensures second still runs on this trigger
        $bus->trigger('event');
        $this->assertSame(['first', 'second'], $invocations);

        // On subsequent trigger, second is gone
        $invocations = [];
        $bus->trigger('event');
        $this->assertSame(['first'], $invocations);
    }

    public function test_listener_registers_another_listener_during_same_dispatch()
    {
        $bus = new EventBus;
        $invocations = [];

        $bus->on('event', function () use ($bus, &$invocations) {
            $invocations[] = 'first';
            $bus->on('event', function () use (&$invocations) {
                $invocations[] = 'dynamically_added';
            });
        });

        // Current dispatch should NOT execute the dynamically added listener
        $bus->trigger('event');
        $this->assertSame(['first'], $invocations);

        // Next dispatch SHOULD execute the dynamically added listener
        $invocations = [];
        $bus->trigger('event');
        $this->assertSame(['first', 'dynamically_added'], $invocations);
    }

    public function test_finishers_retain_forwarding_and_reference_behavior()
    {
        $bus = new EventBus;

        $bus->before('filter', function () {
            return function (&$value) {
                $value .= ':before';
                return $value;
            };
        });

        $bus->on('filter', function () {
            return function (&$value) {
                $value .= ':main';
                return $value;
            };
        });

        $bus->after('filter', function () {
            return function (&$value) {
                $value .= ':after';
                return $value;
            };
        });

        $finisher = $bus->trigger('filter');

        $payload = 'start';
        $result = $finisher($payload);

        $this->assertSame('start:before:main:after', $payload);
        $this->assertSame('start:before:main:after', $result);
    }
}
