<?php

namespace Livewire\Features\SupportSession;

use Livewire\Attributes\Session;
use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    public function test_can_persist_a_property_to_the_session()
    {
        Livewire::visit(new class extends Component {
            #[Session]
            public $count = 0;

            public function increment()
            {
                $this->count++;
            }

            public function render() { return <<<'HTML'
            <div>
                <button dusk="button" wire:click="increment">+</button>
                <span dusk="count">{{ $count }}</span>
            </div>
            HTML; }
        })
            ->assertSeeIn('@count', '0')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@count', '1')
            ->refresh()
            ->assertSeeIn('@count', '1')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@count', '2')
            ;
    }
}
