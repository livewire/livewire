<?php

namespace Livewire\Features\SupportEntangle;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_persist_entangled_data()
    {
        Livewire::visit(new class extends Component
        {
            public $input;

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <div x-data="{ value: $persist(@entangle('input')) }">
                            <input dusk="input" x-model="value" />
                        </div>
                    </div>
                BLADE;
            }
        })
            ->type('@input', 'Hello World')
            ->assertScript('localStorage.getItem("_x_value") == \'"Hello World"\'')
            ->tap(fn ($b) => $b->refresh())
            ->assertScript("localStorage.getItem('_x_value')", '"Hello World"')
        ;
    }

    /** @test */
    public function is_not_live_by_default()
    {
        Livewire::visit(new class extends Component
        {
            public $foo = 'foo';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div x-data="{ state: $wire.$entangle('foo') }">
                        <button dusk="set" x-on:click="state = 'bar'" type="button">
                            Set to bar
                        </button>
                    </div>

                    <div dusk="state">{{ $foo }}</div>

                    <button dusk="refresh" x-on:click="$wire.$refresh()" type="button">
                        Refresh
                    </button>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@state', 'foo')
            ->waitForNoLivewire()->click('@set')
            ->assertSeeIn('@state', 'foo')
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@state', 'bar');
    }

    /** @test */
    public function can_be_forced_to_not_be_live()
    {
        Livewire::visit(new class extends Component
        {
            public $foo = 'foo';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div x-data="{ state: $wire.$entangle('foo', false) }">
                        <button dusk="set" x-on:click="state = 'bar'" type="button">
                            Set to bar
                        </button>
                    </div>

                    <div dusk="state">{{ $foo }}</div>

                    <button dusk="refresh" x-on:click="$wire.$refresh()" type="button">
                        Refresh
                    </button>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@state', 'foo')
            ->waitForNoLivewire()->click('@set')
            ->assertSeeIn('@state', 'foo')
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@state', 'bar');
    }

    /** @test */
    public function can_be_live()
    {
        Livewire::visit(new class extends Component
        {
            public $foo = 'foo';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div x-data="{ state: $wire.$entangle('foo', true) }">
                        <button dusk="set" x-on:click="state = 'bar'" type="button">
                            Set to bar
                        </button>
                    </div>

                    <div dusk="state">{{ $foo }}</div>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@state', 'foo')
            ->waitForLivewire()->click('@set')
            ->assertSeeIn('@state', 'bar');
    }

    /** @test */
    public function can_remove_entangled_components_from_dom_without_side_effects()
    {
        Livewire::visit(new class extends Component
        {
            public $items = [];

            public function add()
            {
                $this->items[] = [
                    'value' => null,
                ];
            }

            public function remove($key)
            {
                unset($this->items[$key]);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <ul>
                        @foreach ($items as $itemKey => $item)
                            <li dusk="item{{ $itemKey }}" wire:key="{{ $itemKey }}">
                                <div x-data="{ value: $wire.entangle('items.{{ $itemKey }}.value') }"></div>

                                {{ $itemKey }}

                                <button type="button" dusk="remove{{ $itemKey }}" wire:click="remove('{{ $itemKey }}')">
                                    Remove
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <button dusk="add" type="button" wire:click="add">
                        Add
                    </button>

                    <div dusk="json">
                        {{ json_encode($items) }}
                    </div>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@json', '[]')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@item0', '0')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@item1', '1')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@item2', '2')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@item3', '3')
            ->waitForLivewire()->click('@remove3')
            ->assertPresent('@item0')
            ->assertPresent('@item1')
            ->assertPresent('@item2')
            ->assertMissing('@item3')
            ->waitForLivewire()->click('@remove2')
            ->assertPresent('@item0')
            ->assertPresent('@item1')
            ->assertMissing('@item2')
            ->assertMissing('@item3')
            ->waitForLivewire()->click('@remove1')
            ->assertPresent('@item0')
            ->assertMissing('@item1')
            ->assertMissing('@item2')
            ->assertMissing('@item3')
            ->waitForLivewire()->click('@remove0')
            ->assertMissing('@item0')
            ->assertMissing('@item1')
            ->assertMissing('@item2')
            ->assertMissing('@item3');
    }

    /** @test */
    public function can_remove_dollar_sign_entangled_components_from_dom_without_side_effects()
    {
        Livewire::visit(new class extends Component
        {
            public $items = [];

            public function add()
            {
                $this->items[] = [
                    'value' => null,
                ];
            }

            public function remove($key)
            {
                unset($this->items[$key]);
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <ul>
                        @foreach ($items as $itemKey => $item)
                            <li dusk="item{{ $itemKey }}" wire:key="{{ $itemKey }}">
                                <div x-data="{ value: $wire.$entangle('items.{{ $itemKey }}.value') }"></div>

                                {{ $itemKey }}

                                <button type="button" dusk="remove{{ $itemKey }}" wire:click="remove('{{ $itemKey }}')">
                                    Remove
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <button dusk="add" type="button" wire:click="add">
                        Add
                    </button>

                    <div dusk="json">
                        {{ json_encode($items) }}
                    </div>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@json', '[]')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@item0', '0')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@item1', '1')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@item2', '2')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@item3', '3')
            ->waitForLivewire()->click('@remove3')
            ->assertPresent('@item0')
            ->assertPresent('@item1')
            ->assertPresent('@item2')
            ->assertMissing('@item3')
            ->waitForLivewire()->click('@remove2')
            ->assertPresent('@item0')
            ->assertPresent('@item1')
            ->assertMissing('@item2')
            ->assertMissing('@item3')
            ->waitForLivewire()->click('@remove1')
            ->assertPresent('@item0')
            ->assertMissing('@item1')
            ->assertMissing('@item2')
            ->assertMissing('@item3')
            ->waitForLivewire()->click('@remove0')
            ->assertMissing('@item0')
            ->assertMissing('@item1')
            ->assertMissing('@item2')
            ->assertMissing('@item3');
    }

    /** @test */
    public function can_removed_nested_items_without_multiple_requests_when_entangled_items_are_present()
    {
        Livewire::visit(new class extends Component
        {
            public $components = [];

            public $filters = [
                'name' => 'bob',
                'phone' => '123',
                'address' => 'street',
            ];

            public $counter = 0;

            public function boot()
            {
                $this->counter++;
            }

            public function removeFilter($filter)
            {
                $this->filters[$filter] = null;
            }

            public function addBackFiltersWithEntangled()
            {
                $this->filters = [
                    'name' => 'bob',
                    'phone' => '123',
                    'address' => 'street',
                    'entangled' => 'hello world', // This filter will be entangled,
                ];
            }

            public function addBackFiltersWithoutEntangled()
            {
                // Add back the same non entangled filters to show that
                // removing/adding non entangled items is not the issue.
                $this->filters = [
                    'name' => 'bob',
                    'phone' => '123',
                    'address' => 'street',
                ];
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div>Page</div>

                    <div dusk="counter">Boot counter: {{ $counter }}</div>

                    @foreach ($filters as $filter => $value)
                        @if ($filter === 'entangled')
                            <div x-data="{ value: $wire.entangle('filters.{{ $filter }}') }">
                                <span
                                    x-text="'Entangled: ' + value"
                                ></span>
                            </div>
                            <div>
                                <button dusk="remove-{{ $filter }}" wire:click="removeFilter('{{ $filter }}')">Remove {{ $filter }}</button>
                            </div>
                        @else
                            <div>
                                Normal: {{ $value }}
                            </div>
                            <div>
                                <button dusk="remove-{{ $filter }}" wire:click="removeFilter('{{ $filter }}')">Remove {{ $filter }}</button>
                            </div>
                        @endif
                    @endforeach

                    <button dusk="add-entangled-filter" wire:click="addEntangledFilter()">Add entangled filter</button>
                    <button dusk="add-back-filters-without-entangled" wire:click="addBackFiltersWithoutEntangled()">Add back filters without entangled</button>
                    <button dusk="add-back-filters-with-entangled" wire:click="addBackFiltersWithEntangled()">Add back filters with entangled</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@counter', '1')
        ->waitForLivewire()->click('@remove-name')
        ->assertSeeIn('@counter', '2')
        ->waitForLivewire()->click('@remove-phone')
        ->assertSeeIn('@counter', '3')
        ->waitForLivewire()->click('@remove-address')
        ->assertSeeIn('@counter', '4')
        ->waitForLivewire()->click('@add-back-filters-without-entangled')
        ->assertSeeIn('@counter', '5')
        ->waitForLivewire()->click('@remove-name')
        ->assertSeeIn('@counter', '6')
        ->waitForLivewire()->click('@remove-phone')
        ->assertSeeIn('@counter', '7')
        ->waitForLivewire()->click('@remove-address')
        ->assertSeeIn('@counter', '8')
        ->waitForLivewire()->click('@add-back-filters-with-entangled')
        ->assertSeeIn('@counter', '9')
        ->waitForLivewire()->click('@remove-name')
        ->assertSeeIn('@counter', '10') // This test will fail here since there will be duplicate requests
        ->waitForLivewire()->click('@remove-phone')
        ->assertSeeIn('@counter', '11')
        ->waitForLivewire()->click('@remove-address')
        ->assertSeeIn('@counter', '12')
        ->waitForLivewire()->click('@remove-entangled')
        ->assertSeeIn('@counter', '13');
    }

    /**
     * @test
     *
     * @todo Remove test before merging the other failing test
     */
    public function it_can_remove_nested_wired_values_one_by_one()
    {
        Livewire::visit(new class extends Component
        {
            public array $items = [
                [
                    'id' => 1,
                    'value' => 5_000,
                ],
                [
                    'id' => 2,
                    'value' => 10_000,
                ],
                [
                    'id' => 3,
                    'value' => 15_000,
                ],
            ];

            public function remove($key)
            {
                // Removing an item from array
                unset($this->items[$key]);

                // Resetting the array keys
                $this->items = array_values($this->items);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        @foreach($items as $itemKey => $item)
                            <div wire:key="item-{{ $item['id'] }}">
                                <input
                                    type="text"
                                    wire:model="items.{{ $itemKey }}.value"
                                >
                                <button
                                    type="button"
                                    wire:click.prevent="remove({{ $itemKey }})"
                                    dusk="remove{{ $itemKey }}"
                                >
                                    Remove
                                </button>
                            </div>
                        @endforeach

                        <div dusk="json">
                            {{ json_encode($items) }}
                        </div>
                    </div>
                HTML;
            }
        })
            ->waitForLivewire()->click('@remove2')
            ->waitForLivewire()->click('@remove1')
            ->waitForLivewire()->click('@remove0')
            ->assertSeeIn('@json', '[]');
    }

    /** @test */
    public function it_can_remove_nested_engangled_values_one_by_one()
    {
        Livewire::visit(new class extends Component
        {
            public array $items = [
                [
                    'id' => 1,
                    'value' => 5_000,
                ],
                [
                    'id' => 2,
                    'value' => 10_000,
                ],
                [
                    'id' => 3,
                    'value' => 15_000,
                ],
            ];

            public function remove($key)
            {
                // Removing an item from array
                unset($this->items[$key]);

                // Resetting the array keys
                $this->items = array_values($this->items);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        @foreach($items as $itemKey => $item)
                            <div wire:key="item-{{ $item['id'] }}">
                                <input
                                    type="text"
                                    x-data="{ value: @entangle('items.'.$itemKey.'.value') }"
                                    x-bind:value="value"
                                >
                                <button
                                    type="button"
                                    wire:click.prevent="remove({{ $itemKey }})"
                                    dusk="remove{{ $itemKey }}"
                                >
                                    Remove
                                </button>
                            </div>
                        @endforeach

                        <div dusk="json">
                            {{ json_encode($items) }}
                        </div>
                    </div>
                HTML;
            }
        })
            ->waitForLivewire()->click('@remove2')
            ->waitForLivewire()->click('@remove1')
            ->waitForLivewire()->click('@remove0')
            ->assertSeeIn('@json', '[]');
    }

    /** @test */
    public function it_supports_entangling_within_an_alpine_data()
    {
        Livewire::visit(new class extends Component
        {
            public $count = 0;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <div x-data="test()">
                            <div>{{ $count }}</div>
                            <button x-on:click="increment" dusk="increment">Increase</button>
                            <div x-text="count" dusk="alpine-output"></div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Alpine.data('test', () => ({
                                    count: @entangle('count').live,
                                    increment() {
                                        this.count++
                                    },
                                }));
                            });
                        </script>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@alpine-output', '0')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@alpine-output', '1')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@alpine-output', '2')
            ;
    }
}
