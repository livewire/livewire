<?php

namespace Livewire\Features\SupportWireModelOnDialogAndPopover;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_model_works_inside_dialog()
    {
        Livewire::visit(new class extends Component {
            public $username = '';
            public $email = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="open-dialog" onclick="document.getElementById('testDialog').showModal()">
                            Open Dialog
                        </button>

                        <dialog id="testDialog" dusk="dialog-content">
                            <div style="padding: 20px;">
                                <h2>Test Dialog</h2>

                                <div style="margin-bottom: 10px;">
                                    <label>Username (wire:model.blur)</label>
                                    <input 
                                        type="text" 
                                        wire:model.blur="username"
                                        dusk="username-input"
                                    >
                                    <div dusk="username-value">{{ $username }}</div>
                                </div>

                                <div style="margin-bottom: 10px;">
                                    <label>Email (wire:model.live)</label>
                                    <input 
                                        type="email" 
                                        wire:model.live="email"
                                        dusk="email-input"
                                    >
                                    <div dusk="email-value">{{ $email }}</div>
                                </div>

                                <button type="button" onclick="document.getElementById('testDialog').close()">Close</button>
                            </div>
                        </dialog>
                    </div>
                BLADE;
            }
        })
            ->click('@open-dialog')
            ->pause(100)
            ->type('@username-input', 'testuser')
            ->click('@email-input') // Trigger blur on username
            ->pause(200)
            ->assertSeeIn('@username-value', 'testuser')
            ->assertVisible('@dialog-content');
    }

    public function test_dialog_stays_open_during_wire_model_live_updates()
    {
        Livewire::visit(new class extends Component {
            public $email = '';

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="open-dialog" onclick="document.getElementById('testDialog').showModal()">
                            Open Dialog
                        </button>

                        <dialog id="testDialog" dusk="dialog-content">
                            <div style="padding: 20px;">
                                <h2>Test Dialog</h2>
                                
                                <div style="margin-bottom: 10px;">
                                    <label>Email (wire:model.live)</label>
                                    <input 
                                        type="email" 
                                        wire:model.live="email"
                                        dusk="email-input"
                                    >
                                    <div dusk="email-value">{{ $email }}</div>
                                </div>

                                <button type="button" onclick="document.getElementById('testDialog').close()">Close</button>
                            </div>
                        </dialog>
                    </div>
                BLADE;
            }
        })
            ->click('@open-dialog')
            ->pause(100)
            ->type('@email-input', 'test@example.com')
            ->pause(300)
            ->assertVisible('@dialog-content')
            ->assertSeeIn('@email-value', 'test@example.com')
            ->assertVisible('@dialog-content');
    }

    public function test_can_submit_form_inside_dialog()
    {
        Livewire::visit(new class extends Component {
            public $username = '';
            public $email = '';
            public $submitted = false;

            public function submit()
            {
                $this->submitted = true;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="open-dialog" onclick="document.getElementById('testDialog').showModal()">
                            Open Dialog
                        </button>

                        <dialog id="testDialog" dusk="dialog-content">
                            <div style="padding: 20px;">
                                <h2>Test Dialog</h2>

                                <form wire:submit="submit">
                                    <div style="margin-bottom: 10px;">
                                        <label>Username</label>
                                        <input 
                                            type="text" 
                                            wire:model.blur="username"
                                            dusk="username-input"
                                        >
                                    </div>

                                    <div style="margin-bottom: 10px;">
                                        <label>Email</label>
                                        <input 
                                            type="email" 
                                            wire:model.live="email"
                                            dusk="email-input"
                                        >
                                    </div>

                                    <button type="submit" dusk="submit-button">Submit</button>
                                </form>

                                @if($submitted)
                                    <div dusk="submitted" style="margin-top: 20px;">
                                        <strong>Submitted!</strong><br>
                                        Username: {{ $username }}<br>
                                        Email: {{ $email }}
                                    </div>
                                @endif
                            </div>
                        </dialog>
                    </div>
                BLADE;
            }
        })
            ->click('@open-dialog')
            ->pause(100)
            ->type('@username-input', 'john')
            ->type('@email-input', 'john@example.com')
            ->pause(200)
            ->click('@submit-button')
            ->pause(200)
            ->assertSeeIn('@submitted', 'john')
            ->assertSeeIn('@submitted', 'john@example.com');
    }
}
