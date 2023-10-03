<?php

namespace Livewire\Features\SupportDataBinding;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    function can_use_wire_dirty()
    {
        Livewire::visit(new class extends Component {
            public $prop = false;

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="checkbox" type="checkbox" wire:model="prop" value="true"  />

                        <div wire:dirty>Unsaved changes...</div>
                        <div wire:dirty.remove>The data is in-sync...</div>
                    </div>
                BLADE;
            }
        })
            ->assertSee('The data is in-sync...')
            ->check('@checkbox')
            ->assertDontSee('The data is in-sync')
            ->assertSee('Unsaved changes...')
            ->uncheck('@checkbox')
            ->assertSee('The data is in-sync...')
            ->assertDontSee('Unsaved changes...')
        ;
    }

    /** @test */
    function can_add_option_and_select()
    {
        Livewire::visit(new class extends Component {
            public int $customer = 2;

            public array $customers = [
                ['id' => 1, 'name' => 'Foo'],
                ['id' => 2, 'name' => 'Bar'],
                ['id' => 3, 'name' => 'FooBar'],
            ];

            public function addAndSelect()
            {
                $this->customers[] = ['id' => 4, 'name' => 'BarFoo'];
                $this->customer = 4;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <select dusk="customer" wire:model.live="customer">
                            @foreach ($this->customers as $customer)
                                <option value="{{ $customer['id'] }}" wire:key="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                            @endforeach
                        </select>
                        <button dusk="addAndSelect" wire:click="addAndSelect">Button</button>
                    </div>
                BLADE;
            }
        })
            ->assertSelected('@customer', '2')
            ->waitForLivewire()->click('@addAndSelect')
            ->assertSelectHasOption('@customer', '4')
            ->assertSelected('@customer', '4')
        ;
    }
}
