<?php

namespace LegacyTests\Browser\Polling;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_wire_poll()
    {
        Livewire::visit(new class extends Component {
            public $enabled = false;
            public $count = 0;

            public function render()
            {
                $this->count++;

                return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="refresh">count++</button>
                        <button wire:click="$set('enabled', true)" dusk="enable">enable</button>
                        <button wire:click="$set('enabled', false)" dusk="disable">disable</button>

                        <span dusk="output">{{ $count }}</span>

                        @if ($enabled) <div wire:poll.500ms></div> @endif
                    </div>
                HTML;
            }
        })
            /**
             * Enable polling by adding a wire:poll directive to an element.
             */
            ->assertSeeIn('@output', '1')
            ->pause('500') // Wait the time for a wire:poll in the view.
            ->assertSeeIn('@output', '1')
            ->waitForLivewire()->click('@enable')
            ->assertSeeIn('@output', '2')
            ->waitForLivewire(function () {}) // Wait for the next Livewire roundtrip
            ->assertSeeIn('@output', '3')
            ->waitForLivewire(function () {})
            ->assertSeeIn('@output', '4')

            /**
             * Disable polling by removing wire:poll from an element.
             */
            ->waitForLivewire()->click('@disable')
            ->assertSeeIn('@output', '5')
            ->pause('500')
            ->assertSeeIn('@output', '5')

            /**
             * Re-enable polling, then test that polling stops when offline and resumes when back online.
             */
            ->waitForLivewire()->click('@enable')
            ->assertSeeIn('@output', '6')
            ->waitForLivewire(function () {})
            ->assertSeeIn('@output', '7')
            ->offline()
            ->pause('500')
            ->assertSeeIn('@output', '7')
            ->online()
            ->waitForLivewire(function () {})
            ->assertSeeIn('@output', '8')
        ;
    }
}
