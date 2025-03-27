<?php

namespace Livewire\Features\SupportInterruptibleRequests;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Interruptible;

class BrowserTest extends BrowserTestCase
{
    public function test_components_can_be_marked_as_interruptible()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:child />
                <button wire:click="\$dispatch('trigger')" dusk="trigger">Dispatch trigger</button>
            </div>
            HTML; }
        }, 'child' => new #[Interruptible] class extends Component {
            public $search = '';

            public function render() { return <<<'HTML'
            <div>
                <input wire:model.live="search" dusk="search" />
                <span dusk="value">{{ $search }}</span>
            </div>
            HTML; }
        }])
        ->waitForLivewire()
        ->type('@search', 'test')
        ->assertSeeIn('@value', 'test');
    }
}