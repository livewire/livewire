<?php

namespace Livewire\Features\SupportIdDirective;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function keeps_input_value_after_component_refresh()
    {
        Livewire::visit(new class extends Component {
            function render() {
                return <<<'HTML'
                <div>
                    <div x-id="['text-input']">
                        <label :for="$id('text-input')" dusk='label'>Username</label>
                        <input type="text" :id="$id('text-input')" name="username" dusk='username'>
                    </div>
                    <button type="button" dusk='refresh' wire:click="$refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->type('@username', 'John')
            ->assertValue('@username', 'John')
            ->assertAttribute('@label', 'for', 'text-input-1')
            ->assertAttribute('@username', 'id', 'text-input-1')
            ->click('@refresh')
            ->waitForLivewire()
            ->assertValue('@username', 'John')
            ->assertAttribute('@label', 'for', 'text-input-1')
            ->assertAttribute('@username', 'id', 'text-input-1')
        ;
    }

    /** @test */
    public function keeps_same_id_after_model_live_update()
    {
        Livewire::visit(new class extends Component {
            public string $username = '';

            function render() {
                return <<<'HTML'
                <div>
                    <div x-id="['text-input']">
                        <label :for="$id('text-input')" dusk='label'>Username</label>
                        <input wire:model.live="username" type="text" :id="$id('text-input')" dusk='username'>
                    </div>
                    <button type="button" dusk='refresh' wire:click="$refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->type('@username', 'John')
            ->assertInputValue('@username', 'John')
            ->assertAttribute('@label', 'for', 'text-input-1')
            ->assertAttribute('@username', 'id', 'text-input-1')
            ->waitForLivewire()
            ->type('@username', 'Doe')
            ->assertInputValue('@username', 'Doe')
            ->assertAttribute('@label', 'for', 'text-input-1')
            ->assertAttribute('@username', 'id', 'text-input-1')
        ;
    }
}
