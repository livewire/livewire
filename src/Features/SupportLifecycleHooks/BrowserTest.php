<?php

namespace Livewire\Features\SupportLifecycleHooks;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_update_hooks_consolidate_when_entire_array_is_replaced()
    {
        Livewire::visit(new class extends Component {
            public $items = ['one', 'two', 'three', 'four'];
            public $hookLog = [];

            public function updatingItems($value, $key)
            {
                $this->hookLog[] = 'updating:' . ($key ?? 'items') . ':' . json_encode($value);
            }

            public function updatedItems($value, $key)
            {
                $this->hookLog[] = 'updated:' . ($key ?? 'items') . ':' . json_encode($value);
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <div dusk="items">{{ json_encode($items) }}</div>
                        <div dusk="hooks">{{ json_encode($hookLog) }}</div>

                        <button dusk="replace-array" type="button"
                            x-on:click="$wire.items = ['two', 'three']; $wire.$commit()">
                            Replace Array
                        </button>

                        <button dusk="clear" wire:click="$set('hookLog', [])">Clear Log</button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@items', '["one","two","three","four"]')
            ->waitForLivewire()->click('@replace-array')
            ->assertSeeIn('@items', '["two","three"]')
            // Should only fire one set of hooks for 'items', not individual ones for each index
            ->assertSeeIn('@hooks', 'updating:items:')
            ->assertSeeIn('@hooks', 'updated:items:')
            ->assertDontSeeIn('@hooks', 'updating:0:')
            ->assertDontSeeIn('@hooks', 'updated:0:')
            ->assertDontSeeIn('@hooks', 'updating:1:')
            ->assertDontSeeIn('@hooks', 'updated:1:');
    }

    public function test_update_hooks_fire_for_individual_item_when_single_item_changes()
    {
        Livewire::visit(new class extends Component {
            public $items = ['one', 'two', 'three'];
            public $hookLog = [];

            public function updatingItems($value, $key)
            {
                $this->hookLog[] = 'updating:' . ($key ?? 'items') . ':' . json_encode($value);
            }

            public function updatedItems($value, $key)
            {
                $this->hookLog[] = 'updated:' . ($key ?? 'items') . ':' . json_encode($value);
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <div dusk="items">{{ json_encode($items) }}</div>
                        <div dusk="hooks">{{ json_encode($hookLog) }}</div>

                        <input dusk="item-input" type="text" wire:model.live="items.1" />
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@items', '["one","two","three"]')
            ->waitForLivewire()->type('@item-input', 'changed')
            ->assertSeeIn('@items', '["one","changed","three"]')
            // Should fire hooks for items.1, not the whole array
            ->assertSeeIn('@hooks', 'updating:1:')
            ->assertSeeIn('@hooks', 'updated:1:');
    }

    public function test_update_hooks_consolidate_when_array_size_increases()
    {
        Livewire::visit(new class extends Component {
            public $items = ['a', 'b'];
            public $hookLog = [];

            public function updatingItems($value, $key)
            {
                $this->hookLog[] = 'updating:' . ($key ?? 'items');
            }

            public function updatedItems($value, $key)
            {
                $this->hookLog[] = 'updated:' . ($key ?? 'items');
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <div dusk="items">{{ json_encode($items) }}</div>
                        <div dusk="hooks">{{ json_encode($hookLog) }}</div>

                        <button dusk="add-items" type="button"
                            x-on:click="$wire.items = ['a', 'b', 'c', 'd']; $wire.$commit()">
                            Add Items
                        </button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@items', '["a","b"]')
            ->waitForLivewire()->click('@add-items')
            ->assertSeeIn('@items', '["a","b","c","d"]')
            // Should consolidate to 'items' since size changed
            ->assertSeeIn('@hooks', 'updating:items')
            ->assertSeeIn('@hooks', 'updated:items')
            ->assertDontSeeIn('@hooks', 'updating:2')
            ->assertDontSeeIn('@hooks', 'updating:3');
    }

    public function test_update_hooks_consolidate_when_array_is_emptied()
    {
        Livewire::visit(new class extends Component {
            public $items = ['a', 'b', 'c'];
            public $hookLog = [];

            public function updatingItems($value, $key)
            {
                $this->hookLog[] = 'updating:' . ($key ?? 'items');
            }

            public function updatedItems($value, $key)
            {
                $this->hookLog[] = 'updated:' . ($key ?? 'items');
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <div dusk="items">{{ json_encode($items) }}</div>
                        <div dusk="hooks">{{ json_encode($hookLog) }}</div>

                        <button dusk="clear-items" type="button"
                            x-on:click="$wire.items = []; $wire.$commit()">
                            Clear Items
                        </button>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@items', '["a","b","c"]')
            ->waitForLivewire()->click('@clear-items')
            ->assertSeeIn('@items', '[]')
            // Should consolidate to 'items' since size changed to zero
            ->assertSeeIn('@hooks', 'updating:items')
            ->assertSeeIn('@hooks', 'updated:items');
    }
}
