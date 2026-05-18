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

    public function test_values_match_returns_false_when_either_value_cannot_be_json_encoded()
    {
        // Both NAN — would collide via crc32(false) without the guard...
        $this->assertFalse(SupportReactiveProps::valuesMatch(NAN, NAN));

        // Both INF — same risk...
        $this->assertFalse(SupportReactiveProps::valuesMatch(INF, INF));

        // One side encodable, one side not...
        $this->assertFalse(SupportReactiveProps::valuesMatch('hello', NAN));
        $this->assertFalse(SupportReactiveProps::valuesMatch(NAN, 'hello'));
    }

    public function test_values_match_returns_true_for_equal_scalars_and_arrays()
    {
        $this->assertTrue(SupportReactiveProps::valuesMatch('hello', 'hello'));
        $this->assertTrue(SupportReactiveProps::valuesMatch(42, 42));
        $this->assertTrue(SupportReactiveProps::valuesMatch(true, true));
        $this->assertTrue(SupportReactiveProps::valuesMatch(null, null));
        $this->assertTrue(SupportReactiveProps::valuesMatch([1, 2, 3], [1, 2, 3]));
        $this->assertTrue(SupportReactiveProps::valuesMatch(['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2]));
    }

    public function test_values_match_returns_false_for_different_scalars_and_arrays()
    {
        $this->assertFalse(SupportReactiveProps::valuesMatch('hello', 'world'));
        $this->assertFalse(SupportReactiveProps::valuesMatch(42, 43));
        $this->assertFalse(SupportReactiveProps::valuesMatch([1, 2, 3], [1, 2, 4]));
        $this->assertFalse(SupportReactiveProps::valuesMatch('5', 5));
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
