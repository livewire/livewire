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
    public function can_remove_dollar_sign_entangled_components_from_dom_without_side_effects()
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


    /**
     * @test
     * @todo Remove test before merging the other failing test
     */
    public function it_can_remove_nested_wired_values_one_by_one()
    {
        Livewire::visit(new class extends Component {
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
                ]
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
        Livewire::visit(new class extends Component {
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
                ]
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
}
