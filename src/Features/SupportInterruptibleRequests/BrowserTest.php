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
        $this->markTestSkipped();
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

    public function test_wire_model_interruptible_modifier()
    {
        $this->markTestSkipped();
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:search-component />
            </div>
            HTML; }
        }, 'search-component' => new class extends Component {
            public $search = '';

            public function render() { return <<<'HTML'
            <div>
                <input wire:model.live.interruptible="search" dusk="search" />
                <span dusk="value">{{ $search }}</span>
            </div>
            HTML; }
        }])
        ->waitForLivewire()
        ->type('@search', 'test')
        ->assertSeeIn('@value', 'test');
    }

    public function test_interruptible_requests_are_sent_immediately()
    {
        $this->markTestSkipped();
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
            <div>
                <livewire:immediate-search-component />
            </div>
            HTML; }
        }, 'immediate-search-component' => new class extends Component {
            public $search = '';
            public $searchResults = [];
            public $requestCount = 0;

            public function updatedSearch()
            {
                // Simulate a slow server response
                usleep(500000); // 500ms delay

                $this->requestCount++;
                $this->searchResults = ["Results for: {$this->search} (Request #{$this->requestCount})"];
            }

            public function render() { return <<<'HTML'
            <div>
                <input wire:model.live.interruptible="search" dusk="search" />
                <div dusk="results">
                    @foreach($searchResults as $result)
                        <div>{{ $result }}</div>
                    @endforeach
                </div>
                <div dusk="count">{{ $requestCount }}</div>
            </div>
            HTML; }
        }])
        ->waitForLivewire()
        ->type('@search', 'a')
        ->pause(100) // Wait briefly
        ->type('@search', 'ab')
        ->pause(100) // Wait briefly
        ->type('@search', 'abc')
        ->waitForLivewire(2000) // Wait for the final request to complete
        // We should only see the final result, not results for 'a' or 'ab'
        ->assertSeeIn('@results', 'Results for: abc')
        // We should see a request count of 1, meaning only the last request was processed
        ->assertSeeIn('@count', '1');
    }
}