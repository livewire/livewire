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

    public function test_confirm_dialog_follows_its_attribute_after_a_morph()
    {
        Livewire::visit(new class extends Component {
            public $message = 'Initial confirmation';
            public $count = 0;

            public function increment() { $this->count++; }

            public function render() { return <<<'HTML'
            <div>
                <button type="button" id="increment" dusk="button" wire:click="increment"
                    @if ($message) wire:confirm="{{ $message }}" @endif
                >Increment</button>
                <button type="button" dusk="change" wire:click="$set('message', 'Updated confirmation')">Change message</button>
                <button type="button" dusk="remove" wire:click="$set('message', '')">Remove confirmation</button>
                <span dusk="count">{{ $count }}</span>
            </div>
            HTML; }
        })
        ->tap(fn ($browser) => $browser->script('window.confirmButton = document.getElementById("increment")'))
        ->click('@button')
        ->assertDialogOpened('Initial confirmation')
        ->dismissDialog()
        ->waitForLivewire()->click('@change')
        ->click('@button')
        ->assertDialogOpened('Updated confirmation')
        ->dismissDialog()
        ->waitForLivewire()->click('@remove')
        ->assertAttributeMissing('@button', 'wire:confirm')
        ->assertScript('return window.confirmButton === document.getElementById("increment")')
        ->tap(fn ($browser) => $browser->script('window.confirmCalls = 0; window.confirm = () => { window.confirmCalls++; return true }'))
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@count', '1')
        ->assertScript('return window.confirmCalls', 0)
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
