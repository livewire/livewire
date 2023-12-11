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
    function can_use_alpines_magic_model_with_wire_model()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button wire:click="increment" dusk="server-button">server increment</button>
                        <span dusk="server-output">{{ $count }}</span>

                        <div x-data="{ value: $model }" wire:model.live="count">
                            <button x-on:click="value++" dusk="browser-button">browser increment</button>
                            <span x-text="value" dusk="browser-output"></span>
                        </div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@server-output', '0')
            ->assertSeeIn('@browser-output', '0')
            ->waitForLivewire()->click('@browser-button')
            ->assertSeeIn('@server-output', '1')
            ->assertSeeIn('@browser-output', '1')
            ->waitForLivewire()->click('@server-button')
            ->assertSeeIn('@server-output', '2')
            ->assertSeeIn('@browser-output', '2')
        ;
    }
}
