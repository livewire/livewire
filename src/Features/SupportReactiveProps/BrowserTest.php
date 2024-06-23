<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_pass_a_reactive_property_from_parent_to_child()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function dec() { $this->count--; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="dec" dusk="parent.dec">dec</button>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
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

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)

            ->waitForLivewire()->click('@parent.dec')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)

            ->waitForLivewire()->click('@parent.dec')
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)

            ->waitForLivewire()->click('@parent.dec')
            ->assertSeeIn('@parent.count', -1)
            ->assertSeeIn('@child.count', -1)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0);
    }

    public function test_can_pass_a_reactive_property_from_parent_to_nested_children()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <h1 dusk="parent.count">{{ $count }}</h1>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public function render() { return <<<'HTML'
                    <div>
                        <h2>Child count: <h2 dusk="child.count">{{ $count }}</h2>

                        <livewire:nestedchild :$count />
                    </div>
                    HTML;
                }
            },
            'nestedchild' => new class extends Component {
                #[BaseReactive]
                public $count;

                public function render() { return <<<'HTML'
                    <div>
                        <h3>Nested child count: <h3 dusk="nested-child.count">{{ $count }}</h3>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)
            ->assertSeeIn('@nested-child.count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)
            ->assertSeeIn('@nested-child.count', 1)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)
            ->assertSeeIn('@nested-child.count', 2);
    }

    public function test_can_throw_exception_cannot_mutate_reactive_prop()
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
                #[BaseReactive]
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
        ->waitFor('#livewire-error')
        ->click('#livewire-error')
        ->assertSeeIn('@parent.count', 1)
        ->assertSeeIn('@child.count', 1);
    }

    public function test_can_pass_a_reactive_property_from_parent_to_lazy_child()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function inc() { $this->count++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>

                        <button wire:click="inc" dusk="parent.inc">inc</button>

                        <livewire:child :$count lazy />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
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
            ->waitFor('@child.count')
            ->assertSeeIn('@child.count', 0)
        ;
    }
}
