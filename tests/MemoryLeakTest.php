<?php

namespace Livewire\Tests;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use Livewire\Features\SupportStreaming\SupportStreaming;
use Livewire\Features\SupportRedirects\SupportRedirects;
use Livewire\Drawer\BaseUtils;

/**
 * Tests to verify that memory leaks are prevented by proper state flushing.
 *
 * These tests simulate what happens in Laravel Octane where the application
 * stays in memory between requests.
 */
class MemoryLeakTest extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    /** @test */
    public function component_stacks_are_cleared_on_flush_state()
    {
        // Simulate component stacks having leftover data (as would happen with an exception)
        HandleComponents::$componentStack = [new \stdClass()];
        HandleComponents::$renderStack = [new \stdClass()];

        $this->assertNotEmpty(HandleComponents::$componentStack);
        $this->assertNotEmpty(HandleComponents::$renderStack);

        // Trigger flush-state
        Livewire::flushState();

        // Verify stacks are cleared
        $this->assertEmpty(HandleComponents::$componentStack);
        $this->assertEmpty(HandleComponents::$renderStack);
    }

    /** @test */
    public function streaming_response_is_cleared_on_flush_state()
    {
        // Use reflection to access protected static property
        $reflection = new \ReflectionClass(SupportStreaming::class);
        $property = $reflection->getProperty('response');
        $property->setAccessible(true);

        // Simulate a response being set
        $property->setValue(null, new \stdClass());

        $this->assertNotNull($property->getValue(null));

        // Trigger flush-state
        Livewire::flushState();

        // Verify response is cleared
        $this->assertNull($property->getValue(null));
    }

    /** @test */
    public function lifecycle_hooks_caches_are_cleared_on_flush_state()
    {
        // Use reflection to access protected static properties
        $reflection = new \ReflectionClass(SupportLifecycleHooks::class);

        $traitCache = $reflection->getProperty('traitCache');
        $traitCache->setAccessible(true);

        $methodCache = $reflection->getProperty('methodCache');
        $methodCache->setAccessible(true);

        // Simulate caches having data
        $traitCache->setValue(null, ['SomeClass' => ['trait1', 'trait2']]);
        $methodCache->setValue(null, ['SomeClass::someMethod' => true]);

        $this->assertNotEmpty($traitCache->getValue(null));
        $this->assertNotEmpty($methodCache->getValue(null));

        // Trigger flush-state
        Livewire::flushState();

        // Verify caches are cleared
        $this->assertEmpty($traitCache->getValue(null));
        $this->assertEmpty($methodCache->getValue(null));
    }

    /** @test */
    public function redirector_cache_stack_is_cleared_on_flush_state()
    {
        // Simulate redirector cache stack having leftover data
        SupportRedirects::$redirectorCacheStack = [new \stdClass()];

        $this->assertNotEmpty(SupportRedirects::$redirectorCacheStack);

        // Trigger flush-state
        Livewire::flushState();

        // Verify stack is cleared
        $this->assertEmpty(SupportRedirects::$redirectorCacheStack);
    }

    /** @test */
    public function base_utils_reflection_cache_is_cleared_on_flush_state()
    {
        // Use reflection to access protected static property
        $reflection = new \ReflectionClass(BaseUtils::class);
        $property = $reflection->getProperty('reflectionCache');
        $property->setAccessible(true);

        // Simulate cache having data
        $property->setValue(null, ['SomeClass' => ['prop1' => [], 'prop2' => []]]);

        $this->assertNotEmpty($property->getValue(null));

        // Trigger flush-state
        Livewire::flushState();

        // Verify cache is cleared
        $this->assertEmpty($property->getValue(null));
    }

    /** @test */
    public function mount_method_properly_cleans_up_on_exception()
    {
        // Clear the stack first
        HandleComponents::$componentStack = [];

        // Create a component that throws during mount
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
            // Expected exception
        }

        // Flush state (simulating end of request)
        Livewire::flushState();

        // Verify component stack is empty after exception + flush
        $this->assertEmpty(HandleComponents::$componentStack);
    }

    /** @test */
    public function repeated_requests_dont_accumulate_static_state()
    {
        // Simulate multiple requests
        for ($i = 0; $i < 5; $i++) {
            // Each "request" would normally accumulate state
            HandleComponents::$componentStack[] = new \stdClass();
            SupportRedirects::$redirectorCacheStack[] = new \stdClass();

            // End of request should flush
            Livewire::flushState();

            // Verify state is cleared after each "request"
            $this->assertEmpty(HandleComponents::$componentStack, "Component stack not empty after request {$i}");
            $this->assertEmpty(SupportRedirects::$redirectorCacheStack, "Redirector stack not empty after request {$i}");
        }
    }
}
