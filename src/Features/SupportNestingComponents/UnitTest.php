<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\Component;
use function Livewire\store;

class UnitTest extends \Tests\TestCase
{
    public function test_parent_renders_child()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('Child: foo', $component->html());
    }

    public function test_parent_renders_stub_element_in_place_of_child_on_subsequent_renders()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('Child: foo', $component->html());

        $component->runAction('$refresh');

        $this->assertStringNotContainsString('Child: foo', $component->html());
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

        $this->assertStringContainsString('Child: foo', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('Child: foo', $component->html());
        $this->assertStringContainsString('Child: bar', $component->html());

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('Child: foo', $component->html());
        $this->assertStringNotContainsString('Child: bar', $component->html());
    }

    public function test_parent_tracks_subsequent_renders_of_children_inside_a_loop_with_colon_wire_key_syntax()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenWithWireKeyStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('Child: foo', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('Child: foo', $component->html());
        $this->assertStringContainsString('Child: bar', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('Child: foo', $component->html());
        $this->assertStringNotContainsString('Child: bar', $component->html());
    }

    public function test_parent_tracks_subsequent_renders_of_children_inside_a_loop_with_colon_wire_key_having_comma()
    {
        app('livewire')->component('parent', ParentComponentForNestingChildrenWithWireKeyHavingCommaStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);
        $component = app('livewire')->test('parent');

        $this->assertStringContainsString('Child: foo', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('Child: foo', $component->html());
        $this->assertStringContainsString('Child: bar', $component->html() );

        $component->runAction('setChildren', ['foo', 'bar']);
        $this->assertStringNotContainsString('Child: foo', $component->html());
        $this->assertStringNotContainsString('Child: bar', $component->html());
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

    public function test_child_tag_name_with_xss_payload_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Livewire child tag name');

        // Create a mock parent component
        $parent = new ChildComponentForNestingStub();

        // Set malicious children data directly in the store
        store($parent)->set('previousChildren', [
            'child-key' => ['div onclick=alert(1)', 'valid-id']
        ]);

        // This should throw an exception due to invalid tag name
        SupportNestingComponents::getPreviouslyRenderedChild($parent, 'child-key');
    }

    public function test_child_tag_name_with_spaces_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Livewire child tag name');

        $parent = new ChildComponentForNestingStub();

        store($parent)->set('previousChildren', [
            'child-key' => ['div class=injected', 'valid-id']
        ]);

        SupportNestingComponents::getPreviouslyRenderedChild($parent, 'child-key');
    }

    public function test_child_tag_name_starting_with_number_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Livewire child tag name');

        $parent = new ChildComponentForNestingStub();

        store($parent)->set('previousChildren', [
            'child-key' => ['1div', 'valid-id']
        ]);

        SupportNestingComponents::getPreviouslyRenderedChild($parent, 'child-key');
    }

    public function test_child_id_with_invalid_characters_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Livewire child component ID format');

        $parent = new ChildComponentForNestingStub();

        store($parent)->set('previousChildren', [
            'child-key' => ['div', 'id<script>alert(1)</script>']
        ]);

        SupportNestingComponents::getPreviouslyRenderedChild($parent, 'child-key');
    }

    public function test_valid_custom_element_tag_names_are_allowed()
    {
        $parent = new ChildComponentForNestingStub();

        store($parent)->set('previousChildren', [
            'child-key' => ['my-custom-element', 'valid-id-123']
        ]);

        // Should not throw an exception
        $result = SupportNestingComponents::getPreviouslyRenderedChild($parent, 'child-key');

        $this->assertEquals(['my-custom-element', 'valid-id-123'], $result);
    }

    public function test_valid_standard_html_tags_are_allowed()
    {
        $parent = new ChildComponentForNestingStub();

        $validTags = ['div', 'span', 'section', 'article', 'h1', 'p', 'ul', 'li'];

        foreach ($validTags as $tag) {
            store($parent)->set('previousChildren', [
                'child-key' => [$tag, 'valid-id']
            ]);

            $result = SupportNestingComponents::getPreviouslyRenderedChild($parent, 'child-key');
            $this->assertEquals([$tag, 'valid-id'], $result);
        }
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
        return '<span>Child: {{ $this->name }}</span>';
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
