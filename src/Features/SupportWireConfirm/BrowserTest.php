<?php

namespace Livewire\Features\SupportWireConfirm;

use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public function test_can_confirm_an_action()
    {
        Livewire::visit(new class extends Component {
            public $confirmed = false;
            public function someAction() { $this->confirmed = true; }
            public function render() { return <<<'HTML'
            <div>
                <button type="button" dusk="button" wire:click="someAction" wire:confirm>Confirm</button>

                @if ($confirmed) <span dusk="success">Confirmed!</span> @endif
            </div>
            HTML; }
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
        ->waitForText('Confirmed!')
        ;
    }

    public function test_can_confirm_an_action_when_used_with_submit_directive()
    {
        $browser = Livewire::visit(new class extends Component {
            public $confirmed = false;
            public function someAction() { $this->confirmed = true; }
            public function render() { return <<<'HTML'
            <div>
                <form wire:submit="someAction" wire:confirm>
                    <input type="text" dusk="input">
                    <input type="checkbox" dusk="checkbox">
                    <button type="submit" dusk="button">Confirm</button>

                    @if ($confirmed) <span dusk="success">Confirmed!</span> @endif
                </form>
            </div>
            HTML; }
        })
        ->assertDontSee('Confirmed!')
        ->click('@button')
        ->assertDialogOpened('Are you sure?')
        ->dismissDialog()
        ->pause(500)
        ->assertDontSee('Confirmed!');

        // ensure the form is still interactable
        $this->assertEquals(null, $browser->attribute('@button', 'disabled'));
        // and so should the input
        $this->assertEquals(null, $browser->attribute('@input', 'readonly'));
        $this->assertEquals(null, $browser->attribute('@checkbox', 'disabled'));

        $browser->click('@button')
        ->assertDialogOpened('Are you sure?')
        ->acceptDialog()
        ->waitForText('Confirmed!')
        ;
    }

    public function test_custom_confirm_message()
    {
        Livewire::visit(new class extends Component {
            public $confirmed = false;
            public function someAction() { $this->confirmed = true; }
            public function render() { return <<<'HTML'
            <div>
                <button type="button" dusk="button" wire:click="someAction" wire:confirm="Foo bar">Confirm</button>

                @if ($confirmed) <span dusk="success">Confirmed!</span> @endif
            </div>
            HTML; }
        })
        ->click('@button')
        ->assertDialogOpened('Foo bar')
        ;
    }

    public function test_can_prompt_a_user_for_a_match()
    {
        Livewire::visit(new class extends Component {
            public $confirmed = false;
            public function someAction() { $this->confirmed = true; }
            public function render() { return <<<'HTML'
            <div>
                <button type="button" dusk="button" wire:click="someAction"
                    wire:confirm.prompt="Type foobar|foobar"
                >Confirm</button>

                @if ($confirmed) <span dusk="success">Confirmed!</span> @endif
            </div>
            HTML; }
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
        ->waitForText('Confirmed!')
        ;
    }

    public function test_can_prompt_a_user_for_a_match_when_used_with_submit_directive()
    {
        $browser = Livewire::visit(new class extends Component {
            public $confirmed = false;
            public function someAction() { $this->confirmed = true; }
            public function render() { return <<<'HTML'
            <div>
                <form wire:submit="someAction" wire:confirm.prompt="Type foobar|foobar">
                    <input type="text" dusk="input">
                    <input type="checkbox" dusk="checkbox">
                    <button type="submit" dusk="button">Confirm</button>

                    @if ($confirmed) <span dusk="success">Confirmed!</span> @endif
                </form>
            </div>
            HTML; }
        })
        ->click('@button')
        ->assertDialogOpened('Type foobar')
        ->dismissDialog()
        ->pause(500)
        ->assertDontSee('Confirmed!');

        // ensure the form is still interactable
        $this->assertEquals(null, $browser->attribute('@button', 'disabled'));
        // and so should the input
        $this->assertEquals(null, $browser->attribute('@input', 'readonly'));
        $this->assertEquals(null, $browser->attribute('@checkbox', 'disabled'));

        $browser->click('@button')
        ->assertDialogOpened('Type foobar')
        ->typeInDialog('foob')
        ->acceptDialog()
        ->pause(500)
        ->assertDontSee('Confirmed!')
        ->click('@button')
        ->assertDialogOpened('Type foobar')
        ->typeInDialog('foobar')
        ->acceptDialog()
        ->waitForText('Confirmed!')
        ;
    }
}
