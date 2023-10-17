<?php

namespace Livewire\Features\SupportWireConfirm;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_confirm_an_action()
    {
        Livewire::visit(new class extends Component
        {
            public $confirmed = false;

            public function someAction()
            {
                $this->confirmed = true;
            }

            public function render()
            {
                return <<<'HTML'
            <div>
                <button type="button" dusk="button" wire:click="someAction" wire:confirm>Confirm</button>

                @if ($confirmed) <span dusk="success">Confirmed!</span> @endif
            </div>
            HTML;
            }
        })
            ->assertDontSee('Confirmed!')
            ->click('@button')
            ->assertDialogOpened('Are you sure?')
            ->dismissDialog()
            ->pause(500)
            ->assertDontSee('Confirmed!')
            ->click('@button')
            ->assertDialogOpened('Are you sure?')
            ->acceptDialog()
            ->waitForText('Confirmed!');
    }

    /** @test */
    public function custom_confirm_message()
    {
        Livewire::visit(new class extends Component
        {
            public $confirmed = false;

            public function someAction()
            {
                $this->confirmed = true;
            }

            public function render()
            {
                return <<<'HTML'
            <div>
                <button type="button" dusk="button" wire:click="someAction" wire:confirm="Foo bar">Confirm</button>

                @if ($confirmed) <span dusk="success">Confirmed!</span> @endif
            </div>
            HTML;
            }
        })
            ->click('@button')
            ->assertDialogOpened('Foo bar');
    }

    /** @test */
    public function can_prompt_a_user_for_a_match()
    {
        Livewire::visit(new class extends Component
        {
            public $confirmed = false;

            public function someAction()
            {
                $this->confirmed = true;
            }

            public function render()
            {
                return <<<'HTML'
            <div>
                <button type="button" dusk="button" wire:click="someAction"
                    wire:confirm.prompt="Type foobar|foobar"
                >Confirm</button>

                @if ($confirmed) <span dusk="success">Confirmed!</span> @endif
            </div>
            HTML;
            }
        })
            ->click('@button')
            ->assertDialogOpened('Type foobar')
            ->dismissDialog()
            ->pause(500)
            ->assertDontSee('Confirmed!')
            ->click('@button')
            ->assertDialogOpened('Type foobar')
            ->typeInDialog('foob')
            ->acceptDialog()
            ->pause(500)
            ->assertDontSee('Confirmed!')
            ->click('@button')
            ->assertDialogOpened('Type foobar')
            ->typeInDialog('foobar')
            ->acceptDialog()
            ->waitForText('Confirmed!');
    }
}
