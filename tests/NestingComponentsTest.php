<?php

namespace Tests;

use Livewire\LivewireComponent;

class NestingComponentsTest extends TestCase
{
    /** @test */
    function parent_renders_child()
    {
        app('livewire')->component('parent', ParentComponentForNestingStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertTrue(str_contains(
            $component->dom,
            'foo'
        ));
    }

    /** @test */
    function parent_renders_stub_element_in_place_of_child_on_subsequent_renders()
    {
        app('livewire')->component('parent', ParentComponentForNestingStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertTrue(str_contains(
            $component->dom,
            'foo'
        ));

        $component->runAction('$refresh');

        $this->assertFalse(str_contains(
            $component->dom,
            'foo'
        ));
    }
}

class ParentComponentForNestingStub extends LivewireComponent {
    public function render()
    {
        return app('view')->make('show-child');
    }
}

class ChildComponentForNestingStub extends LivewireComponent {
    public $name = 'foo';

    public function render()
    {
        return app('view')->make('show-name');
    }
}
