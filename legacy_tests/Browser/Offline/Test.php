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
                        <style>
                            .offline-flex {
                                display: flex;
                            }
                        </style>
                        <span wire:offline dusk="whileOffline">Offline</span>
                        <span wire:offline class="offline-flex" dusk="offlineFlex">Offline Flex</span>
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
             * plain wire:offline preserves the element's existing display value
             */
            ->online()
            ->assertMissing('@offlineFlex')
            ->offline()
            ->assertScript('window.getComputedStyle(document.querySelector(\'[dusk="offlineFlex"]\')).display', 'flex')
            ->online()
            ->assertMissing('@offlineFlex')

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
