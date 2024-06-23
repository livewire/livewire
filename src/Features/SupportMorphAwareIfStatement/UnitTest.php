<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use PHPUnit\Framework\Attributes\DataProvider;

class UnitTest extends \Tests\TestCase
{
    public function test_conditional_markers_are_only_added_to_if_statements_wrapping_elements()
    {
        Livewire::component('foo', new class extends \Livewire\Component
        {
            public function render()
            {
                return '<div>@if (true) <div @if (true) @endif></div> @endif</div>';
            }
        });

        $output = Blade::render('
            <div>@if (true) <div></div> @endif</div>
            <livewire:foo />
        ');

        $this->assertCount(2, explode('<!--[if BLOCK]><![endif]-->', $output));
        $this->assertCount(2, explode('<!--[if ENDBLOCK]><![endif]-->', $output));
    }

    public function test_handles_custom_blade_conditional_directives()
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

        $this->assertOccurrences(1, '<!--[if BLOCK]><![endif]-->', $output);
        $this->assertOccurrences(1, '<!--[if ENDBLOCK]><![endif]-->', $output);
    }

    public function test_handles_if_statements_with_calculation_inside()
    {
        $template = '<div> @if (($someProperty) > 0) <span> {{ $someProperty }} </span> @endif </div>';

        $output = $this->compile($template);

        $this->assertOccurrences(1, '<!--[if BLOCK]><![endif]-->', $output);
        $this->assertOccurrences(1, '<!--[if ENDBLOCK]><![endif]-->', $output);
    }

    #[DataProvider('templatesProvider')]
    public function test_foo($occurrences, $template, $expectedCompiled = null)
    {
        $compiled = $this->compile($template);

        $this->assertOccurrences($occurrences, '<!--[if BLOCK]><![endif]-->', $compiled);
        $this->assertOccurrences($occurrences, '<!--[if ENDBLOCK]><![endif]-->', $compiled);

        $expectedCompiled && $this->assertEquals($expectedCompiled, $compiled);
    }

    public static function templatesProvider()
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
            25 => [
                1,
                <<<'HTML'
                @IF(true)
                    ...
                @ENDIF
                HTML
            ],
            26 => [
                1,
                <<<'HTML'
                <div>
                    @if ($someProperty > 0)
                        <span> {{ $someProperty }} </span>
                    @endif
                </div>
                HTML
            ],
            27 => [
                1,
                <<<'HTML'
                <div>
                    @if (preg_replace('/[^a-zA-Z]+/', '', $spinner))
                        <span> {{ $someProperty }} </span>
                    @endif
                </div>
                HTML
            ],
            28 => [
                2,
                <<<'HTML'
                <div>
                    @forelse([1, 2] as $post)
                        @for($i=0; $i < 10; $i++)
                            <span> {{ $i }} </span>
                        @endfor
                    @empty
                        <span> {{ $someProperty }} </span>
                    @endforelse
                </div>
                HTML,
                <<<'HTML'
                <div>
                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = [1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <!--[if BLOCK]><![endif]--><?php for($i=0; $i < 10; $i++): ?>
                            <span> <?php echo e($i); ?> </span>
                        <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span> <?php echo e($someProperty); ?> </span>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                HTML,
            ],
            29 => [
                1,
                <<<'HTML'
                @if ($item > 2 && request()->is(str(url('/'))->replace('\\', '/')))
                    foo
                @endif
                HTML,
                <<<'HTML'
                <!--[if BLOCK]><![endif]--><?php if($item > 2 && request()->is(str(url('/'))->replace('\\', '/'))): ?>
                    foo
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                HTML,
            ],
            30 => [
                1,
                <<<'HTML'
                <div> @if (preg_replace('/[^a-zA-Z]+/', '', $spinner))<span> {{ $someProperty }} </span> @endif Else</div>
                HTML
            ],
            31 => [
                1,
                <<<'HTML'
                <div> @for ($i=0; $i<3; $i++)<span> {{ $someProperty }} </span> @endfor Else</div>
                HTML
            ]
        ];
    }

    protected function compile($string)
    {
        $undo = app(ExtendBlade::class)->livewireifyBladeCompiler();

        $html = Blade::compileString($string);

        $undo();

        return $html;
    }

    protected function render($string, $data = [])
    {
        $undo = app(ExtendBlade::class)->livewireifyBladeCompiler();

        $html = Blade::render($string, $data);

        $undo();

        return $html;
    }

    protected function compileStatements($template)
    {
        $bladeCompiler = app('blade.compiler');

        return $bladeCompiler->compileStatements($template);
    }

    protected function assertOccurrences($expected, $needle, $haystack)
    {
        $this->assertEquals($expected, count(explode($needle, $haystack)) - 1);
    }
}
