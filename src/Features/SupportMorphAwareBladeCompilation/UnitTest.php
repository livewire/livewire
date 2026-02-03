<?php

namespace Livewire\Features\SupportMorphAwareBladeCompilation;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;

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

    public function test_loop_markers_are_not_output_when_smart_wire_keys_are_disabled()
    {
        Livewire::flushState();

        config()->set('livewire.smart_wire_keys', false);

        // Reload the features so the config is loaded and the precompilers are registered if required...
        $this->reloadFeatures();

        $compiled = $this->compile(<<< 'HTML'
        <div>
            @foreach([1, 2, 3] as $item)
                <div wire:key="{{ $item }}">
                    {{ $item }}
                </div>
            @endforeach
        </div>
        HTML);

        $this->assertStringNotContainsString('SupportCompiledWireKeys::openLoop(', $compiled);
        $this->assertStringNotContainsString('SupportCompiledWireKeys::startLoop(', $compiled);
        $this->assertStringNotContainsString('SupportCompiledWireKeys::endLoop(', $compiled);
        $this->assertStringNotContainsString('SupportCompiledWireKeys::closeLoop(', $compiled);
    }

    public function test_conditional_markers_are_still_output_when_smart_wire_keys_are_disabled()
    {
        Livewire::flushState();

        config()->set('livewire.smart_wire_keys', false);

        // Reload the features so the config is loaded and the precompilers are registered if required...
        $this->reloadFeatures();

        $compiled = $this->compile(<<<'HTML'
        <div>
            @if(true)
                foo
            @endif
        </div>
        HTML);

        $this->assertStringContainsString('<!--[if BLOCK]><![endif]-->', $compiled);
        $this->assertStringContainsString('<!--[if ENDBLOCK]><![endif]-->', $compiled);
    }

    public function test_conditional_markers_are_not_output_when_inject_morph_markers_is_disabled()
    {
        Livewire::flushState();

        config()->set('livewire.inject_morph_markers', false);

        // Reload the features so the config is loaded and the precompilers are registered if required...
        $this->reloadFeatures();

        $compiled = $this->compile(<<< 'HTML'
        <div>
            @if (true) <div @if (true) @endif></div> @endif
        </div>
        HTML);

        $this->assertStringNotContainsString('<!--[if BLOCK]><![endif]-->', $compiled);
        $this->assertStringNotContainsString('<!--[if ENDBLOCK]><![endif]-->', $compiled);
    }

    public function test_loop_markers_are_still_output_when_inject_morph_markers_is_disabled()
    {
        Livewire::flushState();

        config()->set('livewire.inject_morph_markers', false);

        // Reload the features so the config is loaded and the precompilers are registered if required...
        $this->reloadFeatures();

        $compiled = $this->compile(<<<'HTML'
        <div>
            @foreach([1, 2, 3] as $item)
                <div wire:key="{{ $item }}">
                    {{ $item }}
                </div>
            @endforeach
        </div>
        HTML);

        $this->assertStringContainsString('SupportCompiledWireKeys::openLoop(', $compiled);
        $this->assertStringContainsString('SupportCompiledWireKeys::startLoop(', $compiled);
        $this->assertStringContainsString('SupportCompiledWireKeys::endLoop(', $compiled);
        $this->assertStringContainsString('SupportCompiledWireKeys::closeLoop(', $compiled);
    }

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

    public function test_morph_markers_are_not_output_when_not_used_within_a_livewire_context()
    {
        $template = <<<'HTML'
        <div>
            @if (true)
                <span>Test</span>
            @endif
        </div>
        HTML;

        $output = $this->render($template);

        $this->assertStringContainsString('Test', $output);
        $this->assertOccurrences(0, '<!--[if BLOCK]><![endif]-->', $output);
        $this->assertOccurrences(0, '<!--[if ENDBLOCK]><![endif]-->', $output);
    }

    public function test_morph_markers_are_output_when_used_within_a_livewire_context()
    {
        Livewire::component('foo', new class extends \Livewire\Component
        {
            public function render()
            {
                return <<<'HTML'
                <div>
                    @if (true)
                        <span>Test</span>
                    @endif
                </div>
                HTML;
            }
        });

        $output = $this->render('<livewire:foo />');

        $this->assertStringContainsString('Test', $output);
        $this->assertOccurrences(1, '<!--[if BLOCK]><![endif]-->', $output);
        $this->assertOccurrences(1, '<!--[if ENDBLOCK]><![endif]-->', $output);
    }

    public function test_loop_trackers_are_not_used_when_not_within_a_livewire_context()
    {
        $template = <<<'HTML'
        <div>
            @foreach ([1, 2, 3] as $item)
                <span>Test</span>
            @endforeach
        </div>
        HTML;

        $output = $this->render($template);

        $this->assertStringContainsString('Test', $output);

        // When the template is rendered, there should be no loop trackers in the stack...
        $this->assertEmpty(SupportCompiledWireKeys::$loopStack);
    }

    public function test_loop_trackers_are_used_when_used_within_a_livewire_context()
    {
        Livewire::component('foo', new class extends \Livewire\Component
        {
            public function render()
            {
                return <<<'HTML'
                <div>
                    @foreach ([1, 2, 3] as $item)
                        <span>Test</span>
                    @endforeach
                </div>
                HTML;
            }
        });

        $output = $this->render('<livewire:foo />');

        $this->assertStringContainsString('Test', $output);

        // When the template is rendered, there should be 1 loop in the stack, which will be a count of 0 so we don't have an offset compared to the loop indexes...
        $this->assertEquals(0, SupportCompiledWireKeys::$currentLoop['count']);
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = [1, 2]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i=0; $i < 10; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <span> <?php echo e($i); ?> </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <span> <?php echo e($someProperty); ?> </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item > 2 && request()->is(str(url('/'))->replace('\\', '/'))): ?>
                    foo
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
            ],
            32 => [
                0,
                <<<'HTML'
                <style>
                    @supports (filter: drop-shadow(0 0 0 #ccc)) {
                        background-color: blue;
                    }
                </style>
                HTML
            ],
            33 => [
                0,
                <<<'HTML'
                <div>
                    @@if (true)
                </div>
                HTML
            ],
            34 => [
                0,
                <<<'HTML'
                <div>
                    <script>
                        @if (app()->environment('production'))
                            console.debug('tracking enabled');
                        @else
                            console.debug('tracking disabled');
                        @endif
                    </script>
                </div>
                HTML
            ],
            35 => [
                0,
                <<<'HTML'
                <div>
                    <style>
                        @if (app()->environment('local'))
                            body {
                                background-color: red;
                            }
                        @endif
                    </style>
                </div>
                HTML
            ],
            36 => [
                1,
                <<<'HTML'
                <div>
                    <div id="label">
                        @if (now()->dayName === 'Friday')
                            Friday
                        @endif
                    </div>

                    <script type="text/javascript">
                        @if (now()->dayName === 'Friday')
                            document.getElementById('label').style.color = 'red';
                        @endif
                    </script>
                </div>
                HTML
            ],
            37 => [
                2,
                <<<'HTML'
                <div>
                    <div id="label">
                        @if (now()->dayName === 'Friday')
                            Friday
                        @endif
                    </div>

                    <script type="text/javascript">
                        @if (now()->dayName === 'Friday')
                            document.getElementById('label').style.color = 'red';
                        @endif
                    </script>

                    <div id="label">
                        @if (now()->dayName === 'Friday')
                            Friday
                        @endif
                    </div>
                </div>
                HTML
            ],
            // GitHub discussion #9642: @foreach with > comparison in Blade echo (no HTML inside)
            // The bug occurs when > appears before any < in the text after the directive
            38 => [
                1,
                <<<'HTML'
                <div>
                    @foreach($items as $item)
                        {{ $item > 5 ? 'big' : 'small' }}
                    @endforeach
                </div>
                HTML
            ],
            // GitHub discussion #9776: Multiple sequential @foreach loops with > in expressions
            39 => [
                2,
                <<<'HTML'
                <div>
                    @foreach($items as $item)
                        {{ $item > 10 ? 'many' : 'few' }}
                    @endforeach

                    @foreach($other as $thing)
                        {{ $thing > 0 ? 'positive' : 'zero' }}
                    @endforeach
                </div>
                HTML
            ],
            // @if with > comparison where no HTML follows before loop end
            40 => [
                2,
                <<<'HTML'
                <div>
                    @foreach($items as $item)
                        @if($item > 5)
                            {{ $item }}
                        @endif
                    @endforeach
                </div>
                HTML
            ],
            // @if with > inside HTML tag attribute should NOT get markers
            41 => [
                0,
                <<<'HTML'
                <div @if($count > 5) class="many" @endif></div>
                HTML
            ],
            // GitHub issue #9900: dynamic Blade tag names with {{ }}
            42 => [
                0,
                <<<'HTML'
                <{{ $tagName }}
                    @if ($condition)
                        class="test-class"
                    @endif
                >
                    Content
                </{{ $tagName }}>
                HTML
            ],
            // GitHub issue #9900: dynamic Blade tag names with {!! !!}
            43 => [
                0,
                <<<'HTML'
                <{!! $tagName !!}
                    @if ($condition)
                        class="test-class"
                    @endif
                >
                    Content
                </{!! $tagName !!}>
                HTML
            ],
        ];
    }

    #[DataProvider('insideHtmlTagProvider')]
    public function test_is_inside_html_tag($expected, $template, $description = '')
    {
        // Find the | marker position, then remove it for the actual test...
        $position = strpos($template, '|');
        $this->assertNotFalse($position, 'Template must contain a | marker to indicate the position to test');

        $template = substr($template, 0, $position) . substr($template, $position + 1);

        $result = \Livewire\invade(new SupportMorphAwareBladeCompilation)->isInsideHtmlTag($template, $position);

        $this->assertSame($expected, $result, $description ?: "Expected " . ($expected ? 'inside' : 'outside') . " HTML tag");
    }

    public static function insideHtmlTagProvider()
    {
        return [
            // =============================================
            // Inside HTML tags (should return true)
            // =============================================

            'simple tag - directive after tag name' => [
                true,
                '<div |@if(true) class="foo">',
            ],
            'tag with attributes before directive' => [
                true,
                '<div class="foo" |@if(true) bar="baz">',
            ],
            'multiline tag' => [
                true,
                "<div\n    class=\"foo\"\n    |@if(true)\n>",
            ],
            'self-closing tag' => [
                true,
                '<input type="text" |@if(true) />',
            ],
            'closing tag' => [
                true,
                '</div |@if(true)>',
            ],
            'DOCTYPE' => [
                true,
                '<!DOCTYPE |html>',
            ],
            'position right before closing >' => [
                true,
                '<div |>',
            ],

            // > inside quotes (should not close the tag)
            'tag with > inside double quotes' => [
                true,
                '<div data-value="a > b" |@if(true)>',
            ],
            'tag with > inside single quotes' => [
                true,
                "<div data-value='a > b' |@if(true)>",
            ],

            // < inside quotes (should not affect tag detection)
            'tag with < inside double quotes' => [
                true,
                '<div data-value="a < b" |@if(true)>',
            ],
            'tag with < inside single quotes' => [
                true,
                "<div data-value='a < b' |@if(true)>",
            ],

            // > inside parentheses and braces (should not close the tag)
            'tag with > inside parentheses' => [
                true,
                '<div @class([$x > 0 ? "a" : "b"]) |@if(true)>',
            ],
            'tag with > inside braces' => [
                true,
                '<div {{ $x > 0 ? "a" : "b" }} |@if(true)>',
            ],
            'tag with > inside nested parentheses' => [
                true,
                '<div @class(foo(bar($x > 5))) |@if(true)>',
            ],

            // < inside parentheses and braces (should not affect tag detection)
            'tag with < inside parentheses' => [
                true,
                '<div @class([$x < 10 ? "a" : "b"]) |@if(true)>',
            ],
            'tag with < inside braces' => [
                true,
                '<div {{ $x < 10 ? "a" : "b" }} |@if(true)>',
            ],
            'tag with < inside nested parentheses' => [
                true,
                '<div @class(foo(bar($x < 5))) |@if(true)>',
            ],

            // Both < and > inside expressions
            'tag with > and < in same parenthesised expression' => [
                true,
                '<div @if($a > 0 && $b < 10) |@endif>',
            ],
            'tag with > and < in same brace expression' => [
                true,
                '<div {{ $a > 0 && $b < 10 ? "yes" : "no" }} |@if(true)>',
            ],
            'tag with multiple > inside parenthesised expression' => [
                true,
                '<div @if($a > 0 && $b > 1) |@endif>',
            ],
            'tag with multiple < inside parenthesised expression' => [
                true,
                '<div @if($a < 0 && $b < 1) |@endif>',
            ],

            // Arrow operators
            'tag with -> arrow operator' => [
                true,
                '<div @if($foo->bar) |@endif>',
            ],
            'tag with => fat arrow' => [
                true,
                '<div @class(["active" => $isActive]) |@if(true)>',
            ],

            // Blade echo in attributes
            'tag with > in Blade echo attribute' => [
                true,
                '<div class="{{ $x > 0 ? \'active\' : \'\' }}" |@if(true)>',
            ],
            'tag with < in Blade echo attribute' => [
                true,
                '<div class="{{ $x < 10 ? \'active\' : \'\' }}" |@if(true)>',
            ],

            // Escaped quotes
            'escaped single quote with > in attribute' => [
                true,
                '<div data-value="it\'s > than" |@if(true)>',
            ],
            'escaped single quote with < in attribute' => [
                true,
                '<div data-value="it\'s < than" |@if(true)>',
            ],

            // Dynamic Blade tag names
            'dynamic tag with {{ }}' => [
                true,
                '<{{ $tagName }} class="foo" |@if(true)>',
            ],
            'dynamic closing tag with {{ }}' => [
                true,
                '</{{ $tagName }} |@if(true)>',
            ],
            'dynamic tag with {!! !!}' => [
                true,
                '<{!! $tagName !!} class="foo" |@if(true)>',
            ],
            'dynamic closing tag with {!! !!}' => [
                true,
                '</{!! $tagName !!} |@if(true)>',
            ],
            'multiline dynamic tag' => [
                true,
                "<{{ \$tagName }}\n    class=\"foo\"\n    |@if(true)\n>",
            ],

            // =============================================
            // Outside HTML tags (should return false)
            // =============================================

            'after closed tag' => [
                false,
                '<div> |@if(true)',
            ],
            'no tags at all' => [
                false,
                '|@if(true)',
            ],
            'after text with no tags' => [
                false,
                'some text |@foreach($items as $item)',
            ],
            'between closed tags' => [
                false,
                '<div></div> |@if(true) <span>',
            ],
            'after self-closing tag' => [
                false,
                '<input type="text" /> |@if(true)',
            ],
            'after tag on new line' => [
                false,
                "<div>\n    |@if(true)",
            ],
            'nested inside element content' => [
                false,
                '<div><span>text</span> |@if(true) more</div>',
            ],
            'after multiple closed tags' => [
                false,
                '<div><p>text</p><span>more</span></div> |@if(true)',
            ],

            // > comparison in content (should not create tag context)
            'after > in Blade echo' => [
                false,
                '<div>{{ $x > 5 }} |@foreach($items as $item)',
            ],
            'after > comparison in ternary' => [
                false,
                '<div>{{ $item > 5 ? "big" : "small" }} |@endforeach',
            ],

            // < comparison in content (should not be treated as tag opener)
            'after < in Blade echo' => [
                false,
                '<div>{{ $x < 5 }} |@foreach($items as $item)',
            ],
            'after < comparison in ternary' => [
                false,
                '<div>{{ $item < 5 ? "small" : "big" }} |@endforeach',
            ],
            'after < comparison operator' => [
                false,
                '<div>{{ 1 < 5 ? "yes" : "no" }} |@if(true)',
            ],
            'after < with number' => [
                false,
                '<div>{{ $x < 10 }} |@if(true)',
            ],
            'after < with space and variable' => [
                false,
                '<div>{{ $a < $b }} |@if(true)',
            ],

            // Both < and > comparisons in content
            'after > then < in content' => [
                false,
                '<div>{{ $x > 5 }} {{ $y < 10 }} |@if(true)',
            ],
            'after < then > in content' => [
                false,
                '<div>{{ $x < 5 }} {{ $y > 10 }} |@if(true)',
            ],
            'after > and < in same expression' => [
                false,
                '<div>{{ $x > 5 && $y < 10 ? "yes" : "no" }} |@if(true)',
            ],
            'multiple < comparisons' => [
                false,
                '<div>{{ $a < $b }} {{ $c < $d }} |@if(true)',
            ],
            'multiple > comparisons' => [
                false,
                '<div>{{ $a > $b }} {{ $c > $d }} |@if(true)',
            ],

            // Operators
            'after << bitshift operator' => [
                false,
                '<div>{{ $x << 2 }} |@if(true)',
            ],
            'after >> bitshift operator' => [
                false,
                '<div>{{ $x >> 2 }} |@if(true)',
            ],
            'after <=> spaceship operator' => [
                false,
                '<div>{{ $a <=> $b }} |@if(true)',
            ],

            // Tags with > in attributes that are properly closed
            'after tag with > in attribute then closed' => [
                false,
                '<div data-value="a > b"> |@if(true)',
            ],
            'after tag with < in attribute then closed' => [
                false,
                '<div data-value="a < b"> |@if(true)',
            ],
            'after tag with > in parenthesised expression then closed' => [
                false,
                '<div @class([$x > 0 ? "a" : "b"])> |@if(true)',
            ],
            'after tag with < in parenthesised expression then closed' => [
                false,
                '<div @class([$x < 10 ? "a" : "b"])> |@if(true)',
            ],

            // Dynamic Blade tags that are properly closed
            'after closed dynamic {{ }} tag' => [
                false,
                '<{{ $tagName }} class="foo"> |@if(true)',
            ],
            'after closed dynamic {!! !!} tag' => [
                false,
                '<{!! $tagName !!} class="foo"> |@if(true)',
            ],

            // PHP tags and HTML comments
            'after PHP tag' => [
                false,
                '<?php echo $x; ?> |@if(true)',
            ],
            'after short PHP echo tag' => [
                false,
                '<?= $x ?> |@if(true)',
            ],
            'after HTML comment' => [
                false,
                '<!-- comment --> |@if(true)',
            ],
            'after HTML comment containing >' => [
                false,
                '<!-- x > y --> |@if(true)',
            ],
            'after HTML comment containing <' => [
                false,
                '<!-- x < y --> |@if(true)',
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
        $html = Blade::compileString($string);

        return $html;
    }

    protected function render($string, $data = [])
    {
        $html = Blade::render($string, $data);

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
