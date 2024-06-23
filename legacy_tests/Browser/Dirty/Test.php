<?php

namespace LegacyTests\Browser\Dirty;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_wire_dirty()
    {
        Livewire::visit(new class extends Component {
            public $foo = '';
            public $bar = '';
            public $baz = '';
            public $bob = '';

            public function render()
            {
                return <<<HTML
                    <div>
                        <input wire:model.lazy="foo" wire:dirty.class="foo-dirty" dusk="foo">
                        <input wire:model.lazy="bar" wire:dirty.class.remove="bar-dirty" class="bar-dirty" dusk="bar">
                        <span wire:dirty.class="baz-dirty" wire:target="baz" dusk="baz.target"><input wire:model.lazy="baz" dusk="baz.input"></span>
                        <span wire:dirty wire:target="bob" dusk="bob.target">Dirty Indicator</span><input wire:model.lazy="bob" dusk="bob.input">

                        <button type="button" dusk="dummy"></button>
                    </div>
                HTML;
            }
        })
            /**
             * Add class for dirty data.
             */
            ->assertSourceMissing(' class="foo-dirty"')
            ->type('@foo', 'bar')
            ->assertSourceHas(' class="foo-dirty"')
            ->pause(150)
            ->waitForLivewire()->click('@dummy')
            ->assertSourceMissing(' class="foo-dirty"')

            /**
             * Remove class.
             */
            ->assertSourceHas(' class="bar-dirty"')
            ->type('@bar', 'baz')
            ->assertSourceMissing(' class="bar-dirty"')
            ->pause(150)
            ->waitForLivewire()->click('@dummy')
            ->pause(25)
            ->assertSourceHas(' class="bar-dirty"')

            /**
             * Set dirty using wire:target
             */
            ->assertSourceMissing(' class="baz-dirty"')
            ->type('@baz.input', 'baz')
            ->assertSourceHas(' class="baz-dirty"')
            ->pause(150)
            ->waitForLivewire()->click('@dummy')
            ->pause(25)
            ->assertSourceMissing(' class="baz-dirty"')

            /**
             * wire:dirty without modifiers, but with wire:target
             */
            ->assertMissing('@bob.target')
            ->type('@bob.input', 'baz')
            ->assertVisible('@bob.target')
            ->pause(150)
            ->waitForLivewire()->click('@dummy')
            ->pause(25)
            ->assertMissing('@bob.target')
        ;
    }
}
