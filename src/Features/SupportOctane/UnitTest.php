<?php

namespace Livewire\Features\SupportOctane;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\EventBus;
use Livewire\Attributes\Computed;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use Livewire\Features\SupportStreaming\SupportStreaming;
use Livewire\Features\SupportRedirects\SupportRedirects;
use Livewire\Drawer\BaseUtils;

class UnitTest extends \Tests\TestCase
{
    public function test_component_stacks_are_cleared_on_flush_state()
    {
        HandleComponents::$componentStack = [new \stdClass()];
        HandleComponents::$renderStack = [new \stdClass()];

        Livewire::flushState();

        $this->assertEmpty(HandleComponents::$componentStack);
        $this->assertEmpty(HandleComponents::$renderStack);
    }

    public function test_streaming_response_is_cleared_on_flush_state()
    {
        $reflection = new \ReflectionClass(SupportStreaming::class);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);

        $property->setValue(null, new \stdClass());

        Livewire::flushState();

        $this->assertNull($property->getValue(null));
    }

    public function test_lifecycle_hooks_caches_are_cleared_on_flush_state()
    {
        $reflection = new \ReflectionClass(SupportLifecycleHooks::class);

        $traitCache = $reflection->getProperty('traitCache');
        $traitCache->setAccessible(true);

        $methodCache = $reflection->getProperty('methodCache');
        $methodCache->setAccessible(true);

        $traitCache->setValue(null, ['SomeClass' => ['trait1', 'trait2']]);
        $methodCache->setValue(null, ['SomeClass::someMethod' => true]);

        Livewire::flushState();

        $this->assertEmpty($traitCache->getValue(null));
        $this->assertEmpty($methodCache->getValue(null));
    }

    public function test_redirector_cache_stack_is_cleared_on_flush_state()
    {
        SupportRedirects::$redirectorCacheStack = [new \stdClass()];

        Livewire::flushState();

        $this->assertEmpty(SupportRedirects::$redirectorCacheStack);
    }

    public function test_reflection_cache_is_cleared_on_flush_state()
    {
        $reflection = new \ReflectionClass(BaseUtils::class);
        $property = $reflection->getProperty('reflectionCache');
        $property->setAccessible(true);

        $property->setValue(null, ['SomeClass' => ['prop1' => [], 'prop2' => []]]);

        Livewire::flushState();

        $this->assertEmpty($property->getValue(null));
    }

    public function test_flush_state_clears_component_stack_after_exception_during_mount()
    {
        HandleComponents::$componentStack = [];

        $componentClass = new class extends Component {
            public function mount()
            {
                throw new \Exception('Test exception during mount');
            }

            public function render()
            {
                return '<div></div>';
            }
        };

        try {
            Livewire::test($componentClass);
        } catch (\Exception $e) {
            // Expected
        }

        Livewire::flushState();

        $this->assertEmpty(HandleComponents::$componentStack);
    }

    public function test_repeated_requests_dont_accumulate_static_state()
    {
        for ($i = 0; $i < 5; $i++) {
            HandleComponents::$componentStack[] = new \stdClass();
            SupportRedirects::$redirectorCacheStack[] = new \stdClass();

            Livewire::flushState();

            $this->assertEmpty(HandleComponents::$componentStack);
            $this->assertEmpty(SupportRedirects::$redirectorCacheStack);
        }
    }

    public function test_event_bus_listeners_dont_grow_across_multiple_requests()
    {
        $countListeners = function () {
            $eventBus = app(EventBus::class);
            $reflection = new \ReflectionClass($eventBus);
            $total = 0;

            foreach (['listeners', 'listenersAfter', 'listenersBefore'] as $prop) {
                $property = $reflection->getProperty($prop);
                $property->setAccessible(true);

                foreach ($property->getValue($eventBus) as $listeners) {
                    $total += count($listeners);
                }
            }

            return $total;
        };

        // Do a warm-up mount + flush to settle one-time registrations.
        Livewire::test(MemoryLeakComputedStub::class);
        Livewire::flushState();

        $baselineListeners = $countListeners();

        // Simulate 20 request cycles.
        for ($i = 0; $i < 20; $i++) {
            Livewire::test(MemoryLeakComputedStub::class);
            Livewire::flushState();
        }

        $this->assertEquals($baselineListeners, $countListeners());
    }
}

class MemoryLeakComputedStub extends Component
{
    #[Computed]
    public function items(): array
    {
        return ['a', 'b', 'c'];
    }

    #[Computed]
    public function total(): int
    {
        return count($this->items);
    }

    public function render()
    {
        return '<div>{{ $this->total }}</div>';
    }
}
