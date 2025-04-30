<?php

namespace Livewire\Features\SupportCompiledWireKeys;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;
use PHPUnit\Framework\Attributes\DataProvider;

class UnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Livewire::flushState();
    }

    public function test_we_can_open_a_loop()
    {
        SupportCompiledWireKeys::openLoop();

        $this->assertEquals(1, count(SupportCompiledWireKeys::$loopStack));
        $this->assertEquals([
            'count' => 0,
            'index' => null,
            'key' => null,
            'open' => true,
        ], SupportCompiledWireKeys::$loopStack[0]);
    }

    public function test_we_can_close_a_loop()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();

        $this->assertEquals(1, count(SupportCompiledWireKeys::$loopStack));
        $this->assertEquals([
            'count' => 0,
            'index' => null,
            'key' => null,
            'open' => false,
        ], SupportCompiledWireKeys::$loopStack[0]);
    }

    public function test_we_can_open_a_second_loop_after_the_first_one_is_closed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::openLoop();

        $this->assertEquals(1, count(SupportCompiledWireKeys::$loopStack));
        $this->assertEquals([
            'count' => 1,
            'index' => null,
            'key' => null,
            'open' => true,
        ], SupportCompiledWireKeys::$loopStack[0]);
    }

    public function test_we_can_close_a_second_loop_after_the_first_one_is_closed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();

        $this->assertEquals(1, count(SupportCompiledWireKeys::$loopStack));
        $this->assertEquals([
            'count' => 1,
            'index' => null,
            'key' => null,
            'open' => false,
        ], SupportCompiledWireKeys::$loopStack[0]);
    }

    public function test_we_can_open_an_inner_loop_while_the_first_one_is_open()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();

        $this->assertEquals(2, count(SupportCompiledWireKeys::$loopStack));
        $this->assertEquals([
            'count' => 0,
            'index' => null,
            'key' => null,
            'open' => true,
        ], SupportCompiledWireKeys::$loopStack[0]);
        $this->assertEquals([
            'count' => 0,
            'index' => null,
            'key' => null,
            'open' => true,
        ], SupportCompiledWireKeys::$loopStack[1]);
    }

    public function test_we_can_close_an_inner_loop_while_the_first_one_is_open()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();

        $this->assertEquals(2, count(SupportCompiledWireKeys::$loopStack));
        $this->assertEquals([
            'count' => 0,
            'index' => null,
            'key' => null,
            'open' => true,
        ], SupportCompiledWireKeys::$loopStack[0]);
        $this->assertEquals([
            'count' => 0,
            'index' => null,
            'key' => null,
            'open' => false,
        ], SupportCompiledWireKeys::$loopStack[1]);
    }

    public function test_an_inner_loop_is_removed_when_the_outer_loop_is_closed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::closeLoop();

        $this->assertEquals(1, count(SupportCompiledWireKeys::$loopStack));
        $this->assertEquals([
            'count' => 0,
            'index' => null,
            'key' => null,
            'open' => false,
        ], SupportCompiledWireKeys::$loopStack[0]);
    }

    public function test_a_second_outer_loop_is_added_when_the_first_one_is_closed_and_all_inner_loops_are_removed()
    {
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::openLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::closeLoop();
        SupportCompiledWireKeys::openLoop();
        $this->assertEquals(1, count(SupportCompiledWireKeys::$loopStack));
        $this->assertEquals([
            'count' => 1,
            'index' => null,
            'key' => null,
            'open' => true,
        ], SupportCompiledWireKeys::$loopStack[0]);
    }
    

    #[DataProvider('templatesProvider')]
    public function test_we_can_correctly_find_wire_keys_on_elements_only_but_not_blade_or_livewire_components($occurrences, $template)
    {
        $compiled = $this->compile($template);

        $this->assertOccurrences($occurrences, '<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processKey', $compiled);
    }

    public static function templatesProvider()
    {
        return [
            [
                1,
                <<<'HTML'
                <div wire:key="foo">
                </div>
                HTML
            ],
            [
                2,
                <<<'HTML'
                <div wire:key="foo">
                    <div wire:key="bar">
                    </div>
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <div>
                    <livewire:child wire:key="foo" />
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         <livewire:child :wire:key="$child, 5, '_', STR_PAD_BOTH)" />
                     @endforeach
                 </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <div>
                    @livewire('child', [], key('foo'))
                </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         @livewire('child', [], key($child, 5, '_', STR_PAD_BOTH)))
                     @endforeach
                 </div>
                HTML
            ],
            [
                0,
                <<<'HTML'
                <x-basic-component wire:key="foo">
                    Some contents
                </x-basic-component>
                HTML
            ],
            [
                0,
                <<<'HTML'
                 <div>
                     @foreach ($children as $child)
                         <x-basic-component :wire:key="$child">
                             <livewire:child />
                         </x-basic-component>
                     @endforeach
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
