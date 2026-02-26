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

                        <livewire:child :child-count="$count" />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $childCount;

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $childCount }}</span>
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

    public function test_reactive_property_triggers_updated_lifecycle_hook()
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

                public $updatedCount = 0;

                public function updatedCount($value)
                {
                    // This lifecycle hook should fire when reactive prop changes
                    $this->updatedCount = $value * 10;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <h1>Updated count (x10): <span dusk="child.updated-count">{{ $updatedCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)
            ->assertSeeIn('@child.updated-count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)
            ->assertSeeIn('@child.updated-count', 10)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)
            ->assertSeeIn('@child.updated-count', 20)
        ;
    }

    public function test_reactive_property_triggers_updating_lifecycle_hook()
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

                public $oldCount = 0;

                public function updatingCount($value)
                {
                    // Capture the old value before update
                    $this->oldCount = $this->count;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <h1>Old count: <span dusk="child.old-count">{{ $oldCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)
            ->assertSeeIn('@child.old-count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)
            ->assertSeeIn('@child.old-count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)
            ->assertSeeIn('@child.old-count', 1)
        ;
    }

    public function test_reactive_property_does_not_trigger_hooks_when_value_unchanged()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 5;
                public $other = 0;

                public function incOther() { $this->other++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent count: <span dusk="parent.count">{{ $count }}</span>
                        <h1>Parent other: <span dusk="parent.other">{{ $other }}</span>

                        <button wire:click="incOther" dusk="parent.inc-other">inc other</button>

                        <livewire:child :$count />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $count;

                public $hookCallCount = 0;

                public function updatedCount($value)
                {
                    // Count how many times hook is called
                    $this->hookCallCount++;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <h1>Hook call count: <span dusk="child.hook-calls">{{ $hookCallCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 5)
            ->assertSeeIn('@child.count', 5)
            ->assertSeeIn('@child.hook-calls', 0)

            // Trigger parent update that doesn't change $count
            ->waitForLivewire()->click('@parent.inc-other')
            ->assertSeeIn('@parent.other', 1)
            ->assertSeeIn('@child.count', 5)
            // Hook should NOT have been called since value didn't change
            ->assertSeeIn('@child.hook-calls', 0)

            ->waitForLivewire()->click('@parent.inc-other')
            ->assertSeeIn('@parent.other', 2)
            ->assertSeeIn('@child.count', 5)
            ->assertSeeIn('@child.hook-calls', 0)
        ;
    }

    public function test_reactive_property_does_not_trigger_hooks_when_same_value_passed_multiple_times()
    {
        Livewire::visit([
            new class extends Component {
                public $property = 1;
                public $other = 0;

                public function incOther() { $this->other++; }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Parent property: <span dusk="parent.property">{{ $property }}</span>
                        <h1>Parent other: <span dusk="parent.other">{{ $other }}</span>

                        <button wire:click="incOther" dusk="parent.inc-other">inc other</button>

                        <livewire:child :$property />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[BaseReactive]
                public $property;

                public $hookCallCount = 0;

                public function updatedProperty($value)
                {
                    // Count how many times hook is called
                    $this->hookCallCount++;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child property: <span dusk="child.property">{{ $property }}</span>
                        <h1>Hook call count: <span dusk="child.hook-calls">{{ $hookCallCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.property', 1)
            ->assertSeeIn('@child.property', 1)
            ->assertSeeIn('@child.hook-calls', 0)

            // Parent re-renders but property stays 1 -> 1, hook should NOT fire
            ->waitForLivewire()->click('@parent.inc-other')
            ->assertSeeIn('@parent.other', 1)
            ->assertSeeIn('@child.property', 1)
            ->assertSeeIn('@child.hook-calls', 0)

            // Another re-render, property still 1 -> 1, hook should NOT fire
            ->waitForLivewire()->click('@parent.inc-other')
            ->assertSeeIn('@parent.other', 2)
            ->assertSeeIn('@child.property', 1)
            ->assertSeeIn('@child.hook-calls', 0)
        ;
    }

    public function test_reactive_property_is_available_during_booted_lifecycle_hook()
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

                public $bootedCount = 0;

                public function booted()
                {
                    // This should see the updated reactive prop value
                    $this->bootedCount = $this->count;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <h1>Child count: <span dusk="child.count">{{ $count }}</span>
                        <h1>Booted count: <span dusk="child.booted-count">{{ $bootedCount }}</span>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent.count', 0)
            ->assertSeeIn('@child.count', 0)
            ->assertSeeIn('@child.booted-count', 0)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 1)
            ->assertSeeIn('@child.count', 1)
            ->assertSeeIn('@child.booted-count', 1)

            ->waitForLivewire()->click('@parent.inc')
            ->assertSeeIn('@parent.count', 2)
            ->assertSeeIn('@child.count', 2)
            ->assertSeeIn('@child.booted-count', 2)
        ;
    }

    public function test_rapid_model_live_updates_do_not_throw_cannot_mutate_reactive_prop_exception()
    {
        Livewire::visit([
            new class extends Component {
                public string $search = '';

                public array $facets = [
                    'brand' => [
                        'label' => 'Brand',
                        'values' => [
                            ['value' => 'Brembo', 'count' => 2],
                            ['value' => 'Bolt', 'count' => 3],
                        ],
                    ],
                    'query' => [
                        'label' => 'Query',
                        'values' => [
                            ['value' => '', 'count' => 1],
                        ],
                    ],
                ];

                public function updatedSearch(): void
                {
                    usleep(200 * 1000);

                    $this->facets['query']['values'][0]['value'] = $this->search;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <input type="text" wire:model.live.debounce.5ms="search" dusk="parent.search">

                        <livewire:child-facets :facets="$facets" />
                    </div>
                    HTML;
                }
            },
            'child-facets' => new class extends Component {
                #[BaseReactive]
                public array $facets = [];

                public function render() { return <<<'HTML'
                    <div>
                        <span dusk="child.query">{{ $facets['query']['values'][0]['value'] ?? '' }}</span>
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('#livewire-error')
            ->keys('@parent.search', 'b', 'o', 'l', 't', 'a', 'r')
            ->pause(1400)
            ->assertMissing('#livewire-error')
            ->assertSeeIn('@child.query', 'boltar')
        ;
    }

    public function test_overlapping_model_live_requests_send_fresh_parent_snapshots_when_reactive_children_are_bundled()
    {
        Livewire::visit([
            new class extends Component {
                public string $search = '';

                public int $count = 0;

                public function updatedSearch(): void
                {
                    usleep(250 * 1000);

                    $this->count++;
                }

                public function render() { return <<<'HTML'
                    <div>
                        <input type="text" wire:model.live.debounce.5ms="search" dusk="parent.search-order">

                        <span dusk="parent.count-order">{{ $count }}</span>

                        <livewire:child-facets-for-snapshot-order :facets="['query' => ['label' => 'Query', 'values' => [['value' => $search, 'count' => 1]]]]" />

                        @script
                        <script>
                            window.__livewireParentSnapshotCounts ??= []
                            window.__livewireRequestSendCount ??= 0

                            Livewire.interceptRequest(({ request, onSend }) => {
                                onSend(() => {
                                    window.__livewireRequestSendCount++

                                    request.messages.forEach((message) => {
                                        try {
                                            let snapshot = JSON.parse(message.snapshot)

                                            if (snapshot.data && Object.prototype.hasOwnProperty.call(snapshot.data, 'count')) {
                                                window.__livewireParentSnapshotCounts.push(snapshot.data.count)
                                            }
                                        } catch (error) {
                                            window.__livewireParentSnapshotCounts.push('parse-error')
                                        }
                                    })
                                })
                            })
                        </script>
                        @endscript
                    </div>
                    HTML;
                }
            },
            'child-facets-for-snapshot-order' => new class extends Component {
                #[BaseReactive]
                public array $facets = [];

                public function render() { return <<<'HTML'
                    <div>
                        <span dusk="child.query-order">{{ $facets['query']['values'][0]['value'] ?? '' }}</span>
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->keys('@parent.search-order', 'a')
            ->pause(25)
            ->keys('@parent.search-order', 'b')
            ->pause(1300)
            ->assertScript('window.__livewireRequestSendCount', 2)
            ->assertScript('JSON.stringify(window.__livewireParentSnapshotCounts)', '[0,1]')
            ->waitForTextIn('@parent.count-order', '2', 3)
            ->waitForTextIn('@child.query-order', 'ab', 3)
            ->assertSeeIn('@parent.count-order', '2')
            ->assertSeeIn('@child.query-order', 'ab')
            ->assertMissing('#livewire-error')
        ;
    }
}
