<?php

namespace Livewire\Features\SupportDebounce;

use Livewire\Component;
use Livewire\Features\SupportQueryString\BaseUrl;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    function test_debounce_attribute_delays_live_model_network_update()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(500)]
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live="search" dusk="input" />
                    <span dusk="output">{{ $search }}</span>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@output', '')
            ->type('@input', 'hello')
            ->pause(150)
            ->assertSeeIn('@output', '')
            ->waitForTextIn('@output', 'hello', 1)
        ;
    }

    function test_blade_debounce_modifier_overrides_attribute()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(2000)]
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live.debounce.100ms="search" dusk="input" />
                    <span dusk="output">{{ $search }}</span>
                </div>
                HTML;
            }
        })
            ->type('@input', 'fast')
            // Modifier 100ms must win over attribute 2000ms
            ->waitForTextIn('@output', 'fast', 1)
        ;
    }

    function test_debounce_attribute_does_not_force_live_on_deferred_model()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce]
            public $search = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model="search" dusk="input" />
                    <span dusk="output">{{ $search }}</span>
                    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->type('@input', 'deferred')
            ->pause(200)
            ->assertSeeIn('@output', '')
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@output', 'deferred')
        ;
    }

    function test_only_annotated_property_uses_attribute_debounce()
    {
        Livewire::visit(new class extends Component {
            #[BaseDebounce(800)]
            public $slow = '';

            public $fast = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live="slow" dusk="slow" />
                    <input type="text" wire:model.live="fast" dusk="fast" />
                    <span dusk="slow-out">{{ $slow }}</span>
                    <span dusk="fast-out">{{ $fast }}</span>
                </div>
                HTML;
            }
        })
            ->type('@fast', 'a')
            ->waitForTextIn('@fast-out', 'a', 1)
            ->assertSeeIn('@slow-out', '')
            ->type('@slow', 'b')
            ->pause(200)
            ->assertSeeIn('@slow-out', '')
            ->waitForTextIn('@slow-out', 'b', 1)
        ;
    }

    public function test_debounce_attribute_works_on_multiple_back_forward_navigations()
    {
        Livewire::visit(new class extends Component {
            #[BaseUrl(history: true)]
            public $page = '1';

            #[BaseDebounce(500)]
            public $search = '';

            public function setPage($value)
            {
                $this->page = $value;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input type="text" wire:model.live="search" dusk="input" />
                        <button dusk="page2" wire:click="setPage('2')">Page 2</button>
                        <button dusk="page3" wire:click="setPage('3')">Page 3</button>
                        <button dusk="page4" wire:click="setPage('4')">Page 4</button>
                        <span dusk="output">{{ $search }}</span>
                        <span dusk="page">{{ $page }}</span>
                    </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@output', '')
            ->assertSeeIn('@page', '1')

            // Initial debounce
            ->type('@input', 'one')
            ->pause(150)
            ->assertSeeIn('@output', '')
            ->waitForTextIn('@output', 'one', 1)

            // Page 2 — search is still "one"; type a new value
            ->waitForLivewire()->click('@page2')
            ->assertSeeIn('@page', '2')
            ->assertSeeIn('@output', 'one')
            ->type('@input', 'two')
            ->pause(150)
            ->assertSeeIn('@output', 'one')
            ->waitForTextIn('@output', 'two', 1)

            // Page 3
            ->waitForLivewire()->click('@page3')
            ->assertSeeIn('@page', '3')
            ->assertSeeIn('@output', 'two')
            ->type('@input', 'three')
            ->pause(150)
            ->assertSeeIn('@output', 'two')
            ->waitForTextIn('@output', 'three', 1)

            // Page 4
            ->waitForLivewire()->click('@page4')
            ->assertSeeIn('@page', '4')
            ->assertSeeIn('@output', 'three')
            ->type('@input', 'four')
            ->pause(150)
            ->assertSeeIn('@output', 'three')
            ->waitForTextIn('@output', 'four', 1)

            // Back → page 3 (history: true required)
            ->back()
            ->pause(100)
            ->assertSeeIn('@page', '3')
            ->type('@input', 'back-three')
            ->pause(150)
            ->assertDontSeeIn('@output', 'back-three')
            ->waitForTextIn('@output', 'back-three', 1)

            // Back → page 2
            ->back()
            ->pause(100)
            ->assertSeeIn('@page', '2')
            ->type('@input', 'back-two')
            ->pause(150)
            ->assertDontSeeIn('@output', 'back-two')
            ->waitForTextIn('@output', 'back-two', 1)

            // Forward → page 3
            ->forward()
            ->pause(100)
            ->assertSeeIn('@page', '3')
            ->type('@input', 'fwd-three')
            ->pause(150)
            ->assertDontSeeIn('@output', 'fwd-three')
            ->waitForTextIn('@output', 'fwd-three', 1)

            // Forward → page 4
            ->forward()
            ->pause(100)
            ->assertSeeIn('@page', '4')
            ->type('@input', 'fwd-four')
            ->pause(150)
            ->assertDontSeeIn('@output', 'fwd-four')
            ->waitForTextIn('@output', 'fwd-four', 1)

            // Back → page 3 again
            ->back()
            ->pause(100)
            ->assertSeeIn('@page', '3')
            ->type('@input', 'final')
            ->pause(150)
            ->assertDontSeeIn('@output', 'final')
            ->waitForTextIn('@output', 'final', 1)
        ;
    }
}