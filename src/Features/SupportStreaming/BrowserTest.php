<?php

namespace Livewire\Features\SupportStreaming;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    public function test_streaming_completes_without_header_modification_errors()
    {
        Livewire::visit([new class extends Component {
            public $start = 2;

            public function begin()
            {
                while ($this->start > 0) {
                    $this->start = $this->start - 1;

                    $this->stream(to: 'count', content: $this->start, replace: true);
                };
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="begin" dusk="button">Start count-down</button>
                    <h1>Count: <span wire:stream="count">{{ $start }}</span></h1>
                </div>
                HTML;
            }
        }])
        ->waitForLivewire()->click('@button')
        ->assertSee("0")
        ->assertConsoleLogMissingWarning('headers already sent')
        ;
    }

    public function test_final_response_contains_component_snapshot_after_streaming()
    {
        Livewire::visit([new class extends Component {
            public $count = 3;

            public function streamContent()
            {
                $this->stream(to: 'output', content: 'Streaming...', replace: true);
                $this->count = 0;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="streamContent" dusk="stream-button">Stream</button>
                    <div wire:stream="output">Initial</div>
                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML;
            }
        }])
        ->waitForLivewire()->click('@stream-button')
        ->assertSee('Streaming...')
        ->assertSee('0')
        ;
    }

    public function test_non_streaming_request_still_works()
    {
        Livewire::visit([new class extends Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="increment" dusk="increment">Increment</button>
                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML;
            }
        }])
        ->waitForLivewire()->click('@increment')
        ->assertSee('1')
        ->waitForLivewire()->click('@increment')
        ->assertSee('2')
        ;
    }

    public function test_multiple_stream_calls()
    {
        Livewire::visit([new class extends Component {
            public $items = [];

            public function addItems()
            {
                for ($i = 1; $i <= 5; $i++) {
                    $this->items[] = "Item $i";
                    $this->stream(
                        to: 'items',
                        content: implode(', ', $this->items),
                        replace: true
                    );
                }
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="addItems" dusk="add-items">Add Items</button>
                    <div wire:stream="items" dusk="items">No items</div>
                </div>
                HTML;
            }
        }])
        ->waitForLivewire()->click('@add-items')
        ->assertSee('Item 1, Item 2, Item 3, Item 4, Item 5')
        ;
    }

}
