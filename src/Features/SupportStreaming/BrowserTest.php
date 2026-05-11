<?php

namespace Livewire\Features\SupportStreaming;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    public function test_can_stream_falsy_values()
    {
        Livewire::visit([new class extends Component {
            public function begin()
            {
                for ($i = 0; $i < 5; $i++) {
                    $this->stream(to: 'stream', content: $i);
                }
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="begin" dusk="button">Start</button>
                    <div wire:ignore>
                        <span wire:stream="stream" dusk="output"></span>
                    </div>
                </div>
                HTML;
            }
        }])
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@output', '01234')
        ;
    }

    public function test_can_stream()
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
        ;
    }

}
