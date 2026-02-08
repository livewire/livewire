<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

/** @group morphing */
class AlpineMorphingBrowserTest extends \Tests\BrowserTestCase
{
    public function test_component_with_custom_directive_keeps_state_after_cloning()
    {
        Livewire::visit(new class extends Component {
            public int $counter = 0;

            function render() {
                return <<<'HTML'
                <div>
                    <div x-counter wire:model.live='counter'>
                        <span dusk='counter' x-text="__counter"></span>
                        <button x-counter:increment dusk='increment'>+</button>
                    </div>

                    <script>
                        document.addEventListener('alpine:init', () => {
                            Alpine.directive('counter', function (el, { value }) {
                                if (value === 'increment') {
                                    Alpine.bind(el, {
                                        'x-on:click.prevent'() {
                                            this.$data.__counter++;
                                        }
                                    })
                                } else if (! value) {
                                    Alpine.bind(el, {
                                        'x-modelable': '__counter',
                                        'x-data'() {
                                            return {
                                                __counter: 0
                                            }
                                        }
                                    })
                                }
                            })
                        })
                    </script>
                </div>
                HTML;
            }
        })
            ->waitForLivewire()->click('@increment')
            ->assertInputValue('@counter', '1')
        ;
    }

    public function test_deep_alpine_state_is_preserved_when_morphing_with_uninitialized_livewire_html()
    {
        Livewire::visit(new class extends Component {
            function render() {
                return <<<'HTML'
                <div>
                    <div x-data="{ showCounter: false }">
                        <button @click="showCounter = true" dusk="button">show</button>

                        <template x-if="showCounter">
                            <div x-data="{ count: 0 }">
                                <button x-on:click="count++" dusk="increment">+</button>

                                <h1 x-text="count" dusk="count"></h1>
                            </div>
                        </template>
                    </div>

                    <button wire:click="$commit" dusk="refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->assertMissing('@count')
            ->click('@button')
            ->assertVisible('@count')
            ->assertSeeIn('@count', '0')
            ->click('@increment')
            ->assertSeeIn('@count', '1')
            ->waitForLivewire()->click('@refresh')
            ->assertVisible('@count')
            ->assertSeeIn('@count', '1');
        ;
    }

    public function test_alpine_property_persists_on_array_item_reorder()
    {
        return Livewire::visit(new class extends Component {
            public array $items = [
                ['id' => 1, 'title' => 'foo', 'complete' => false],
                ['id' => 2, 'title' => 'bar', 'complete' => false],
                ['id' => 3, 'title' => 'baz', 'complete' => false]
            ];

            public function getItems()
            {
                return $this->items;
            }

            public function complete(int $index): void
            {
                $this->items[$index]['complete'] = true;
            }

            function render() {
                return <<<'HTML'
                    <div>
                        @foreach (collect($this->getItems())->sortBy('complete')->toArray() as $index => $item)
                            <div wire:key="{{$item['id']}}" x-data="{show: false}">
                                <div>{{ $item['title'] }} (completed: @json($item['complete']))</div>

                                <div x-show="show" x-cloak dusk="hidden">
                                    (I shouldn't be visible): {{ $item['title'] }}
                                </div>

                                <button dusk="complete-{{ $index }}" wire:click="complete({{ $index }})">complete</button>
                            </div>
                        @endforeach
                    </div>
                HTML;
            }
        })
            // Click on the top two items and mark them as complete.
            ->click('@complete-0')
            ->pause(500)
            ->click('@complete-1')
            ->pause(500)

            // Error thrown in console, and Alpine fails and shows the hidden text when it should not.
            ->assertMissing('@hidden');
            // ->assertConsoleLogMissingWarning('show is not defined');
    }

    public function test_removing_array_item_does_not_trigger_effect_error_for_removed_element()
    {
        // This tests the race condition where deleting an array item would cause
        // effects to fire on elements referencing now-invalid array indices
        // BEFORE the DOM morph cleanup removes those elements.
        // The key issue: when we remove the last item, elements with x-text="$wire.items[2]..."
        // would error because items[2] no longer exists, but the effect fires before cleanup.
        Livewire::visit(new class extends Component {
            public array $items = [
                ['id' => 1, 'name' => 'Item 1', 'expanded' => false],
                ['id' => 2, 'name' => 'Item 2', 'expanded' => true],
                ['id' => 3, 'name' => 'Item 3', 'expanded' => false],
            ];

            public function removeLastItem()
            {
                array_pop($this->items);
            }

            public function toggleItem($id)
            {
                foreach ($this->items as &$item) {
                    if ($item['id'] === $id) {
                        $item['expanded'] = !$item['expanded'];
                    }
                }
            }

            function render() {
                return <<<'HTML'
                    <div>
                        @foreach ($items as $idx => $item)
                            <div wire:key="item-{{ $item['id'] }}">
                                <span dusk="item-{{ $item['id'] }}-name">{{ $item['name'] }}</span>
                                <button
                                    dusk="item-{{ $item['id'] }}-status"
                                    x-text="$wire.items[{{ $idx }}].expanded ? 'ON' : 'OFF'"
                                ></button>
                                <button dusk="toggle-{{ $item['id'] }}" wire:click="toggleItem({{ $item['id'] }})">Toggle</button>
                            </div>
                        @endforeach

                        <button dusk="remove-last" wire:click="removeLastItem">Remove Last</button>
                        <div dusk="count">Count: {{ count($items) }}</div>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@count', 'Count: 3')
            ->assertSeeIn('@item-1-status', 'OFF')
            ->assertSeeIn('@item-2-status', 'ON')
            ->assertSeeIn('@item-3-status', 'OFF')

            // Remove last item - this would previously cause "Cannot read properties of undefined"
            // because the effect for item 3 (x-text="$wire.items[2].expanded") would fire
            // BEFORE the element was removed from the DOM
            ->waitForLivewire()->click('@remove-last')
            ->assertSeeIn('@count', 'Count: 2')
            ->assertMissing('@item-3-name')
            ->assertConsoleLogHasNoErrors()

            // Remove another - same issue with items[1]
            ->waitForLivewire()->click('@remove-last')
            ->assertSeeIn('@count', 'Count: 1')
            ->assertMissing('@item-2-name')
            ->assertConsoleLogHasNoErrors()

            // Verify remaining item still works
            ->waitForLivewire()->click('@toggle-1')
            ->assertSeeIn('@item-1-status', 'ON')
            ->assertConsoleLogHasNoErrors()
        ;
    }

    public function test_adding_then_removing_array_items()
    {
        // This tests adding items then removing them - the removal is what tests the fix
        Livewire::visit(new class extends Component {
            public array $items = [
                ['id' => 1, 'name' => 'Item 1'],
            ];

            public function addItem()
            {
                $id = count($this->items) + 1;
                $this->items[] = ['id' => $id, 'name' => "Item {$id}"];
            }

            public function removeLast()
            {
                array_pop($this->items);
            }

            function render() {
                return <<<'HTML'
                    <div>
                        @foreach ($items as $idx => $item)
                            <div wire:key="item-{{ $item['id'] }}">
                                <span dusk="item-{{ $item['id'] }}-name" x-text="$wire.items[{{ $idx }}].name"></span>
                            </div>
                        @endforeach

                        <button dusk="add" wire:click="addItem">Add</button>
                        <button dusk="remove" wire:click="removeLast">Remove</button>
                        <div dusk="count">Count: {{ count($items) }}</div>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@count', 'Count: 1')
            ->assertSeeIn('@item-1-name', 'Item 1')

            // Add items
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@count', 'Count: 2')
            ->assertSeeIn('@item-2-name', 'Item 2')

            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@count', 'Count: 3')
            ->assertSeeIn('@item-3-name', 'Item 3')

            // Now remove - THIS is what tests the transaction fix
            ->waitForLivewire()->click('@remove')
            ->assertSeeIn('@count', 'Count: 2')
            ->assertMissing('@item-3-name')
            ->assertConsoleLogHasNoErrors()

            ->waitForLivewire()->click('@remove')
            ->assertSeeIn('@count', 'Count: 1')
            ->assertMissing('@item-2-name')
            ->assertConsoleLogHasNoErrors()
        ;
    }

    public function test_removing_last_item_with_different_data_types()
    {
        // This tests removing items with various data types in the binding
        Livewire::visit(new class extends Component {
            public array $items = [
                ['id' => 1, 'count' => 10, 'active' => true],
                ['id' => 2, 'count' => 20, 'active' => false],
                ['id' => 3, 'count' => 30, 'active' => true],
            ];

            public function removeLast()
            {
                array_pop($this->items);
            }

            function render() {
                return <<<'HTML'
                    <div>
                        @foreach ($items as $idx => $item)
                            <div wire:key="item-{{ $item['id'] }}" dusk="item-{{ $item['id'] }}">
                                <span x-text="$wire.items[{{ $idx }}].count + ' - ' + ($wire.items[{{ $idx }}].active ? 'active' : 'inactive')"></span>
                            </div>
                        @endforeach

                        <button dusk="remove" wire:click="removeLast">Remove Last</button>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@item-1', '10 - active')
            ->assertSeeIn('@item-2', '20 - inactive')
            ->assertSeeIn('@item-3', '30 - active')

            ->waitForLivewire()->click('@remove')
            ->assertMissing('@item-3')
            ->assertConsoleLogHasNoErrors()

            ->waitForLivewire()->click('@remove')
            ->assertMissing('@item-2')
            ->assertConsoleLogHasNoErrors()
        ;
    }

    public function test_bulk_remove_all_items_at_once()
    {
        // This tests removing ALL items in a single action
        // When all items are removed, all elements are cleaned up
        Livewire::visit(new class extends Component {
            public array $items = [
                ['id' => 1, 'label' => 'First'],
                ['id' => 2, 'label' => 'Second'],
                ['id' => 3, 'label' => 'Third'],
            ];

            public function removeAll()
            {
                $this->items = [];
            }

            function render() {
                return <<<'HTML'
                    <div>
                        @foreach ($items as $idx => $item)
                            <div wire:key="item-{{ $item['id'] }}" dusk="item-{{ $item['id'] }}">
                                <span x-text="$wire.items[{{ $idx }}].label"></span>
                            </div>
                        @endforeach

                        <button dusk="remove-all" wire:click="removeAll">Remove All</button>
                        <div dusk="count">Count: {{ count($items) }}</div>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@count', 'Count: 3')
            ->assertSeeIn('@item-1', 'First')
            ->assertSeeIn('@item-2', 'Second')
            ->assertSeeIn('@item-3', 'Third')

            // Remove all 3 items at once
            // Effects for all 3 elements would fire before cleanup without the transaction fix
            ->waitForLivewire()->click('@remove-all')
            ->assertSeeIn('@count', 'Count: 0')
            ->assertMissing('@item-1')
            ->assertMissing('@item-2')
            ->assertMissing('@item-3')
            ->assertConsoleLogHasNoErrors()
        ;
    }

    public function test_clear_all_items_from_array()
    {
        // This tests clearing all items from an array
        Livewire::visit(new class extends Component {
            public array $items = [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2'],
                ['id' => 3, 'name' => 'Item 3'],
            ];

            public function clearAll()
            {
                $this->items = [];
            }

            public function addItem()
            {
                $this->items[] = ['id' => count($this->items) + 1, 'name' => 'New Item'];
            }

            function render() {
                return <<<'HTML'
                    <div>
                        @foreach ($items as $idx => $item)
                            <div wire:key="item-{{ $item['id'] }}" dusk="item-{{ $item['id'] }}">
                                <span x-text="$wire.items[{{ $idx }}].name"></span>
                            </div>
                        @endforeach

                        <button dusk="clear" wire:click="clearAll">Clear All</button>
                        <button dusk="add" wire:click="addItem">Add</button>
                        <div dusk="count">Count: {{ count($items) }}</div>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@count', 'Count: 3')

            // Clear all items - removes 3 elements at once, all with x-text bindings
            ->waitForLivewire()->click('@clear')
            ->assertSeeIn('@count', 'Count: 0')
            ->assertMissing('@item-1')
            ->assertMissing('@item-2')
            ->assertMissing('@item-3')
            ->assertConsoleLogHasNoErrors()

            // Add items back
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@count', 'Count: 1')
            ->assertSeeIn('@item-1', 'New Item')
            ->assertConsoleLogHasNoErrors()
        ;
    }

    public function test_nested_array_property_access_after_removal()
    {
        // This tests deeply nested property access that would fail if effects fire before cleanup
        Livewire::visit(new class extends Component {
            public array $items = [
                ['id' => 1, 'meta' => ['tags' => ['a', 'b'], 'stats' => ['views' => 100]]],
                ['id' => 2, 'meta' => ['tags' => ['c', 'd'], 'stats' => ['views' => 200]]],
                ['id' => 3, 'meta' => ['tags' => ['e', 'f'], 'stats' => ['views' => 300]]],
            ];

            public function removeLast()
            {
                array_pop($this->items);
            }

            function render() {
                return <<<'HTML'
                    <div>
                        @foreach ($items as $idx => $item)
                            <div wire:key="item-{{ $item['id'] }}" dusk="item-{{ $item['id'] }}">
                                <span x-text="$wire.items[{{ $idx }}].meta.stats.views + ' views'"></span>
                            </div>
                        @endforeach

                        <button dusk="remove" wire:click="removeLast">Remove Last</button>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@item-1', '100 views')
            ->assertSeeIn('@item-2', '200 views')
            ->assertSeeIn('@item-3', '300 views')

            // Remove last item - deeply nested access would error without transaction fix
            ->waitForLivewire()->click('@remove')
            ->assertMissing('@item-3')
            ->assertConsoleLogHasNoErrors()

            ->waitForLivewire()->click('@remove')
            ->assertMissing('@item-2')
            ->assertConsoleLogHasNoErrors()
        ;
    }
}
