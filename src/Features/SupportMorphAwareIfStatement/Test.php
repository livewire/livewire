<?php

namespace Livewire\Features\SupportMorphAwareIfStatement;

use Livewire\Livewire;
use Livewire\Component;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Blade;

class Test extends \Tests\TestCase
{
    /** @test */
    public function conditional_markers_are_only_added_to_if_statements_wrapping_elements()
    {
        $this->markTestSkipped(); // @todo: Reenable this failing test
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
    public function blade_conditionals_are_handled_properly_by_morphdom()
    {
        $this->markTestSkipped(); // @todo: Reenable this failing test
        $this->visit(new class extends Component {
            public $show = true;

            function toggle()
            {
                $this->show = ! $this->show;
            }

            function render() {
                return <<<'HTML'
                <div>
                    <button wire:click="toggle" dusk="toggle">Toggle</button>

                    <div>
                        @if ($show)
                            <div dusk="foo">foo</div>
                        @endif

                        <div>bar<input dusk="bar"></div>
                    </div>
                </div>
                HTML;
            }
        },

        function (Browser $browser) {
            $browser
                ->type('@bar', 'Hey!')
                ->waitForLivewire()->click('@toggle')
                ->assertInputValue('@bar', 'Hey!')
                ->assertNotPresent('@foo')
                ->waitForLivewire()->click('@toggle')
                ->assertInputValue('@bar', 'Hey!')
                ->assertVisible('@foo');
        });
    }
}

