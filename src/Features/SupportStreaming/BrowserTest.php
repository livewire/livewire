<?php

namespace Livewire\Features\Supporttreaming;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    public function test_streaming()
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
