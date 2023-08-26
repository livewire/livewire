<?php

namespace Livewire\Features\SupportEntangle;

use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_remove_entangled_components_from_dom_without_side_effects()
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

    /** @test */
    public function can_removed_nested_items_without_multiple_requests_when_entangled_items_are_present()
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
        ->waitForLivewire()->click('@remove-email')
        ->assertSeeIn('@counter', '13');
    }
}
