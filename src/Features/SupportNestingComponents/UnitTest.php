<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\Features\SupportLazyLoading\SupportLazyLoading;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $this->expectException(CorruptComponentPayloadException::class);

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
        $this->expectException(CorruptComponentPayloadException::class);

        $parent = new ChildComponentForNestingStub();

        store($parent)->set('previousChildren', [
            'child-key' => ['div class=injected', 'valid-id']
        ]);

        SupportNestingComponents::getPreviouslyRenderedChild($parent, 'child-key');
    }

    public function test_child_tag_name_starting_with_number_throws_exception()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        $parent = new ChildComponentForNestingStub();

        store($parent)->set('previousChildren', [
            'child-key' => ['1div', 'valid-id']
        ]);

        SupportNestingComponents::getPreviouslyRenderedChild($parent, 'child-key');
    }

    public function test_child_id_with_invalid_characters_throws_exception()
    {
        $this->expectException(CorruptComponentPayloadException::class);

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

    #[DataProvider('malformedChildrenMemos')]
    public function test_malformed_children_memo_returns_419($children)
    {
        // Disable debug mode to test production HTTP responses (404/419)...
        config()->set('app.debug', false);

        app('livewire')->component('parent', ParentComponentForNestingChildrenWithWireKeyStub::class);
        app('livewire')->component('child', ChildComponentForNestingStub::class);

        $snapshot = app('livewire')->test('parent')->snapshot;

        // "children" isn't covered by the checksum, so the snapshot still verifies...
        if ($children === null) {
            unset($snapshot['memo']['children']);
        } else {
            $snapshot['memo']['children'] = $children;
        }

        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), ['components' => [
                ['snapshot' => json_encode($snapshot), 'updates' => [], 'calls' => []],
            ]])
            ->assertStatus(419);
    }

    public static function malformedChildrenMemos()
    {
        return [
            'missing children' => [null],
            'non-array children' => ['foo'],
            'non-array child' => [['foo' => 'div']],
            'child without id' => [['foo' => ['div']]],
            'associative child' => [['foo' => ['tag' => 'div', 'id' => 'abc']]],
            'child with invalid tag' => [['foo' => ['<script>', 'abc']]],
            'child with invalid id' => [['foo' => ['div', 'id<script>']]],
        ];
    }

    public function test_child_tag_is_only_validated_when_the_child_is_rendered_again()
    {
        app('livewire')->component('parent', ParentComponentForSkipRenderWithCommentedChildStub::class);
        app('livewire')->component('child', ChildComponentWithLeadingCommentForNestingStub::class);

        $component = app('livewire')->test('parent');

        // The leading comment means the child's tag is tracked as an empty string...
        $this->assertSame('', array_values($component->snapshot['memo']['children'])[0][0]);

        $component->call('skip')->assertOk();
    }

    public function test_lazy_component_still_loads()
    {
        // The lazy mount params container snapshot has no "children" memo...
        SupportLazyLoading::$disableWhileTesting = false;

        app('livewire')->component('lazy-child', LazyChildComponentForNestingStub::class);

        $component = app('livewire')->test('lazy-child', ['name' => 'foo']);

        preg_match("/__lazyLoad\('([^']+)'\)/", html_entity_decode($component->html()), $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        $component
            ->call('__lazyLoad', $matches[1])
            ->assertSee('Child: foo');
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

class ParentComponentForSkipRenderWithCommentedChildStub extends ParentComponentForSkipRenderStub
{
    public function render()
    {
        return '<div><livewire:child /></div>';
    }
}

class ChildComponentWithLeadingCommentForNestingStub extends Component
{
    public function render()
    {
        return "<!-- child -->\n<div>Child</div>";
    }
}

#[Lazy]
class LazyChildComponentForNestingStub extends ChildComponentForNestingStub
{
    public function placeholder()
    {
        return '<span>Loading...</span>';
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
