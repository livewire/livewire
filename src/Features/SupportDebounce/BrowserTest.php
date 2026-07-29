<?php

namespace Livewire\Features\SupportDebounce;

use Livewire\Component;
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
}