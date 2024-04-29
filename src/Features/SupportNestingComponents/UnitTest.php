<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    public function test_parent_renders_child()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->html());
    }

    public function test_parent_renders_stub_element_in_place_of_child_on_subsequent_renders()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->html());

        $component->runAction('$refresh');

        $this->assertStringNotContainsString('foo', $component->html());
    }

    public function test_stub_element_root_element_matches_original_child_component_root_element()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('span', $component->html());

        $component->runAction('$refresh');

        $this->assertStringContainsString('span', $component->html());
    }

    public function test_parent_tracks_subsequent_renders_of_children_inside_a_loop()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->html());
        $this->assertStringContainsString('bar', $component->html());

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->html());
        $this->assertStringNotContainsString('bar', $component->html());
    }

    public function test_parent_tracks_subsequent_renders_of_children_inside_a_loop_with_colon_wire_key_syntax()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenWithWireKeyStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->html());
        $this->assertStringContainsString('bar', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->html());
        $this->assertStringNotContainsString('bar', $component->html());
    }

    public function test_parent_tracks_subsequent_renders_of_children_inside_a_loop_with_colon_wire_key_having_comma()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenWithWireKeyHavingCommaStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->html());
        $this->assertStringContainsString('bar', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->html());
        $this->assertStringNotContainsString('bar', $component->html());
    }

    public function test_parent_keeps_rendered_children_even_when_skipped_rendering()
    {
        app('livewire')->component('parent', ParentComponentForSkipRenderStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $children = $component->snapshot['memo']['children'];

        $component->runAction('skip');

        $this->assertContains($children, $component->snapshot['memo']);
    }
}

class ParentComponentForNestingChildStub extends Component
{
    public function render()
    {
        return app('view')->make('show-child', [
            'child' => ['name' => 'foo'],
        ]);
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
        return app('view')->make('show-children', [
            'children' => $this->children,
        ]);
    }
}

class ParentComponentForNestingChildrenWithWireKeyStub extends Component
{
    public $children = ['foo'];

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function render()
    {
        return <<<'blade'
            <div>
                @foreach ($children as $child)
                    <livewire:child :name="$child" :wire:key="$child" />
                @endforeach
            </div>
blade;
    }
}

class ParentComponentForNestingChildrenWithWireKeyHavingCommaStub extends Component
{
    public $children = ['foo'];

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function render()
    {
        return <<<'blade'
            <div>
                @foreach ($children as $child)
                    <livewire:child :name="$child" :wire:key="str_pad($child, 5, '_', STR_PAD_BOTH)" />
                @endforeach
            </div>
blade;
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
        return app('view')->make('show-name-with-this');
    }
}

class ParentComponentForSkipRenderStub extends Component
{
    public function skip()
    {
        $this->skipRender();
    }

    public function render()
    {
        return app('view')->make('show-child', [
            'child' => ['name' => 'foo'],
        ]);
    }
}
