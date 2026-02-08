<?php

namespace Livewire\Features\SupportCompiledWireKeys;

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportMorphAwareBladeCompilation\SupportMorphAwareBladeCompilation;
use Livewire\Livewire;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use PHPUnit\Framework\Attributes\DataProvider;

use function Livewire\invade;

class UnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Livewire::flushState();

        config()->set('livewire.smart_wire_keys', true);

        // Reload the features so the config is loaded and the precompilers are registered if required...
        $this->reloadFeatures();
    }

    public function test_keys_are_not_compiled_when_smart_wire_keys_are_disabled()
    {
        Livewire::flushState();

        config()->set('livewire.smart_wire_keys', false);

        // Reload the features so the config is loaded and the precompilers are registered if required...
        $this->reloadFeatures();

        $compiled = $this->compile('<div wire:key="foo">');

        $this->assertStringNotContainsString('<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey', $compiled);
    }

    public function test_child_keys_are_correctly_generated()
    {
        app('livewire')->component('keys-parent', KeysParent::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParent::class);

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(2, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-D'
        ], $childKeys);
    }

    public function test_child_keys_are_correctly_generated_when_the_parent_data_is_prepended()
    {
        app('livewire')->component('keys-parent', KeysParent::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParent::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('prepend');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(3, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-A',
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-D'
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_are_correctly_generated_when_the_parent_data_is_inserted()
    {
        app('livewire')->component('keys-parent', KeysParent::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParent::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('insert');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(3, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-C',
            'lw-XXXXXXXX-0-0-D'
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_are_correctly_generated_when_the_parent_data_is_appended()
    {
        app('livewire')->component('keys-parent', KeysParent::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParent::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('append');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(3, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-D',
            'lw-XXXXXXXX-0-0-E'
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_a_nested_loop_are_correctly_generated()
    {
        app('livewire')->component('keys-parent-with-nested-loops', KeysParentWithNestedLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithNestedLoops::class);

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(4, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B-0-B',
            'lw-XXXXXXXX-0-0-B-0-D',
            'lw-XXXXXXXX-0-0-D-0-B',
            'lw-XXXXXXXX-0-0-D-0-D',
        ], $childKeys);
    }

    public function test_child_keys_in_a_nested_loop_are_correctly_generated_when_the_parent_data_is_prepended()
    {
        app('livewire')->component('keys-parent-with-nested-loops', KeysParentWithNestedLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithNestedLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('prepend');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(9, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-A-0-A',
            'lw-XXXXXXXX-0-0-A-0-B',
            'lw-XXXXXXXX-0-0-A-0-D',
            'lw-XXXXXXXX-0-0-B-0-A',
            'lw-XXXXXXXX-0-0-B-0-B',
            'lw-XXXXXXXX-0-0-B-0-D',
            'lw-XXXXXXXX-0-0-D-0-A',
            'lw-XXXXXXXX-0-0-D-0-B',
            'lw-XXXXXXXX-0-0-D-0-D',
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_a_nested_loop_are_correctly_generated_when_the_parent_data_is_inserted()
    {
        app('livewire')->component('keys-parent-with-nested-loops', KeysParentWithNestedLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithNestedLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('insert');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(9, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B-0-B',
            'lw-XXXXXXXX-0-0-B-0-C',
            'lw-XXXXXXXX-0-0-B-0-D',
            'lw-XXXXXXXX-0-0-C-0-B',
            'lw-XXXXXXXX-0-0-C-0-C',
            'lw-XXXXXXXX-0-0-C-0-D',
            'lw-XXXXXXXX-0-0-D-0-B',
            'lw-XXXXXXXX-0-0-D-0-C',
            'lw-XXXXXXXX-0-0-D-0-D',

        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_a_nested_loop_are_correctly_generated_when_the_parent_data_is_appended()
    {
        app('livewire')->component('keys-parent-with-nested-loops', KeysParentWithNestedLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithNestedLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('append');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(9, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B-0-B',
            'lw-XXXXXXXX-0-0-B-0-D',
            'lw-XXXXXXXX-0-0-B-0-E',
            'lw-XXXXXXXX-0-0-D-0-B',
            'lw-XXXXXXXX-0-0-D-0-D',
            'lw-XXXXXXXX-0-0-D-0-E',
            'lw-XXXXXXXX-0-0-E-0-B',
            'lw-XXXXXXXX-0-0-E-0-D',
            'lw-XXXXXXXX-0-0-E-0-E',
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_a_sibling_loop_are_correctly_generated()
    {
        app('livewire')->component('keys-parent-with-sibling-loops', KeysParentWithSiblingLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithSiblingLoops::class);

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(4, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-D',
            'lw-XXXXXXXX-1-1-B',
            'lw-XXXXXXXX-1-1-D',
        ], $childKeys);
    }

    public function test_child_keys_in_a_sibling_loop_are_correctly_generated_when_the_parent_data_is_prepended()
    {
        app('livewire')->component('keys-parent-with-sibling-loops', KeysParentWithSiblingLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithSiblingLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('prepend');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(6, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-A',
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-D',
            'lw-XXXXXXXX-1-1-A',
            'lw-XXXXXXXX-1-1-B',
            'lw-XXXXXXXX-1-1-D',
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_a_sibling_loop_are_correctly_generated_when_the_parent_data_is_inserted()
    {
        app('livewire')->component('keys-parent-with-sibling-loops', KeysParentWithSiblingLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithSiblingLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('insert');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(6, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-C',
            'lw-XXXXXXXX-0-0-D',
            'lw-XXXXXXXX-1-1-B',
            'lw-XXXXXXXX-1-1-C',
            'lw-XXXXXXXX-1-1-D',
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_a_sibling_loop_are_correctly_generated_when_the_parent_data_is_appended()
    {
        app('livewire')->component('keys-parent-with-sibling-loops', KeysParentWithSiblingLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithSiblingLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('append');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(6, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-D',
            'lw-XXXXXXXX-0-0-E',
            'lw-XXXXXXXX-1-1-B',
            'lw-XXXXXXXX-1-1-D',
            'lw-XXXXXXXX-1-1-E',
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_sibling_and_nested_loops_are_correctly_generated()
    {
        app('livewire')->component('keys-parent-with-sibling-and-nested-loops', KeysParentWithSiblingAndNestedLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithSiblingAndNestedLoops::class);

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(16, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B-0-B',
            'lw-XXXXXXXX-0-0-B-0-D',
            'lw-XXXXXXXX-1-0-B-1-B',
            'lw-XXXXXXXX-1-0-B-1-D',
            'lw-XXXXXXXX-0-0-D-0-B',
            'lw-XXXXXXXX-0-0-D-0-D',
            'lw-XXXXXXXX-1-0-D-1-B',
            'lw-XXXXXXXX-1-0-D-1-D',
            'lw-XXXXXXXX-2-1-B-0-B',
            'lw-XXXXXXXX-2-1-B-0-D',
            'lw-XXXXXXXX-3-1-B-1-B',
            'lw-XXXXXXXX-3-1-B-1-D',
            'lw-XXXXXXXX-2-1-D-0-B',
            'lw-XXXXXXXX-2-1-D-0-D',
            'lw-XXXXXXXX-3-1-D-1-B',
            'lw-XXXXXXXX-3-1-D-1-D',
        ], $childKeys);
    }

    public function test_child_keys_in_sibling_and_nested_loops_are_correctly_generated_when_the_parent_data_is_prepended()
    {
        app('livewire')->component('keys-parent-with-sibling-and-nested-loops', KeysParentWithSiblingAndNestedLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithSiblingAndNestedLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('prepend');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(36, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-A-0-A',
            'lw-XXXXXXXX-0-0-A-0-B',
            'lw-XXXXXXXX-0-0-A-0-D',
            'lw-XXXXXXXX-1-0-A-1-A',
            'lw-XXXXXXXX-1-0-A-1-B',
            'lw-XXXXXXXX-1-0-A-1-D',
            'lw-XXXXXXXX-0-0-B-0-A',
            'lw-XXXXXXXX-0-0-B-0-B',
            'lw-XXXXXXXX-0-0-B-0-D',
            'lw-XXXXXXXX-1-0-B-1-A',
            'lw-XXXXXXXX-1-0-B-1-B',
            'lw-XXXXXXXX-1-0-B-1-D',
            'lw-XXXXXXXX-0-0-D-0-A',
            'lw-XXXXXXXX-0-0-D-0-B',
            'lw-XXXXXXXX-0-0-D-0-D',
            'lw-XXXXXXXX-1-0-D-1-A',
            'lw-XXXXXXXX-1-0-D-1-B',
            'lw-XXXXXXXX-1-0-D-1-D',
            'lw-XXXXXXXX-2-1-A-0-A',
            'lw-XXXXXXXX-2-1-A-0-B',
            'lw-XXXXXXXX-2-1-A-0-D',
            'lw-XXXXXXXX-3-1-A-1-A',
            'lw-XXXXXXXX-3-1-A-1-B',
            'lw-XXXXXXXX-3-1-A-1-D',
            'lw-XXXXXXXX-2-1-B-0-A',
            'lw-XXXXXXXX-2-1-B-0-B',
            'lw-XXXXXXXX-2-1-B-0-D',
            'lw-XXXXXXXX-3-1-B-1-A',
            'lw-XXXXXXXX-3-1-B-1-B',
            'lw-XXXXXXXX-3-1-B-1-D',
            'lw-XXXXXXXX-2-1-D-0-A',
            'lw-XXXXXXXX-2-1-D-0-B',
            'lw-XXXXXXXX-2-1-D-0-D',
            'lw-XXXXXXXX-3-1-D-1-A',
            'lw-XXXXXXXX-3-1-D-1-B',
            'lw-XXXXXXXX-3-1-D-1-D',
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_sibling_and_nested_loops_are_correctly_generated_when_the_parent_data_is_inserted()
    {
        app('livewire')->component('keys-parent-with-sibling-and-nested-loops', KeysParentWithSiblingAndNestedLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithSiblingAndNestedLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('insert');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(36, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B-0-B',
            'lw-XXXXXXXX-0-0-B-0-C',
            'lw-XXXXXXXX-0-0-B-0-D',
            'lw-XXXXXXXX-1-0-B-1-B',
            'lw-XXXXXXXX-1-0-B-1-C',
            'lw-XXXXXXXX-1-0-B-1-D',
            'lw-XXXXXXXX-0-0-C-0-B',
            'lw-XXXXXXXX-0-0-C-0-C',
            'lw-XXXXXXXX-0-0-C-0-D',
            'lw-XXXXXXXX-1-0-C-1-B',
            'lw-XXXXXXXX-1-0-C-1-C',
            'lw-XXXXXXXX-1-0-C-1-D',
            'lw-XXXXXXXX-0-0-D-0-B',
            'lw-XXXXXXXX-0-0-D-0-C',
            'lw-XXXXXXXX-0-0-D-0-D',
            'lw-XXXXXXXX-1-0-D-1-B',
            'lw-XXXXXXXX-1-0-D-1-C',
            'lw-XXXXXXXX-1-0-D-1-D',
            'lw-XXXXXXXX-2-1-B-0-B',
            'lw-XXXXXXXX-2-1-B-0-C',
            'lw-XXXXXXXX-2-1-B-0-D',
            'lw-XXXXXXXX-3-1-B-1-B',
            'lw-XXXXXXXX-3-1-B-1-C',
            'lw-XXXXXXXX-3-1-B-1-D',
            'lw-XXXXXXXX-2-1-C-0-B',
            'lw-XXXXXXXX-2-1-C-0-C',
            'lw-XXXXXXXX-2-1-C-0-D',
            'lw-XXXXXXXX-3-1-C-1-B',
            'lw-XXXXXXXX-3-1-C-1-C',
            'lw-XXXXXXXX-3-1-C-1-D',
            'lw-XXXXXXXX-2-1-D-0-B',
            'lw-XXXXXXXX-2-1-D-0-C',
            'lw-XXXXXXXX-2-1-D-0-D',
            'lw-XXXXXXXX-3-1-D-1-B',
            'lw-XXXXXXXX-3-1-D-1-C',
            'lw-XXXXXXXX-3-1-D-1-D',
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_child_keys_in_sibling_and_nested_loops_are_correctly_generated_when_the_parent_data_is_appended()
    {
        app('livewire')->component('keys-parent-with-sibling-and-nested-loops', KeysParentWithSiblingAndNestedLoops::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithSiblingAndNestedLoops::class);

        $childrenBefore = invade($component)->lastState->getSnapshot()['memo']['children'];

        $component->call('append');

        $childrenAfter = invade($component)->lastState->getSnapshot()['memo']['children'];

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(36, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B-0-B',
            'lw-XXXXXXXX-0-0-B-0-D',
            'lw-XXXXXXXX-0-0-B-0-E',
            'lw-XXXXXXXX-1-0-B-1-B',
            'lw-XXXXXXXX-1-0-B-1-D',
            'lw-XXXXXXXX-1-0-B-1-E',
            'lw-XXXXXXXX-0-0-D-0-B',
            'lw-XXXXXXXX-0-0-D-0-D',
            'lw-XXXXXXXX-0-0-D-0-E',
            'lw-XXXXXXXX-1-0-D-1-B',
            'lw-XXXXXXXX-1-0-D-1-D',
            'lw-XXXXXXXX-1-0-D-1-E',
            'lw-XXXXXXXX-0-0-E-0-B',
            'lw-XXXXXXXX-0-0-E-0-D',
            'lw-XXXXXXXX-0-0-E-0-E',
            'lw-XXXXXXXX-1-0-E-1-B',
            'lw-XXXXXXXX-1-0-E-1-D',
            'lw-XXXXXXXX-1-0-E-1-E',
            'lw-XXXXXXXX-2-1-B-0-B',
            'lw-XXXXXXXX-2-1-B-0-D',
            'lw-XXXXXXXX-2-1-B-0-E',
            'lw-XXXXXXXX-3-1-B-1-B',
            'lw-XXXXXXXX-3-1-B-1-D',
            'lw-XXXXXXXX-3-1-B-1-E',
            'lw-XXXXXXXX-2-1-D-0-B',
            'lw-XXXXXXXX-2-1-D-0-D',
            'lw-XXXXXXXX-2-1-D-0-E',
            'lw-XXXXXXXX-3-1-D-1-B',
            'lw-XXXXXXXX-3-1-D-1-D',
            'lw-XXXXXXXX-3-1-D-1-E',
            'lw-XXXXXXXX-2-1-E-0-B',
            'lw-XXXXXXXX-2-1-E-0-D',
            'lw-XXXXXXXX-2-1-E-0-E',
            'lw-XXXXXXXX-3-1-E-1-B',
            'lw-XXXXXXXX-3-1-E-1-D',
            'lw-XXXXXXXX-3-1-E-1-E',
        ], $childKeys);

        // Ensure that the children from before match the children after including ID and element...
        foreach ($childrenBefore as $key => $childBefore) {
            $this->assertEquals($childBefore, $childrenAfter[$key]);
        }
    }

    public function test_when_using_a_for_else_statement_child_keys_are_correctly_generated()
    {
        app('livewire')->component('keys-parent-with-for-else', KeysParentWithForElse::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithForElse::class);

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(2, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-0-0-B',
            'lw-XXXXXXXX-0-0-D'
        ], $childKeys);
    }

    public function test_when_using_a_for_else_statement_and_empty_is_shown_child_keys_are_correctly_generated()
    {
        app('livewire')->component('keys-parent-with-for-else', KeysParentWithForElse::class);
        app('livewire')->component('keys-child', KeysChild::class);

        $component = Livewire::test(KeysParentWithForElse::class)
            ->call('empty');

        $childKeys = array_keys(invade($component)->lastState->getSnapshot()['memo']['children']);

        $this->assertEquals(1, count($childKeys));

        $this->assertKeysMatchPattern([
            'lw-XXXXXXXX-1', // There should be no loop suffixes here, because the empty block is shown...
        ], $childKeys);
    }

    public function test_loop_stack_defaults_are_correctly_set()
    {
        $this->assertEquals([], SupportCompiledWireKeys::$loopStack);
        $this->assertEquals(
            [
                'count' => null,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_plain_string_key_matching_view_name_is_not_rendered_as_view()
    {
        // "show-name" is an existing test view in tests/views/show-name.blade.php
        // Using it as a wire:key should return the literal string, not render the view
        SupportCompiledWireKeys::processElementKey('show-name', []);

        $this->assertEquals('show-name', SupportCompiledWireKeys::$currentLoop['key']);
    }

    public function test_we_can_open_a_loop()
    {
        SupportCompiledWireKeys::openLoop();

        $this->assertEquals(
            [
                [
                    'count' => 0,
                    'index' => null,
                    'key' => null,
                ],
            ],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => null,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_we_can_close_a_loop()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();

        $this->assertEquals(
            [],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => 0,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_we_can_open_a_second_loop_after_the_first_one_is_closed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::openLoop();

        $this->assertEquals(
            [
                [
                    'count' => 1,
                    'index' => null,
                    'key' => null,
                ],
            ],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => null,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_we_can_close_a_second_loop_after_the_first_one_is_closed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();

        $this->assertEquals(
            [],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => 1,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_we_can_open_an_inner_loop_while_the_first_one_is_open()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();

        $this->assertEquals(
            [
                [
                    'count' => 0,
                    'index' => null,
                    'key' => null,
                ],
                [
                    'count' => 0,
                    'index' => null,
                    'key' => null,
                ],
            ],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => null,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_we_can_close_an_inner_loop_while_the_first_one_is_open()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();

        $this->assertEquals(
            [
                [
                    'count' => 0,
                    'index' => null,
                    'key' => null,
                ],
            ],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => 0,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_an_inner_loop_is_removed_when_the_outer_loop_is_closed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::closeLoop();

        $this->assertEquals(
            [],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => 0,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_a_second_outer_loop_is_added_when_the_first_one_is_closed_and_all_inner_loops_are_removed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::openLoop();
        
        $this->assertEquals(
            [
                [
                    'count' => 1,
                    'index' => null,
                    'key' => null,
                ],
            ],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => null,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    public function test_a_second_inner_loop_is_added_when_the_first_inner_loop_is_closed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::openLoop();
        
        $this->assertEquals(
            [
                [
                    'count' => 0,
                    'index' => null,
                    'key' => null,
                ],
                [
                    'count' => 1,
                    'index' => null,
                    'key' => null,
                ],
            ],
            SupportCompiledWireKeys::$loopStack
        );

        $this->assertEquals(
            [
                'count' => null,
                'index' => null,
                'key' => null,
            ],
            SupportCompiledWireKeys::$currentLoop
        );
    }

    #[DataProvider('elementsTestProvider')]
    public function test_we_can_correctly_find_wire_keys_on_elements_only_but_not_blade_or_livewire_components($occurrences, $template)
    {
        $compiled = $this->compile($template);

        $this->assertOccurrences($occurrences, '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey', $compiled);
    }

    #[DataProvider('bladeComponentsTestProvider')]
    public function test_we_can_correctly_find_wire_keys_on_blade_components_only_but_not_elements_or_livewire_components($occurrences, $template)
    {
        $compiled = $this->compile($template);

        $this->assertOccurrences($occurrences, '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey', $compiled);
    }

    public static function elementsTestProvider()
    {
        return [
            [
                1,
                <<<'HTML'
                <div wire:key="foo">
                </div>
                HTML
            ],
            [
                2,
                <<<'HTML'
                <div wire:key="foo">
                    <div wire:key="bar">
                    </div>
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <div>
                    <livewire:child wire:key="foo" />
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         <livewire:child :wire:key="$child, 5, '_', STR_PAD_BOTH)" />
                     @endforeach
                 </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <div>
                    @livewire('child', [], key('foo'))
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         @livewire('child', [], key($child, 5, '_', STR_PAD_BOTH)))
                     @endforeach
                 </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <x-basic-component wire:key="foo">
                    Some contents
                </x-basic-component>
                HTML
            ],
            [
                0,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         <x-basic-component :wire:key="$child">
                             <livewire:child />
                         </x-basic-component>
                     @endforeach
                 </div>
                HTML
            ],
        ];
    }

    public static function bladeComponentsTestProvider()
    {
        return [
            [
                1,
                <<<'HTML'
                <x-basic-component wire:key="foo">
                    Some contents
                </x-basic-component>
                HTML
            ],
            [
                1,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         <x-basic-component :wire:key="$child">
                             <livewire:child />
                         </x-basic-component>
                     @endforeach
                 </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <div>
                    <livewire:child wire:key="foo" />
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         <livewire:child :wire:key="$child, 5, '_', STR_PAD_BOTH)" />
                     @endforeach
                 </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <div>
                    @livewire('child', [], key('foo'))
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         @livewire('child', [], key($child, 5, '_', STR_PAD_BOTH)))
                     @endforeach
                 </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <div wire:key="foo">
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <div wire:key="foo">
                    <div wire:key="bar">
                    </div>
                </div>
                HTML
            ],
        ];
    }

    protected function reloadFeatures()
    {
        // We need to remove these two precompilers so we can test if the 
        // feature is disabled and whether they get registered again...
        $precompilers = \Livewire\invade(app('blade.compiler'))->precompilers;

        \Livewire\invade(app('blade.compiler'))->precompilers = array_filter($precompilers, function ($precompiler) {
            if (! $precompiler instanceof \Closure) return true;

            $closureClass = (new \ReflectionFunction($precompiler))->getClosureScopeClass()->getName();

            return $closureClass !== SupportCompiledWireKeys::class 
                && $closureClass !== SupportMorphAwareBladeCompilation::class;
        });

        // We need to call these so provide gets called again to load the
        // new config and register the precompilers if required...
        ComponentHookRegistry::register(SupportMorphAwareBladeCompilation::class);
        ComponentHookRegistry::register(SupportCompiledWireKeys::class);
    }

    protected function compile($string)
    {
        $undo = app(ExtendBlade::class)->livewireifyBladeCompiler();

        $html = Blade::compileString($string);

        $undo();

        return $html;
    }

    protected function assertOccurrences($expected, $needle, $haystack)
    {
        $this->assertEquals($expected, count(explode($needle, $haystack)) - 1);
    }

    protected function assertKeysMatchPattern($expected, $keys)
    {
        for ($i = 0; $i < count($expected); $i++) {
            $expectedKey = $expected[$i];
            // The mock key should like like `lw-XXXXXXXX-0-0-A` with the `XXXXXXXX` being the hash of the path which we will replace with a regex check...
            $pattern = str_replace('XXXXXXXX', '(\d{1,10})', $expectedKey);

            $this->assertTrue((bool) preg_match('/'.$pattern.'/', $keys[$i]), 'Key '.$keys[$i].' does not match expected pattern '.$expected[$i]);
        }
    }
}

class KeysParent extends Component
{
    public $items = ['B', 'D'];

    public function prepend() {
        $this->items = ['A','B','D'];
    }

    public function insert() {
        $this->items = ['B','C','D'];
    }

    public function append() {
        $this->items = ['B','D','E'];
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            @foreach ($items as $item)
                <div wire:key="{{ $item }}">
                    <livewire:keys-child :item="$item" />
                </div>
            @endforeach
        </div>
        HTML;
    }
}

class KeysParentWithNestedLoops extends Component
{
    public $items = ['B', 'D'];

    public function prepend() {
        $this->items = ['A','B','D'];
    }

    public function insert() {
        $this->items = ['B','C','D'];
    }

    public function append() {
        $this->items = ['B','D','E'];
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            @foreach ($items as $item)
                <div wire:key="{{ $item }}">
                    @foreach ($items as $item2)
                        <div wire:key="{{ $item2 }}">
                            <livewire:keys-child :item="$item . $item2" />
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        HTML;
    }
}

class KeysParentWithSiblingLoops extends Component
{
    public $items = ['B', 'D'];

    public function prepend() {
        $this->items = ['A','B','D'];
    }

    public function insert() {
        $this->items = ['B','C','D'];
    }

    public function append() {
        $this->items = ['B','D','E'];
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            @foreach ($items as $item)
                <div wire:key="{{ $item }}">
                    <livewire:keys-child :item="$item" />
                </div>
            @endforeach
            @foreach ($items as $item)
                <div wire:key="{{ $item }}">
                    <livewire:keys-child :item="$item" />
                </div>
            @endforeach
        </div>
        HTML;
    }
}

class KeysParentWithSiblingAndNestedLoops extends Component
{
    public $items = ['B', 'D'];

    public function prepend() {
        $this->items = ['A','B','D'];
    }

    public function insert() {
        $this->items = ['B','C','D'];
    }

    public function append() {
        $this->items = ['B','D','E'];
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            @foreach ($items as $item)
                <div wire:key="{{ $item }}">
                    @foreach ($items as $item2)
                        <div wire:key="{{ $item2 }}">
                            <livewire:keys-child :item="$item . $item2" />
                        </div>
                    @endforeach
                    @foreach ($items as $item2)
                        <div wire:key="{{ $item2 }}">
                            <livewire:keys-child :item="$item . $item2" />
                        </div>
                    @endforeach
                </div>
            @endforeach
            @foreach ($items as $item)
                <div wire:key="{{ $item }}">
                    @foreach ($items as $item2)
                        <div wire:key="{{ $item2 }}">
                            <livewire:keys-child :item="$item . $item2" />
                        </div>
                    @endforeach
                    @foreach ($items as $item2)
                        <div wire:key="{{ $item2 }}">
                            <livewire:keys-child :item="$item . $item2" />
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        HTML;
    }
}

class KeysParentWithForElse extends Component
{
    public $items = ['B', 'D'];

    public function prepend() {
        $this->items = ['A','B','D'];
    }

    public function insert() {
        $this->items = ['B','C','D'];
    }

    public function append() {
        $this->items = ['B','D','E'];
    }

    public function empty() {
        $this->items = [];
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            @forelse ($items as $item)
                <div wire:key="{{ $item }}">
                    <livewire:keys-child :item="$item" />
                </div>
            @empty
                <div>
                    <livewire:keys-child item="empty" />
                </div>
            @endforelse
        </div>
        HTML;
    }
}

class KeysChild extends Component
{
    public $item;

    public function render()
    {
        return '<div>Child: {{ $item }}</div>';
    }
}
