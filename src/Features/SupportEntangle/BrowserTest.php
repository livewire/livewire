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
    public function is_not_live_by_default()
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

    /** @test */
    public function can_be_forced_to_not_be_live()
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

    /** @test */
    public function can_be_live()
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
}
