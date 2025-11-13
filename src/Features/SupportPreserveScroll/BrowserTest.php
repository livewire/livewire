<?php

namespace Livewire\Features\SupportPreserveScroll;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_the_scroll_position_is_preserved_when_a_request_is_triggered()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div>
                    <button wire:click.preserve-scroll="$refresh" wire:island.prepend="foo" dusk="refresh">Refresh</button>

                    @island(name: 'foo', always: true)
                        @foreach(range(1, 100) as $i)
                            <div>{{ $i }}</div>
                        @endforeach
                    @endisland
                </div>
                HTML; }
            }
        ])
            ->waitForLivewireToLoad()

            // Assert we can see the refresh button which is above the island which will have data prepended...
            ->assertInViewPort('@refresh')

            ->waitForLivewire()->click('@refresh')

            // Assert we can't see the refresh button as it should have been pushed off the top of the screen if scroll was preserved...
            ->assertNotInViewPort('@refresh')
            ;
    }
}
