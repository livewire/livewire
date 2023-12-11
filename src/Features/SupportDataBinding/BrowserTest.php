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
    function can_update_bound_value_from_lifecyle_hook()
    {
        Livewire::visit(new class extends Component {
            public $foo = null;

            public $bar = null;

            public function updatedFoo(): void
            {
                $this->bar = null;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <select wire:model.live="foo" dusk="fooSelect">
                            <option value=""></option>
                            <option value="one">One</option>
                            <option value="two">Two</option>
                            <option value="three">Three</option>
                        </select>

                        <select wire:model="bar" dusk="barSelect">
                            <option value=""></option>
                            <option value="one">One</option>
                            <option value="two">Two</option>
                            <option value="three">Three</option>
                        </select>
                    </div>
                BLADE;
            }
        })
            ->select('@barSelect', 'one')
            ->select('@fooSelect', 'one')
            ->waitForLivewire()
            ->assertSelected('@barSelect', '')
        ;
    }
}
