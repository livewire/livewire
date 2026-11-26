<?php

namespace Livewire\Features\SupportIslands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IslandPartsUnitTest extends TestCase
{
    public function test_normal_renders_keep_parts_inline_and_register_only_one_template_for_many_items()
    {
        $component = Livewire::test(IslandPartsTestComponent::class, ['count' => 1000]);
        $island = $component->instance()->getIslands()[0];
        $path = IslandCompiler::getCachedPathFromToken($island['token']);
        $source = File::get($path);

        $this->assertCount(1, $island['parts']);
        $this->assertSame(0, IslandPartsTestComponent::$partRenders);
        $this->assertSame(1000, substr_count($component->html(), 'data-island-part='));
        $this->assertStringContainsString('@foreach', $source);
        $this->assertStringNotContainsString('renderIslandPart', $source);
        $this->assertStringNotContainsString('__LIVEWIRE_PART_', $source);
        $this->assertStringNotContainsString('FRAGMENT', File::get(IslandCompiler::getCachedPathFromToken($island['parts']['row'])));

        $component->call('refreshIsland')->assertHasNoErrors();
        $component->call('refreshIsland', 'append')->assertHasNoErrors();
        $component->call('refreshIsland', 'prepend')->assertHasNoErrors();
        $this->assertSame(0, IslandPartsTestComponent::$partRenders);
    }

    public function test_explicit_operations_render_only_the_cached_part_and_do_not_reset_list_state()
    {
        $component = Livewire::test(IslandPartsTestComponent::class);
        $component->call('change');

        $this->assertArrayNotHasKey('html', $component->effects);
        $this->assertArrayNotHasKey('islandFragments', $component->effects);
        $this->assertSame(1, IslandPartsTestComponent::$partRenders);
        $this->assertSame(1, IslandPartsTestComponent::$rootRenders);
        $this->assertSame(1, IslandPartsTestComponent::$listEvaluations);
        $this->assertSame(2, $component->get('cursor'));
        $part = $component->effects['islandParts'][0];
        $this->assertSame('users', $part['island']);
        $this->assertSame('row', $part['name']);
        $this->assertSame('row-1', $part['target']);
        $this->assertSame('morph', $part['mode']);
        $this->assertSame($component->instance()->getIslands()[0]['token'], $part['token']);
        $this->assertStringContainsString('Updated', $part['html']);
        $this->assertStringNotContainsString('Row 2', $part['html']);
    }

    public function test_insertion_and_removal_have_separate_effects_and_removal_does_not_render_a_view()
    {
        $component = Livewire::test(IslandPartsTestComponent::class);
        foreach (['prepend', 'append'] as $mode) {
            $component->call('change', $mode, 'row-3');
            $this->assertSame($mode, $component->effects['islandParts'][0]['mode']);
            $this->assertStringContainsString('data-island-part="row-3"', $component->effects['islandParts'][0]['html']);
        }
        $component->call('remove');
        $this->assertSame('remove', $component->effects['islandParts'][0]['mode']);
        $this->assertSame('', $component->effects['islandParts'][0]['html']);
        $this->assertSame(2, IslandPartsTestComponent::$partRenders);
    }

    public function test_an_empty_list_still_registers_a_template_for_later_insertion()
    {
        $component = Livewire::test(IslandPartsTestComponent::class, ['count' => 0]);
        $this->assertCount(1, $component->instance()->getIslands()[0]['parts']);
        $component->assertDontSee('data-island-part=', false)->call('change', 'append');
        $this->assertStringContainsString('data-island-part="row-1"', $component->effects['islandParts'][0]['html']);
        $this->assertSame(1, IslandPartsTestComponent::$partRenders);
    }

    public function test_part_api_does_not_automatically_skip_a_root_render()
    {
        $component = Livewire::test(IslandPartsTestComponent::class)->call('change', 'morph', 'row-1', false);
        $this->assertArrayHasKey('html', $component->effects);
        $this->assertArrayHasKey('islandParts', $component->effects);
    }

    public function test_always_and_implicit_island_renders_do_not_invoke_the_part_renderer()
    {
        $component = Livewire::test(IslandPartsTestComponent::class, ['always' => true]);
        $component->call('$refresh');
        $component->update(calls: [[
            'method' => '$refresh',
            'params' => [],
            'metadata' => ['island' => ['name' => 'users', 'mode' => 'morph']],
        ]])->assertHasNoErrors();
        $this->assertSame(0, IslandPartsTestComponent::$partRenders);
        $this->assertSame(3, IslandPartsTestComponent::$listEvaluations);
    }

