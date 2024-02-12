<?php

namespace Livewire\Features\SupportReorderInputs;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Computed;

class BrowserTest extends BrowserTestCase
{

    /** @test */
    function can_reorder_inputs_and_bind_data_unique()
    {
        Livewire::visit(new class extends Component {
            public $items;

            public function mount() {
                $this->items = [
                    1 => ['id' => 1,  'value' => 'first'],
                    2 => ['id' => 2,  'value' => 'second'],
                ];
            }

            public function swap() {
                $this->items = [
                    2 => ['id' => 2,  'value' => 'second'],
                    1 => ['id' => 1,  'value' => 'first'],
                ];
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <ul>
                            @foreach($items as $index => $item)
                            <li wire:key="key-{{ $item['id'] }}">
                                <input dusk="input{{ $item['id'] }}" type="text" wire:model.live="items.{{ $index }}.value">
                            </li>
                            @endforeach
                        </ul>

                        <button type='Button' dusk='button' wire:click='swap'>Swap</button>

                    </div>
                BLADE;
            }
        })
            // Check data has been bound correctly
            ->assertInputValue('@input1', 'first')
            ->assertInputValue('@input2', 'second')
            ->waitForLivewire()->type('@input1', 'first updated')

            ->assertInputValue('@input2', 'second')
            // Swap the data array around
            ->waitForLivewire()->click("@button")

            // Check initial bound data is correct
            ->assertInputValue('@input1', 'first')
            ->assertInputValue('@input2', 'second')
            // Update one input
            ->waitForLivewire()->type('@input1', 'first updated')

            // Check second input has not changed value too
            ->assertInputValue('@input2', 'second')
        ;
    }

}
