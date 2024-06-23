<?php

namespace Livewire\Features\SupportEntangle;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_can_persist_entangled_data()
    {
        Livewire::visit(new class extends Component {
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

    public function test_is_not_live_by_default()
    {
        Livewire::visit(new class extends Component {
            public $foo = 'foo';

            function render()
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

    public function test_can_be_forced_to_not_be_live()
    {
        Livewire::visit(new class extends Component {
            public $foo = 'foo';

            function render()
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

    public function test_can_be_live()
    {
        Livewire::visit(new class extends Component {
            public $foo = 'foo';

            function render()
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

    public function test_can_remove_entangled_components_from_dom_without_side_effects()
    {
        Livewire::visit(new class extends Component {
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

            function render()
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

    public function test_can_remove_dollar_sign_entangled_components_from_dom_without_side_effects()
    {
        Livewire::visit(new class extends Component {
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

            function render()
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

    public function test_can_removed_nested_items_without_multiple_requests_when_entangled_items_are_present()
    {
        Livewire::visit(new class extends Component {
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

            function render()
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

    public function test_can_reorder_entangled_keys()
    {
        Livewire::visit(new class extends Component {
            public $test = [
                'one' => 'One',
                'two' => 'Two',
                'three' => 'Three',
            ];

            function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="output">Test: {{ json_encode($test) }}</div>

                    <div>
                        <button dusk="set" wire:click="$set('test', { one: 'One', three: 'Three', two: 'Two' })" type="button">
                            Set test to {"one":"One","three":"Three","two":"Two"}
                        </button>
                    </div>

                    <div>
                        <button dusk="set-add" wire:click="$set('test', { one: 'One', three: 'Three', two: 'Two', four: 'Four' })" type="button">
                            Set test to {"one":"One","three":"Three","two":"Two","four":"Four"}
                        </button>
                    </div>

                    <!-- This is meant to fail for now... We're only tackling key preservance for $wire.$set()...  -->
                    <!-- <div x-data="{ test: $wire.entangle('test', true) }">
                        <div>
                            <button dusk="set-alpine" x-on:click="test = { one: 'One', three: 'Three', two: 'Two' }" type="button">
                                Set test to {"one":"One","three":"Three","two":"Two"} with Alpine
                            </button>
                        </div>

                        <div>
                            <button dusk="set-add-alpine" x-on:click="test = { one: 'One', three: 'Three', two: 'Two', four: 'Four' }" type="button">
                                Set test to {"one":"One","three":"Three","two":"Two","four":"Four"} with Alpine
                            </button>
                        </div>
                    </div> -->
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@output', json_encode(['one' => 'One', 'two' => 'Two', 'three' => 'Three']))
        ->waitForLivewire()->click('@set')
        ->assertSeeIn('@output', json_encode(['one' => 'One', 'three' => 'Three', 'two' => 'Two']))
        ->waitForLivewire()->click('@set-add')
        ->assertSeeIn('@output', json_encode(['one' => 'One', 'three' => 'Three', 'two' => 'Two', 'four' => 'Four']))
        // This is meant to fail for now... We're only tackling key preservance for $wire.$set()...
        // ->waitForLivewire()->click('@set-alpine')
        // ->assertSeeIn('@output', json_encode(['one' => 'One', 'three' => 'Three', 'two' => 'Two']))
        // ->waitForLivewire()->click('@set-add-alpine')
        // ->assertSeeIn('@output', json_encode(['one' => 'One', 'three' => 'Three', 'two' => 'Two', 'four' => 'Four']));
        ;
    }
}
