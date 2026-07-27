<?php

namespace Livewire\Features\SupportMagicEmpty;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_empty_magic_tracks_property_emptiness_across_server_roundtrips()
    {
        Livewire::visit(new class extends Component {
            public $items = [];

            public function add() { $this->items[] = 'item'; }

            public function clear() { $this->items = []; }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="add" dusk="add">Add</button>
                    <button wire:click="clear" dusk="clear">Clear</button>

                    <div wire:show="$empty('items')" dusk="empty-state">No items yet</div>
                    <div wire:show="! $empty('items')" dusk="list">Has items</div>
                </div>
                HTML;
            }
        })
        ->assertVisible('@empty-state')
        ->assertMissing('@list')
        ->waitForLivewire()->click('@add')
        ->assertMissing('@empty-state')
        ->assertVisible('@list')
        ->waitForLivewire()->click('@clear')
        ->assertVisible('@empty-state')
        ->assertMissing('@list');
    }

    public function test_empty_magic_is_reactive_to_client_side_only_mutations()
    {
        Livewire::visit(new class extends Component {
            public $items = ['item'];

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button x-on:click="$wire.items = []" dusk="clear-client">Clear client-side</button>

                    <div wire:show="$empty('items')" dusk="empty-state">No items yet</div>
                </div>
                HTML;
            }
        })
        ->assertMissing('@empty-state')
        ->click('@clear-client')
        ->waitFor('@empty-state')
        ->assertVisible('@empty-state');
    }

    public function test_empty_magic_mirrors_php_empty_semantics()
    {
        Livewire::visit(new class extends Component {
            public $nothing = null;
            public $falsy = false;
            public $zero = 0;
            public $zeroString = '0';
            public $blank = '';
            public $list = [];
            public $name = 'Caleb';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="results"
                        x-text="[
                            $wire.$empty('nothing'),
                            $wire.$empty('falsy'),
                            $wire.$empty('zero'),
                            $wire.$empty('zeroString'),
                            $wire.$empty('blank'),
                            $wire.$empty('list'),
                            $wire.$empty('name'),
                        ].join()"
                    ></span>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@results', 'true,true,true,true,true,true,false');
    }
}
