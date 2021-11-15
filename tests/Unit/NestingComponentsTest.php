<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class NestingComponentsTest extends TestCase
{
    /** @test */
    public function parent_renders_child()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->payload['effects']['html']);
    }

    /** @test */
    public function parent_renders_stub_element_in_place_of_child_on_subsequent_renders()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->payload['effects']['html']);

        $component->runAction('$refresh');

        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);
    }

    /** @test */
    public function parent_renders_stub_element_in_place_of_child_on_subsequent_renders_even_if_view_cache_cleared()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->payload['effects']['html']);

        Artisan::call('view:clear');
        $component->runAction('$refresh');

        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);
    }

    /** @test */
    public function stub_element_root_element_matches_original_child_component_root_element()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('span', $component->payload['effects']['html']);

        $component->runAction('$refresh');

        $this->assertStringContainsString('span', $component->payload['effects']['html']);
    }

    /** @test */
    public function parent_tracks_subsequent_renders_of_children_inside_a_loop()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->payload['effects']['html'] );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);
        $this->assertStringContainsString('bar', $component->payload['effects']['html'] );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);
        $this->assertStringNotContainsString('bar', $component->payload['effects']['html']);
    }

    /** @test */
    public function parent_tracks_subsequent_renders_of_children_inside_a_loop_with_colon_wire_key_syntax()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenWithWireKeyStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->payload['effects']['html'] );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);
        $this->assertStringContainsString('bar', $component->payload['effects']['html'] );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);
        $this->assertStringNotContainsString('bar', $component->payload['effects']['html']);
    }

    /** @test */
    public function parent_tracks_subsequent_renders_of_children_inside_a_loop_with_colon_wire_key_having_comma()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenWithWireKeyHavingCommaStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('foo', $component->payload['effects']['html'] );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);
        $this->assertStringContainsString('bar', $component->payload['effects']['html'] );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);
        $this->assertStringNotContainsString('bar', $component->payload['effects']['html']);
    }

    /** @test */
    public function parent_tracks_subsequent_renders_of_duplicate_children()
    {
        app('livewire')->component('parent', ParentComponentForNestingDuplicateChildrenStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        // $name appears twice per component
        $this->assertEquals(2 * 2, substr_count($component->payload['effects']['html'], 'foo'));

        $component->runAction('$refresh');

        $this->assertStringNotContainsString('foo', $component->payload['effects']['html']);

        preg_match_all('/<span wire:id="(\w+)"/', $component->payload['effects']['html'], $matches);
        $childComponentIds = $matches[1];
        $this->assertEquals(array_unique($childComponentIds), $childComponentIds);
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

class ParentComponentForNestingDuplicateChildrenStub extends Component
{
    public function render()
    {
        return app('view')->make('show-duplicate-children', [
            'childName' => 'foo',
        ]);
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