    public function test_part_render_failure_restores_shared_livewire_context()
    {
        $component = $this->template('<div>@island(name: "users") @islandPart("row") @php if ($fail ?? false) throw new \\RuntimeException("Part failed"); @endphp <p>Row</p> @endislandPart @endisland</div>')->instance();
        $before = app('view')->shared('__livewire');
        try {
            $component->renderIslandPart('users', 'row', 'row', with: ['fail' => true]);
            $this->fail('Expected the part render to fail.');
        } catch (\Illuminate\View\ViewException $e) {
            $this->assertStringContainsString('Part failed', $e->getMessage());
        }
        $this->assertSame($before, app('view')->shared('__livewire'));
        $this->assertSame([], $component->getRenderedIslandParts());
    }

    public function test_ordinary_islands_retain_their_memo_shape()
    {
        $component = $this->template('<div>@island(name: "ordinary") <p>Hello</p> @endisland</div>');
        $this->assertSame(['name', 'token'], array_keys($component->instance()->getIslands()[0]));
        $this->assertArrayNotHasKey('islandParts', $component->effects);
    }

    #[DataProvider('attributeTemplates')]
    public function test_attributes_are_prepared_in_both_cached_copies($body, $expected)
    {
        $component = $this->template('<div>@island(name: "users")' . $body . '@endisland</div>');
        $island = $component->instance()->getIslands()[0];
        foreach ([$island['token'], $island['parts']['row']] as $token) {
            $source = File::get(IslandCompiler::getCachedPathFromToken($token));
            $this->assertStringContainsString($expected, $source);
            $this->assertStringNotContainsString('@islandPart', $source);
        }
    }

    public static function attributeTemplates()
    {
        return [
            'static fallback' => ['@islandPart("row") <p>Static</p> @endislandPart', 'data-island-part="row"'],
            'key' => ['@islandPart(name: "row") <p wire:key="row-{{ $id }}">Item</p> @endislandPart', 'data-island-part="row-{{ $id }}"'],
            'manual target wins' => ['@islandPart("row") <p data-island-part="manual" wire:key="other">Item</p> @endislandPart', 'data-island-part="manual"'],
            'quoted expression' => ['@islandPart("row") <p title="{{ \'a > b\' }}" wire:key="{{ \'row-\' . $id }}">Item</p> @endislandPart', 'data-island-part="{{ \'row-\' . $id }}"'],
            'leading PHP' => ['@islandPart("row") <?php $id = 2; ?> <!-- comment --> <p wire:key="{{ $id }}">Item</p> @endislandPart', 'data-island-part="{{ $id }}"'],
            'PHP close in a string' => ['@islandPart("row") <?php $id = "?>"; ?> <p wire:key="{{ $id }}">Item</p> @endislandPart', 'data-island-part="{{ $id }}"'],
            'inline PHP setup' => ['@islandPart("row") @php($id = 2) <p wire:key="{{ $id }}">Item</p> @endislandPart', 'data-island-part="{{ $id }}"'],
            'self closing' => ['@islandPart("row") <input wire:key="row-{{ $id }}" /> @endislandPart', 'data-island-part="row-{{ $id }}"'],
            'attribute directives' => ['@islandPart("row") <p @class([\'active\' => true]) wire:key="row-{{ $id }}">Item</p> @endislandPart', 'data-island-part="row-{{ $id }}"'],
            'conditional explicit roots' => ['@islandPart("row") @if(true) <p data-island-part="one"></p> @else <p data-island-part="two"></p> @endif @endislandPart', 'data-island-part="one"'],
        ];
    }

    public function test_bound_component_keys_are_copied_at_compilation_time()
    {
        $compiled = IslandCompiler::compile('bound-part', '<div>@island(name: "users") @foreach($items as $item) @islandPart("row") <x-item :wire:key="\'row-\'.$item->id" /> @endislandPart @endforeach @endisland</div>');
        $this->assertStringContainsString('parts:', $compiled);
        $path = IslandCompiler::getCachedPathFromToken(app('livewire.compiler')->cacheManager->getHash('bound-part') . '-1-part-0');
        $this->assertStringContainsString(':data-island-part="\'row-\'.$item->id"', File::get($path));
    }

    public function test_extraction_preserves_complex_attribute_expressions_verbatim()
    {
        // Inspect prepared Blade, independently of Blade's own echo parser.
        $body = '@islandPart("row") <p title="{{ \'}}\' }}" wire:key="row-{{ $id }}">Item</p> @endislandPart';
        IslandCompiler::compile('complex-attribute', '@island ' . $body . ' @endisland');
        $hash = app('livewire.compiler')->cacheManager->getHash('complex-attribute');
        foreach ([$hash . '-1', $hash . '-1-part-0'] as $token) {
            $source = File::get(IslandCompiler::getCachedPathFromToken($token));
            $this->assertStringContainsString('title="{{ \'}}\' }}"', $source);
            $this->assertStringContainsString('data-island-part="row-{{ $id }}"', $source);
        }
    }

