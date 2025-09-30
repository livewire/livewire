<?php

namespace Livewire\Features\SupportIslands;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Attributes\Computed;

class BrowserTest extends BrowserTestCase
{
    public function test_render_island_directives()
    {
        Livewire::visit([new class extends \Livewire\Component {
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island
                        <div dusk="island">
                            Count: <span dusk="island-count">{{ $count }}</span>

                            <button type="button" wire:click="increment" dusk="island-increment">Increment</button>
                        </div>
                    @endisland

                    <div dusk="root">
                        Root count: <span dusk="root-count">{{ $count }}</span>

                        <button type="button" wire:click="increment" dusk="root-increment">Increment root</button>
                    </div>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@island-count', '0')
            ->assertSeeIn('@root-count', '0')
            ->waitForLivewire()->click('@island-increment')
            ->assertSeeIn('@island-count', '1')
            ->assertSeeIn('@root-count', '0')
            ->waitForLivewire()->click('@root-increment')
            ->assertSeeIn('@island-count', '1')
            ->assertSeeIn('@root-count', '2')
            ;
    }
}
