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
    function ensures_that_the_input_correctly_receives_the_value_when_binding_with_the_alpine_directive()
    {
        Livewire::visit(new class extends Component {
            public string $input = '100';

            public function save() { }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk='input' wire:model="input" x-mask="***-9"/>

                        <div dusk="input.alpine" x-text="$wire.get('input')"></div>
                        <div dusk="input.blade">{{ $input }}</div>

                        <button wire:click="save" dusk="save">save</button>
                    </div>
                BLADE;
            }
        })
            ->click('@save')
            ->assertSeeIn('@input.blade', '100')

            ->type('@input', 'Hello World')
            ->assertDontSeeIn('@input.alpine', 'Hel-l') // it shouldn't be possible to insert a letter because of x-mask

            ->type('@input', 'abc-d')
            ->assertDontSeeIn('@input.alpine', 'abc-d') // it shouldn't be possible to insert a letter because of x-mask

            ->type('@input', 'xyz-1')
            ->assertSeeIn('@input.alpine', 'xyz-1')

            ->waitForLivewire()->click('@save')
            ->assertSeeIn('@input.blade', 'xyz-1')
        ;
    }
}