    public function test_blade_component_roots_forward_bound_keys_and_targets_in_both_render_paths()
    {
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(__DIR__ . '/fixtures', 'parts');
        $component = $this->template(<<<'HTML'
        <ul>@island(name: 'users')
            @foreach([1, 2] as $row)
                @islandPart('row')
                    <x-parts::part-root :wire:key="'row-'.$row">{{ $row }}</x-parts::part-root>
                @endislandPart
            @endforeach
        @endisland</ul>
        HTML);
        $component->assertSee('data-island-part="row-1"', false)->assertSee('data-island-part="row-2"', false);
        $component->instance()->renderIslandPart('users', 'row', 'row-3', with: ['row' => 3], mode: 'append');
        $this->assertStringContainsString('data-island-part="row-3"', $component->instance()->getRenderedIslandParts()[0]['html']);
    }

    #[DataProvider('invalidTemplates')]
    public function test_invalid_declarations_fail_at_compilation($body, $reason)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($reason);
        IslandCompiler::compile('invalid-part', $body);
    }

    public static function invalidTemplates()
    {
        return [
            ['@islandPart("row") <p></p> @endislandPart', 'inside an @island'],
            ['@island @islandPart($name) <p></p> @endislandPart @endisland', 'literal template name'],
            ['@island @islandPart("row", with: []) <p></p> @endislandPart @endisland', 'literal template name'],
            ['@island @islandPart("") <p></p> @endislandPart @endisland', 'literal template name'],
            ['@island @islandPart("row") @islandPart("nested") <p></p> @endislandPart @endislandPart @endisland', 'Nested'],
            ['@island @islandPart("row") <p></p> @endislandPart @islandPart("row") <p></p> @endislandPart @endisland', 'Duplicate'],
            ['@island @islandPart("row") <p></p> @endisland', 'Missing @endislandPart'],
            ['@island @foreach($items as $item) @islandPart("row") <p></p> @endislandPart @endforeach @endisland', 'stable wire:key'],
            ['@island @forelse($items as $item) @islandPart("row") <p></p> @endislandPart @empty @endforelse @endisland', 'stable wire:key'],
            ['@island @for($i=0;$i<2;$i++) @islandPart("row") <p></p> @endislandPart @endfor @endisland', 'stable wire:key'],
            ['@island @while(true) @islandPart("row") <p></p> @endislandPart @endwhile @endisland', 'stable wire:key'],
            ['@island @foreach($items as $item) @forelse([] as $other) @empty @islandPart("row") <p></p> @endislandPart @endforelse @endforeach @endisland', 'stable wire:key'],
            ['@island @islandPart("row") @if(true) <p></p> @endif @endislandPart @endisland', 'literal root'],
        ];
    }

    public function test_empty_forelse_branches_and_ignored_loops_do_not_require_keys()
    {
        $component = $this->template(<<<'HTML'
        <div>@island(name: 'users')
            {{-- @foreach($items as $item) --}}
            <?php $ignored = '@foreach($items as $item)'; ?>
            @php $alsoIgnored = '@while(true)'; @endphp
            @forelse([] as $item)
            @empty
                @islandPart('row') <p>Empty</p> @endislandPart
            @endforelse
        @endisland</div>
        HTML);
        $component->assertSee('data-island-part="row"', false);
    }

    public function test_names_are_local_to_their_immediate_island()
    {
        $component = $this->template(<<<'HTML'
        <div>@island(name: 'outer')
            @islandPart('row') <p>Outer</p> @endislandPart
            @island(name: 'inner')
                @islandPart('row') <p>Inner</p> @endislandPart
            @endisland
        @endisland</div>
        HTML);
        $islands = $component->instance()->getIslands();
        $this->assertCount(2, $islands);
        $this->assertNotSame($islands[0]['parts']['row'], $islands[1]['parts']['row']);
    }

    public function test_warm_renders_reuse_compilation_and_missing_templates_and_view_clear_recover()
    {
        $compiled = [];
        app('blade.compiler')->prepareStringsForCompilationUsing(function ($content) use (&$compiled) {
            $compiled[] = app('blade.compiler')->getPath();
            return $content;
        });
        $component = Livewire::test(IslandPartsTestComponent::class);
        $component->call('change');
        $count = count($compiled);
        $component->call('change');
        $component->call('refreshIsland');
        $this->assertCount($count, $compiled);

        $island = $component->instance()->getIslands()[0];
        File::delete(IslandCompiler::getCachedPathFromToken($island['parts']['row']));
        $component->call('change')->assertHasNoErrors();
        $this->assertGreaterThan($count, count($compiled));
        $count = count($compiled);
        Artisan::call('view:clear');
        $component->call('change')->assertHasNoErrors();
        $this->assertGreaterThan($count, count($compiled));
    }

    public function test_helpers_are_not_client_callable_actions()
    {
        $this->expectException(\Livewire\Exceptions\MethodNotFoundException::class);
        Livewire::test(IslandPartsTestComponent::class)->call('renderIslandPart', 'users', 'row', 'row-1');
    }

    public function test_part_keys_use_normal_blade_escaping()
    {
        $component = $this->template('<div>@island(name: "users") @islandPart("row") <p wire:key="row-{{ $id }}">Item</p> @endislandPart @endisland</div>');
        $component->assertSee('data-island-part="row-a&quot;&lt;&amp;"', false);
    }

    public function test_parts_inherit_view_imports_and_recover_in_a_view_cached_sfc()
    {
        app('livewire.finder')->addLocation(viewPath: __DIR__ . '/fixtures');
        Artisan::call('view:cache');
        $component = Livewire::test('sfc-island-parts')->assertSee('2026');
        $component->call('change')->assertHasNoErrors();
        $this->assertStringContainsString('2030', $component->effects['islandParts'][0]['html']);
        $this->assertArrayNotHasKey('islandFragments', $component->effects);
    }

    public function test_linked_islands_render_their_own_part_templates()
    {
        $component = $this->template('<div>@island(name: "users") @islandPart("row") <p>One</p> @endislandPart @endisland @island(name: "users") @islandPart("row") <span>Two</span> @endislandPart @endisland</div>')->instance();
        $component->renderIslandPart('users', 'row', 'row');
        $parts = $component->getRenderedIslandParts();
        $this->assertCount(2, $parts);
        $this->assertNotSame($parts[0]['token'], $parts[1]['token']);
        $this->assertStringContainsString('<p', $parts[0]['html']);
        $this->assertStringContainsString('<span', $parts[1]['html']);
    }

    #[DataProvider('invalidOperations')]
    public function test_invalid_operations_are_rejected_on_the_server($island, $name, $target, $mode)
    {
        $this->expectException(\InvalidArgumentException::class);
        $component = Livewire::test(IslandPartsTestComponent::class)->instance();
        $component->renderIslandPart($island, $name, $target, mode: $mode);
    }

    public static function invalidOperations()
    {
        return [['missing', 'row', 'row-1', 'morph'], ['users', 'missing', 'row-1', 'morph'], ['users', 'row', '', 'morph'], ['users', 'row', 'row-1', 'replace']];
    }

    protected function template($template)
    {
        return Livewire::test(new class($template) extends Component {
            protected static $template;
            public $id = 'a"<&';
            public function __construct($template = null) { if ($template !== null) static::$template = $template; }
            public function render() { return static::$template; }
        });
    }
}

