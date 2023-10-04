<?php

namespace LegacyTests\Browser\Offline;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_wire_offline()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<<'HTML'
                    <div>
                        <span wire:offline dusk="whileOffline">Offline</span>
                        <span wire:offline.class="foo" dusk="addClass"></span>
                        <span class="hidden" wire:offline.class.remove="hidden" dusk="removeClass"></span>
                        <span wire:offline.attr="disabled" dusk="withAttribute"></span>
                        <span wire:offline.attr.remove="disabled" disabled="true" dusk="withoutAttribute"></span>
                    </div>
                HTML;
            }
        })
            ->assertMissing('@whileOffline')
            ->offline()
            ->assertSeeIn('@whileOffline', 'Offline')
            ->online()
            ->assertMissing('@whileOffline')

            /**
             * add element class while offline
             */
            ->online()
            ->assertClassMissing('@addClass', 'foo')
            ->offline()
            ->assertHasClass('@addClass', 'foo')

            /**
             * add element class while offline
             */
            ->online()
            ->assertHasClass('@removeClass', 'hidden')
            ->offline()
            ->assertClassMissing('@removeClass', 'hidden')

            /**
             * add element attribute while offline
             */
            ->online()
            ->assertAttributeMissing('@withAttribute', 'disabled')
            ->offline()
            ->assertAttribute('@withAttribute', 'disabled', 'true')

            /**
             * remove element attribute while offline
             */
            ->online()
            ->assertAttribute('@withoutAttribute', 'disabled', 'true')
            ->offline()
            ->assertAttributeMissing('@withoutAttribute', 'disabled')
        ;
    }
}
