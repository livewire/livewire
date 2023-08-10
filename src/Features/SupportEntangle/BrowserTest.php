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
    public function can_destroy_entangled_watchers_when_they_are_removed_from_the_dom()
    {
        Livewire::visit(new class extends Component {
            public $foo = null;

            public $isVisible = true;

            public function increment()
            {
                $this->foo = Str::random();
            }

            public function hideAndIncrement()
            {
                $this->isVisible = false;
                $this->increment();
            }

            function render()
            {
                return <<<'HTML'
                <div x-data="{}">
                    <div>
                        @if ($isVisible)
                            <div
                                x-data="{ foo: $wire.entangle('foo') }"
                                x-init="
                                    $watch('foo', () => {
                                        $refs.counter.innerText = +$refs.counter.innerText + 1
                                    })
                                "
                            ></div>
                        @endif
                    </div>

                    <span
                        dusk="counter"
                        wire:ignore
                        x-ref="counter"
                    >0</span>

                    <button dusk="increment" type="button" wire:click="increment">
                        Increment
                    </button>

                    <button dusk="hideAndIncrement" type="button" wire:click="hideAndIncrement">
                        Hide and increment
                    </button>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@counter', '0')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@counter', '1')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@counter', '2')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@counter', '3')
            ->waitForLivewire()->click('@hideAndIncrement')
            ->assertSeeIn('@counter', '3');
    }
}
