<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

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
    public function it_only_adds_condtional_markers_to_any_if_that_is_not_inside_a_html_tag()
    {
        $output = $this->compile(<<<HTML
        <div @if (true) other @endif>
            @if (true)
                foo
            @endif

            @if (true)
                bar
            @endif
        </div>
        HTML);

        $this->assertOccurrences(2, '__BLOCK__', $output);
        $this->assertOccurrences(2, '__ENDBLOCK__', $output);
    }

    /** @test */
    public function it_only_adds_condtional_markers_to_any_for_each_that_is_not_inside_a_html_tag()
    {
        $output = $this->compile(<<<'HTML'
        <div @foreach(range(1,4) as $key => $value) {{ $key }}="{{ $value }}" @endforeach>
            @foreach(range(1,4) as $key => $value)
                {{ $key }}="{{ $value }}"
            @endforeach

            @foreach(range(1,4) as $key => $value)
                {{ $key }}="{{ $value }}"
            @endforeach
        </div>
        HTML);

        $this->assertOccurrences(2, '__BLOCK__', $output);
        $this->assertOccurrences(2, '__ENDBLOCK__', $output);
    }

    /** @test */
    public function it_still_adds_conditional_markers_if_there_is_a_blade_expression_before_it_that_contains_a_less_than_symbol()
    {
        $output = $this->compile(<<<'HTML'
        <div>
            {{ 1 < 5 ? "true" : "false" }}

            @foreach(range(1,4) as $key => $value)
                {{ $key }}="{{ $value }}"
            @endforeach

            @foreach(range(1,4) as $key => $value)
                {{ $key }}="{{ $value }}"
            @endforeach
        </div>
        HTML);

        $this->assertOccurrences(2, '__BLOCK__', $output);
        $this->assertOccurrences(2, '__ENDBLOCK__', $output);
    }

    /** @test */
    public function conditional_markers_do_not_remove_nested_endif_statements_without_a_parent_tag()
    {
        Livewire::component('foo', new class extends \Livewire\Component {
            public function render() {
                return '<div> @if (true) @if (true) <div></div> @endif @endif </div>';
            }
        });

        $output = Blade::render('
            <livewire:foo />
        ');

        $this->assertOccurrences(2, '__BLOCK__', $output);
        $this->assertOccurrences(2, '__ENDBLOCK__', $output);
    }

    /** @test */
    public function supports_two_in_a_row()
    {
        $compiled = $this->compile('<div>
    @if (true)
        Dispatch up worked!
    @endif

    @if (true)
        Dispatch to worked!
    @endif
</div>');

        $this->assertEquals('<div>
    <!-- __BLOCK__ --><?php if(true): ?>
        Dispatch up worked!
    <?php endif; ?> <!-- __ENDBLOCK__ -->

    <!-- __BLOCK__ --><?php if(true): ?>
        Dispatch to worked!
    <?php endif; ?> <!-- __ENDBLOCK__ -->
</div>', $compiled);
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

