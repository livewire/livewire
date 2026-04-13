<?php

namespace Livewire\Features\SupportWireShow;

use Livewire\Attributes\Validate;
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

    public function test_wire_show_reacts_to_validation_errors_from_the_magic_errors_object()
    {
        Livewire::visit(new class extends Component {
            #[Validate('required')]
            public string $email = '';

            public function save()
            {
                $this->validate();
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <form wire:submit="save">
                        <input type="email" wire:model="email">

                        <div wire:show="$errors.has('email')" dusk="email-error">
                            <span wire:text="$errors.first('email')" dusk="email-error-text"></span>
                        </div>

                        <button type="submit" dusk="save">Save</button>
                    </form>
                </div>
                HTML;
            }
        })
        ->assertMissing('@email-error')
        ->waitForLivewire()->click('@save')
        ->assertVisible('@email-error')
        ->assertSeeIn('@email-error-text', 'The email field is required.');
    }
}
