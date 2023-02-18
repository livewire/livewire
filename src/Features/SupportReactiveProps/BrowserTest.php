<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_pass_a_reactive_property_from_parent_to_child()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[Prop]
                public $count;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <button wire:click="inc" dusk="child.inc">inc</button>
                    </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@parent.count', 0)
        ->assertSeeIn('@child.count', 0)
        ->waitForLivewire()->click('@parent.inc')
        ->assertSeeIn('@parent.count', 1)
        ->assertSeeIn('@child.count', 1)
        ->waitForLivewire()->click('@child.inc')
        ->assertSeeIn('@parent.count', 1)
        ->assertSeeIn('@child.count', 1)
        ;
    }
}
