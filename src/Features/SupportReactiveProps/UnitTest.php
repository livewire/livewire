<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_reactive_prop_value_is_available_during_boot_hydrate_and_booted_hooks()
    {
        Livewire::component('child-with-lifecycle-hooks', ChildWithLifecycleHooks::class);

        $child = Livewire::test(ChildWithLifecycleHooks::class, ['count' => 0]);
        $this->assertEquals(0, $child->get('count'));

        // Simulate parent passing count=5 on next request
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 5];
        $child->call('$refresh');

        $this->assertEquals(5, $child->get('count'));
        $this->assertEquals(5, $child->get('bootValue'), 'boot() should see the new reactive prop value');
        $this->assertEquals(5, $child->get('hydrateValue'), 'hydrate() should see the new reactive prop value');
        $this->assertEquals(5, $child->get('bootedValue'), 'booted() should see the new reactive prop value');
    }

    public function test_should_skip_update_returns_true_when_reactive_props_unchanged()
    {
        Livewire::component('child-with-lifecycle-hooks', ChildWithLifecycleHooks::class);

        $child = Livewire::test(ChildWithLifecycleHooks::class, ['count' => 5]);

        // Simulate parent passing the same value
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 5];

        $this->assertTrue(SupportReactiveProps::shouldSkipUpdate($child->snapshot));
    }

    public function test_should_skip_update_returns_false_when_reactive_props_changed()
    {
        Livewire::component('child-with-lifecycle-hooks', ChildWithLifecycleHooks::class);

        $child = Livewire::test(ChildWithLifecycleHooks::class, ['count' => 5]);

        // Simulate parent passing a different value
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 10];

        $this->assertFalse(SupportReactiveProps::shouldSkipUpdate($child->snapshot));
    }

    public function test_should_skip_update_returns_false_without_pending_params()
    {
        Livewire::component('child-with-lifecycle-hooks', ChildWithLifecycleHooks::class);

        $child = Livewire::test(ChildWithLifecycleHooks::class, ['count' => 5]);

        // No pending params (component wasn't bundled with a parent)
        $this->assertFalse(SupportReactiveProps::shouldSkipUpdate($child->snapshot));
    }

    public function test_should_skip_update_returns_false_for_non_reactive_components()
    {
        $child = Livewire::test(new class extends Component {
            public $count = 5;
            public function render() { return '<div>{{ $count }}</div>'; }
        });

        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 5];

        // No reactive props in memo, should not skip
        $this->assertFalse(SupportReactiveProps::shouldSkipUpdate($child->snapshot));
    }

    public function test_skip_update_returns_skip_response_from_update_endpoint()
    {
        Livewire::component('skip-test-parent', SkipTestParent::class);
        Livewire::component('skip-test-child', SkipTestChild::class);

        $parent = Livewire::test(SkipTestParent::class);
        $child = Livewire::test(SkipTestChild::class, ['name' => 'Taylor']);

        $parentSnapshotJson = json_encode($parent->snapshot);
        $childSnapshotJson = json_encode($child->snapshot);

        // Simulate what happens after parent renders: pending params for child
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['name' => 'Taylor'];

        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(\Livewire\Mechanisms\HandleRequests\EndpointResolver::updatePath(), ['components' => [
                // Parent with an action
                ['snapshot' => $parentSnapshotJson, 'updates' => [], 'calls' => [
                    ['method' => 'increment', 'params' => []],
                ]],
                // Child with unchanged props ($commit only)
                ['snapshot' => $childSnapshotJson, 'updates' => [], 'calls' => [
                    ['method' => '$commit', 'params' => []],
                ]],
            ]]);

        $response->assertOk();

        $components = $response->json('components');

        // Parent should have a normal response
        $this->assertArrayHasKey('snapshot', $components[0]);

        // Child should be skipped
        $this->assertTrue($components[1]['skip']);
        $this->assertEquals($child->id(), $components[1]['id']);
    }

    public function test_updating_hook_sees_old_value_and_updated_hook_sees_new_value_for_reactive_props()
    {
        Livewire::component('child-with-update-hooks', ChildWithUpdateHooks::class);

        $child = Livewire::test(ChildWithUpdateHooks::class, ['count' => 0]);

        // Simulate parent passing count=5 on next request
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 5];
        $child->call('$refresh');

        $this->assertEquals(5, $child->get('count'));
        $this->assertEquals(0, $child->get('oldValueDuringUpdating'), 'updatingCount() should see the old value via $this->count');
        $this->assertEquals(5, $child->get('newValueDuringUpdated'), 'updatedCount() should see the new value via $this->count');
    }
}

class ChildWithLifecycleHooks extends Component
{
    #[BaseReactive]
    public $count;

    public $bootValue = 0;
    public $hydrateValue = 0;
    public $bootedValue = 0;

    public function boot()
    {
        $this->bootValue = $this->count;
    }

    public function hydrate()
    {
        $this->hydrateValue = $this->count;
    }

    public function booted()
    {
        $this->bootedValue = $this->count;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}

class SkipTestParent extends Component
{
    public $count = 0;
    public $name = 'Taylor';

    public function increment() { $this->count++; }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}

class SkipTestChild extends Component
{
    #[BaseReactive]
    public $name;

    public function render()
    {
        return '<div>{{ $name }}</div>';
    }
}

class ChildWithUpdateHooks extends Component
{
    #[BaseReactive]
    public $count;

    public $oldValueDuringUpdating = null;
    public $newValueDuringUpdated = null;

    public function updatingCount($value)
    {
        // $this->count should still be the OLD value at this point
        $this->oldValueDuringUpdating = $this->count;
    }

    public function updatedCount($value)
    {
        // $this->count should be the NEW value at this point
        $this->newValueDuringUpdated = $this->count;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}
