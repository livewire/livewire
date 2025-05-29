<?php

namespace Livewire\Features\SupportWireShow;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_show_toggles_element_visibility()
    {
        Livewire::visit(new class extends Component {
            public $show = true;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>
                    <div wire:show="show" dusk="hello">Hello</div>
                </div>
                HTML;
            }
        })
        ->assertVisible('@hello')
        ->assertSee('Hello')
        ->waitForLivewire()->click('@toggle')
        ->assertMissing('@hello')
        ->assertDontSee('Hello');
    }

    public function test_wire_show_does_not_display_elements_when_property_is_initially_false()
    {
        Livewire::visit(new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>
                    <div wire:show="show" dusk="hello">Hello</div>
                </div>
                HTML;
            }
        })
        ->assertMissing('@hello')
        ->assertDontSee('Hello')
        ->waitForLivewire()->click('@toggle')
        ->assertVisible('@hello')
        ->assertSee('Hello');
    }

    public function test_wire_show_supports_the_not_operator_before_the_expression()
    {
        Livewire::visit(new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>
                    <div wire:show="!show" dusk="hello">Hello</div>
                </div>
                HTML;
            }
        })
        ->assertVisible('@hello')
        ->assertSee('Hello')
        ->waitForLivewire()->click('@toggle')
        ->assertMissing('@hello')
        ->assertDontSee('Hello');
    }

    public function test_wire_show_supports_the_not_operator_with_a_space_before_the_expression()
    {
        Livewire::visit(new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>
                    <div wire:show="! show" dusk="hello">Hello</div>
                </div>
                HTML;
            }
        })
        ->assertVisible('@hello')
        ->assertSee('Hello')
        ->waitForLivewire()->click('@toggle')
        ->assertMissing('@hello')
        ->assertDontSee('Hello');
    }

    public function test_wire_show_supports_the_important_modifier()
    {
        Livewire::visit(new class extends Component {
            public $show = true;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>
                    <div wire:show.important="show" dusk="hello">Hello</div>
                </div>
                HTML;
            }
        })
        ->assertVisible('@hello')
        ->assertSee('Hello')
        ->waitForLivewire()->click('@toggle')
        ->assertMissing('@hello')
        ->assertDontSee('Hello')
        ->assertAttributeContains('@hello', 'style', 'display: none !important;');
    }
}
