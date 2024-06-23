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
}
