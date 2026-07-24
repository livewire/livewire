<?php

namespace Livewire\Features\SupportPropertyFactories;

use Livewire\Attributes\Factory;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Selection;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_factory_state_reaches_the_browser_and_updates_sync_back()
    {
        Livewire::visit(new class extends Component {
            public $runs = 0;

            #[Factory]
            public function selection(): Selection
            {
                $this->runs++;

                return new Selection(keys: ['2']);
            }

            public function pick($key)
            {
                $this->selection->select($key);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />
                    <input type="checkbox" dusk="three" wire:model="selection" value="3" />

                    <button dusk="pick-one" type="button" wire:click="pick('1')">Pick one</button>
                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="server">{{ implode(',', $selection->keys()) }}:{{ $runs }}</span>
                </div>
                HTML;
            }
        })
        // The factory-built state hydrated into the browser like a normal property...
        ->assertNotChecked('@one')
        ->assertChecked('@two')
        ->assertSeeIn('@server', '2:1')
        // A checkbox update syncs back, and the factory ran again server-side...
        ->check('@three')
        ->waitForLivewire()->click('@refresh')
        ->assertChecked('@three')
        ->assertSeeIn('@server', '2,3:2')
        // A server-side mutation from an action reaches the checkboxes...
        ->waitForLivewire()->click('@pick-one')
        ->assertChecked('@one')
        ->assertSeeIn('@server', '2,3,1:3')
        ;
    }

    public function test_select_all_factory_config_and_server_totals_survive_round_trips()
    {
        Livewire::visit(new class extends Component {
            #[Factory]
            public function selection(): Selection
            {
                // The total only ever lives server-side — surviving round
                // trips proves client state hydrates INTO the factory-built
                // instance rather than replacing it...
                return (new Selection(keys: ['2'], mode: 'except'))->setTotal(5);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="server">{{ $selection->isAll() ? 'all' : 'some' }}:{{ implode(',', $selection->except()) }}:{{ $selection->count() }}</span>
                </div>
                HTML;
            }
        })
        // Select-all-except-2 straight out of the factory...
        ->assertChecked('@one')
        ->assertNotChecked('@two')
        ->assertSeeIn('@server', 'all:2:4')
        // Selecting the exception empties the except list...
        ->check('@two')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', 'all::5')
        // Deselecting adds a fresh exception — mode and total intact...
        ->uncheck('@one')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', 'all:1:4')
        ;
    }
}
