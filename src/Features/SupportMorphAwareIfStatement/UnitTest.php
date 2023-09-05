<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Illuminate\View\Factory;
use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function conditional_markers_are_only_added_to_if_statements_wrapping_elements()
    {
        Livewire::component('foo', new class extends \Livewire\Component {
            public function render() {
                return '<div>@if (true) <div @if (true) @endif></div> @endif</div>';
            }
        });

        $output = Blade::render('
            <div>@if (true) <div></div> @endif</div>
            <livewire:foo />
        ');

        $this->assertCount(2, explode('__BLOCK__', $output));
        $this->assertCount(2, explode('__ENDBLOCK__', $output));
    }

    /** @test */
    public function handles_custom_blade_conditional_directives()
    {
        Blade::if('foo', function () {
            return '...';
        });

        $output = $this->compile(<<<'HTML'
        <div>
            @foo (true)
                ...
            @endfoo
        </div>
        HTML);

        $this->assertOccurrences(1, '__BLOCK__', $output);
        $this->assertOccurrences(1, '__ENDBLOCK__', $output);
    }

    /**
     * @test
     * @dataProvider templatesProvider
     **/
    function foo($occurances, $template, $expectedCompiled = null)
    {
        $compiled = $this->compile($template);

        $expectedCompiled && $this->assertEquals($expectedCompiled, $compiled);

        $this->assertNotEmpty($compiled);

        $this->assertOccurrences($occurances, '__BLOCK__', $compiled);
        $this->assertOccurrences($occurances, '__ENDBLOCK__', $compiled);
    }

    /**
     * @test
     * @dataProvider filamentTemplatesProvider
     **/
    function fooFilament($occurances, $template, $expectedCompiled = null)
    {
        app(Factory::class)->addNamespace('filament-tables', __DIR__ . '/../../../tests/views');

        $compiled = $this->compile($template);

        $expectedCompiled && $this->assertEquals($expectedCompiled, $compiled);

        $this->assertNotEmpty($compiled);

        $this->assertOccurrences($occurances, '__BLOCK__', $compiled);
        $this->assertOccurrences($occurances, '__ENDBLOCK__', $compiled);
    }

    public function templatesProvider()
    {
        return [
            0 => [
                2,
                <<<'HTML'
                <div @if (true) other @endif>
                    @if (true)
                        foo
                    @endif

                    @if (true)
                        bar
                    @endif
                </div>
                HTML
            ],
            1 => [
                0,
                <<<'HTML'
                <div
                    @if ($foo->bar)
                        @click="baz"
                    @endif
                >
                </div>
                HTML
            ],
            2 => [
                1,
                <<<'HTML'
                <div>
                    <div @class(['foo(bar)'])></div>

                    <div>
                        @if (true)
                            <div foo="{
                                'bar': 'baz',
                            }"></div>
                        @endif
                    </div>
                </div>
                HTML
            ],
            3 => [
                1,
                <<<'HTML'
                <div>
                    <div @class(['foo[bar]'])></div>

                    <div>
                        @if (true)
                            <div foo="{
                                'bar': 'baz',
                            }"></div>
                        @endif
                    </div>
                </div>
                HTML
            ],
            4 => [
                2,
                <<<'HTML'
                <div @foreach(range(1,4) as $key => $value) {{ $key }}="{{ $value }}" @endforeach>
                    @foreach(range(1,4) as $key => $value)
                        {{ $key }}="{{ $value }}"
                    @endforeach

                    @foreach(range(1,4) as $key => $value)
                        {{ $key }}="{{ $value }}"
                    @endforeach
                </div>
                HTML
            ],
            5 => [
                0,
                <<<'HTML'
                <div
                    @if ($foo)
                        {{ $foo->bar }}
                    @endif

                    @if ($foo)
                        {{ $foo=>bar }}="bar"
                    @endif
                >
                </div>
                HTML
            ],
            6 => [
                0,
                <<<'HTML'
                <div
                    @if ($foo)
                        foo="{{ $foo->bar }}"
                    @endif

                    @if ($foo)
                        foo="{{ $foo=>bar }}"
                    @endif
                >
                </div>
                HTML
            ],
            7 => [
                0,
                <<<'HTML'
                <div
                    @if ($foo->bar)
                        foo="bar"
                    @endif

                    @if ($foo=>bar)
                        foo="bar"
                    @endif
                >
                </div>
                HTML
            ],
            8 => [
                2,
                <<<'HTML'
                <div>
                    {{ 1 < 5 ? "true" : "false" }}

                    @foreach(range(1,4) as $key => $value)
                        {{ $key }}="{{ $value }}"
                    @endforeach

                    @foreach(range(1,4) as $key => $value)
                        {{ $key }}="{{ $value }}"
                    @endforeach
                </div>
                HTML
            ],
            9 => [
                2,
                <<<'HTML'
                <div> @if (true) @if (true) <div></div> @endif @endif </div>
                HTML
            ],
            10 => [
                1,
                <<<'HTML'
                <div
                    @class([
                        'flex',
                    ])

                    @if(true)
                        data-no-block
                    @endif
                >
                    @if(true)
                        <span>foo</span>
                    @endif
                </div>
                HTML
            ],
            11 => [
                1,
                <<<'HTML'
                <div
                    @class([
                        'flex' => true,
                    ])

                    @if(true)
                        data-no-block
                    @endif
                >
                    @if(true)
                        <span>foo</span>
                    @endif
                </div>
                HTML
            ],
            12 => [
                2,
                <<<'HTML'
                <div>
                    @if (true)
                        Dispatch up worked!
                    @endif

                    @if (true)
                        Dispatch to worked!
                    @endif
                </div>
                HTML
            ],
            13 => [
                0,
                <<<'HTML'
                <div {{ $object->method("test {$foo}") }} @if (true) bar="bob" @endif></div>
                HTML
            ],
            14 => [
                0,
                <<<'HTML'
                <div {{ $object->method("test {$foo}") }} @if (true) bar="bob" @endif></div>
                HTML
            ],
            15 => [
                0,
                <<<'HTML'
                <div @if ($object->method() && $object->property) foo="bar" @endif something="here"></div>
                HTML
            ],
            16 => [
                0,
                <<<'HTML'
                <div something="here" @if ($object->method() && $object->property) foo="bar" @endif something="here"></div>
                HTML
            ],
            17 => [
                0,
                <<<'HTML'
                <div @if ($object->method() && $object->method()) foo="bar" @endif something="here"></div>
                HTML
            ],
            18 => [
                0,
                <<<'HTML'
                <div something="here" @if ($object->method() && $object->method()) foo="bar" @endif something="here"></div>
                HTML
            ],
            19 => [
                1,
                <<<'HTML'
                <div>
                    @forelse($posts as $post)
                        ...
                    @empty
                        ...
                    @endforelse
                </div>
                HTML
            ],
            20 => [
                0,
                <<<'HTML'
                <div>
                    @unlessfoo(true)
                    <div class="col-span-3 text-right">
                       toots
                    </div>
                    @endunlessfoo
                </div>
                HTML,
                <<<'HTML'
                <div>
                    @unlessfoo(true)
                    <div class="col-span-3 text-right">
                       toots
                    </div>
                    @endunlessfoo
                </div>
                HTML
            ],
            21 => [
                0,
                <<<'HTML'
                <div @if (0 < 1) bar="bob" @endif></div>
                HTML
            ],
            22 => [
                0,
                <<<'HTML'
                <div @if (1 > 0 && 0 < 1) bar="bob" @endif></div>
                HTML
            ],
            23 => [
                0,
                <<<'HTML'
                <div @if (1 > 0) bar="bob" @endif></div>
                HTML
            ],
            24 => [
                1,
                <<<'HTML'
                <div>
                    @empty($foo)
                        ...
                    @endempty
                </div>
                HTML
            ],
        ];
    }

    public function filamentTemplatesProvider()
    {
        return [
            0 => [
                5,
                <<<'HTML'
                <div>
                                <x-filament-tables::generic>
                                    @if (count($records = []))
                                        @php
                                            $isRecordRowStriped = false;
                                            $previousRecord = null;
                                            $previousRecordGroupKey = null;
                                            $previousRecordGroupTitle = null;
                                        @endphp

                                        @foreach ($records as $record)
                                            @php
                                                $group = null;
                                                $recordAction = $getRecordAction($record);
                                                $recordKey = $getRecordKey($record);
                                                $recordUrl = $getRecordUrl($record);
                                                $recordGroupKey = $group?->getKey($record);
                                                $recordGroupTitle = $group?->getTitle($record);
                                            @endphp

                                            @if (! $isGroupsOnly)
                                                <x-filament-tables::generic
                                                    :alpine-hidden="($group?->isCollapsible() ? 'true' : 'false') . ' && isGroupCollapsed(\'' . $recordGroupTitle . '\')'"
                                                    :alpine-selected="'isRecordSelected(\'' . $recordKey . '\')'"
                                                    :record-action="$recordAction"
                                                    :record-url="$recordUrl"
                                                    :striped="$isStriped && $isRecordRowStriped"
                                                    :wire:key="$this->getId() . '.table.records.' . $recordKey"
                                                    :x-sortable-handle="$isReordering"
                                                    :x-sortable-item="$isReordering ? $recordKey : null"
                                                    @class([
                                                        'group cursor-move' => $isReordering,
                                                        ...$getRecordClasses($record),
                                                    ])
                                                >
                                                    @if (false)
                                                        <x-filament-tables::generic>
                                                            <x-filament-tables::generic />
                                                        </x-filament-tables::generic>
                                                    @endif

                                                    @if (count($actions) && $actionsPosition === ActionsPosition::BeforeCells && (! $isReordering))
                                                        <x-filament-tables::generic>
                                                            <x-filament-tables::generic
                                                                :actions="$actions"
                                                                :alignment="$actionsAlignment"
                                                                :record="$record"
                                                            />
                                                        </x-filament-tables::generic>
                                                    @endif
                                                </x-filament-tables::generic>
                                            @endif
                                        @endforeach
                                    @endif
                                </x-filament-tables::generic>

                    <div>This should be rendered below</div>
                </div>
                HTML
            ],
        ];
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
}