class IslandPartsTestComponent extends Component
{
    public static $partRenders;
    public static $rootRenders;
    public static $listEvaluations;
    public $rows = [];
    public $cursor = 2;
    public $always = false;

    public function mount($count = 2, $always = false)
    {
        static::$partRenders = static::$rootRenders = static::$listEvaluations = 0;
        $this->always = $always;
        $this->rows = $count ? array_map(fn ($id) => ['id' => $id, 'name' => "Row {$id}"], range(1, $count)) : [];
    }

    public function change($mode = 'morph', $target = 'row-1', $skip = true)
    {
        $this->renderIslandPart('users', 'row', $target, with: ['row' => ['id' => substr($target, 4), 'name' => 'Updated']], mode: $mode);
        if ($skip) $this->skipRender();
    }

    public function remove()
    {
        $this->removeIslandPart('users', 'row', 'row-1');
        $this->skipRender();
    }

    public function refreshIsland($mode = 'morph')
    {
        $this->renderIsland('users', mode: $mode);
        $this->skipRender();
    }

    public function renderIslandView($name, $token, $data = [])
    {
        if (str_contains($token, '-part-')) static::$partRenders++;
        return parent::renderIslandView($name, $token, $data);
    }

    public function trackList() { static::$listEvaluations++; }

    public function render()
    {
        static::$rootRenders++;
        return <<<'HTML'
        <div>
            <ul>@island(name: 'users', always: $always)
                @php $this->trackList(); @endphp
                @foreach($rows as $row)
                    @islandPart(name: 'row')
                        <li wire:key="row-{{ $row['id'] }}">{{ $row['name'] }}</li>
                    @endislandPart
                @endforeach
            @endisland</ul>
        </div>
        HTML;
    }
}
