<?php

namespace Tests;

use Livewire\Component;

class NestingComponentsTest extends TestCase
{
    /** @test */
    public function parent_renders_child()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertTrue(str_contains(
            $component->dom,
            'foo'
        ));
    }

    /** @test */
    public function parent_renders_stub_element_in_place_of_child_on_subsequent_renders()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
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

    /** @test */
    public function stub_element_root_element_matches_original_child_component_root_element()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertTrue(str_contains(
            $component->dom,
            'span'
        ));

        $component->runAction('$refresh');

        $this->assertTrue(str_contains(
            $component->dom,
            'span'
        ));
    }

    /** @test */
    public function parent_tracks_subsequent_renders_of_children_inside_a_loop()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertTrue(str_contains($component->dom, 'foo'));

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertFalse(str_contains($component->dom, 'foo'));
        $this->assertTrue(str_contains($component->dom, 'bar'));

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertFalse(str_contains($component->dom, 'foo'));
        $this->assertFalse(str_contains($component->dom, 'bar'));
    }
}

class ParentComponentForNestingChildStub extends Component
{
    public $child = 'foo';

    public function render()
    {
        return app('view')->make('show-child');
    }
}

class ParentComponentForNestingChildrenStub extends Component
{
    public $children = ['foo'];

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function render()
    {
        return app('view')->make('show-children');
    }
}

class ChildComponentForNestingStub extends Component
{
    public $name;

    public function mount($name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return app('view')->make('show-name');
    }
}
