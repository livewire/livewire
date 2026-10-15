<?php

namespace Livewire\Features\SupportWireDirty;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_dirty_cleans_up_when_its_element_is_removed()
    {
        Livewire::visit(new class extends Component {
            public $foo = '';
            public $showIndicator = true;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input wire:model.lazy="foo" dusk="foo">

                        @if ($showIndicator)
                            <span wire:dirty wire:target="foo" dusk="dirty">Dirty Indicator</span>
                        @endif

                        <button type="button" wire:click="$toggle('showIndicator')" dusk="toggle"></button>
                        <button type="button" dusk="dummy"></button>
                    </div>
                HTML;
            }
        })
            /**
             * Dirty indicator works while mounted.
             */
            ->assertMissing('@dirty')
            ->type('@foo', 'bar')
            ->assertVisible('@dirty')
            ->pause(150)
            ->waitForLivewire()->click('@dummy')
            ->pause(25)
            ->assertMissing('@dirty')

            /**
             * Tear down the wire:dirty element (should run directive cleanup)...
             */
            ->waitForLivewire()->click('@toggle')
            ->assertMissing('@dirty')

            /**
             * Further commits after teardown must not break the component...
             */
            ->type('@foo', 'baz')
            ->pause(150)
            ->waitForLivewire()->click('@dummy')
            ->pause(25)
            ->assertMissing('@dirty')

            /**
             * Re-mount the indicator and ensure dirty tracking still works.
             */
            ->waitForLivewire()->click('@toggle')
            ->assertMissing('@dirty')
            ->type('@foo', 'qux')
            ->assertVisible('@dirty') // fails without cleanup
            ->pause(150)
            ->waitForLivewire()->click('@dummy')
            ->pause(25)
            ->assertMissing('@dirty')
        ;
    }
}
