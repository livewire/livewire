<?php

namespace Livewire\Features\SupportWireIntersect;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_dwell_modifier_is_forwarded_to_alpine()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            public function track()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <p dusk="count">Count: {{ $count }}</p>

                    <div dusk="viewport" style="height: 200px; overflow-y: auto;">
                        <div style="height: 200px;"></div>
                        <div wire:intersect.once.parent.dwell.1000ms="track" style="height: 200px;">Target</div>
                    </div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', 'Count: 0')
        ->tap(fn ($browser) => $browser->script("document.querySelector('[dusk=viewport]').scrollTop = 200"))
        ->pause(200)
        ->assertSeeIn('@count', 'Count: 0')
        ->waitForText('Count: 1');
    }

    public function test_dwell_initializes_on_elements_added_during_a_morph()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            public $targetVisible = false;

            public function revealTarget()
            {
                $this->targetVisible = true;
            }

            public function track()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="revealTarget" dusk="show">Show target</button>
                    <p dusk="count">Count: {{ $count }}</p>

                    @if ($targetVisible)
                        <div dusk="viewport" style="height: 200px; overflow-y: auto;">
                            <div style="height: 200px;"></div>
                            <div wire:intersect.once.half.parent.dwell.300ms="track" style="height: 200px;">Target</div>
                        </div>
                    @endif
                </div>
                HTML;
            }
        })
        ->assertNotPresent('@viewport')
        ->waitForLivewire()->click('@show')
        ->assertPresent('@viewport')
        ->tap(fn ($browser) => $browser->script("document.querySelector('[dusk=viewport]').scrollTop = 110"))
        ->waitForText('Count: 1');
    }

    public function test_dwell_supports_renderless_actions()
    {
        Livewire::visit(new class extends Component {
            public $renders = 0;

            public function track()
            {
                $this->js('window.livewireIntersectTracked = true');
            }

            public function render()
            {
                $this->renders++;

                return <<<'HTML'
                <div x-init="window.livewireIntersectTracked = false">
                    <p dusk="renders">Renders: {{ $renders }}</p>

                    <div dusk="viewport" style="height: 200px; overflow-y: auto;">
                        <div style="height: 200px;"></div>
                        <div wire:intersect.once.half.parent.dwell.300ms.renderless="track" style="height: 200px;">Target</div>
                    </div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@renders', 'Renders: 1')
        ->tap(fn ($browser) => $browser->script("document.querySelector('[dusk=viewport]').scrollTop = 110"))
        ->waitUntil('window.livewireIntersectTracked === true')
        ->assertSeeIn('@renders', 'Renders: 1');
    }
}
