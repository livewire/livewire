<?php

namespace Livewire\Features\SupportSelection;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\Selection;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_checkboxes_can_wire_model_to_a_selection_and_sync_to_the_server()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <span dusk="count" x-text="$wire.selection.count()"></span>

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="server">{{ $selection->count() }}:{{ implode(',', $selection->all()) }}</span>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')
        ->check('@one')
        ->assertSeeIn('@count', '1')
        ->check('@two')
        ->assertSeeIn('@count', '2')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', '2:1,2')
        ->uncheck('@one')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', '1:2')
        ;
    }

    public function test_selection_methods_are_callable_from_directive_expressions()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <template wire:if="selection.any()">
                        <div dusk="toolbar">
                            <button dusk="clear" type="button" wire:click="selection.clear()">Clear selection</button>
                        </div>
                    </template>

                    <button dusk="select-two" type="button" wire:click="selection.select('2')">Select two</button>

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="server">{{ $selection->count() }}</span>
                </div>
                HTML;
            }
        })
        ->assertNotPresent('@toolbar')
        ->click('@select-two')
        ->assertPresent('@toolbar')
        ->assertChecked('@two')
        ->check('@one')
        ->click('@clear')
        ->assertNotPresent('@toolbar')
        ->assertNotChecked('@one')
        ->assertNotChecked('@two')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', '0')
        ;
    }

    public function test_select_page_selects_every_bound_checkbox_on_the_page()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />
                    <input type="checkbox" dusk="three" wire:model="selection" value="3" />

                    <span dusk="count" x-text="$wire.selection.count()"></span>

                    <button dusk="select-page" type="button" wire:click="selection.selectPage()">Select page</button>

                    <button dusk="refresh" type="button" wire:click="$refresh">Refresh</button>

                    <span dusk="server">{{ implode(',', $selection->all()) }}</span>
                </div>
                HTML;
            }
        })
        ->click('@select-page')
        ->assertChecked('@one')
        ->assertChecked('@two')
        ->assertChecked('@three')
        ->assertSeeIn('@count', '3')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@server', '1,2,3')
        ;
    }

    public function test_select_page_still_works_after_a_manual_checkbox_toggle_replaces_the_selection()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <span dusk="count" x-text="$wire.selection.count()"></span>

                    <button dusk="select-page" type="button" wire:click="selection.selectPage()">Select page</button>
                </div>
                HTML;
            }
        })
        // Checking (then unchecking) a box replaces the selection instance
        // through Alpine's concat/filter — selectPage must survive that...
        ->check('@one')
        ->uncheck('@one')
        ->assertSeeIn('@count', '0')
        ->click('@select-page')
        ->assertChecked('@one')
        ->assertChecked('@two')
        ->assertSeeIn('@count', '2')
        ;
    }

    public function test_a_header_checkbox_bound_to_the_page_facet_toggles_the_whole_page()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="header" wire:model="selection.page" />

                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <span dusk="count" x-text="$wire.selection.count()"></span>
                </div>
                HTML;
            }
        })
        ->assertNotChecked('@header')
        ->check('@header')
        ->assertChecked('@one')
        ->assertChecked('@two')
        ->assertChecked('@header')
        ->assertSeeIn('@count', '2')
        ->uncheck('@header')
        ->assertNotChecked('@one')
        ->assertNotChecked('@two')
        ->assertSeeIn('@count', '0')
        ;
    }

    public function test_the_header_checkbox_is_indeterminate_when_the_page_is_partially_selected()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection;
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="header" wire:model="selection.page" />

                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />
                </div>
                HTML;
            }
        })
        ->assertScript('document.querySelector(\'[dusk="header"]\').indeterminate', false)
        ->check('@one')
        ->assertScript('document.querySelector(\'[dusk="header"]\').indeterminate', true)
        ->assertNotChecked('@header')
        ->check('@two')
        ->assertScript('document.querySelector(\'[dusk="header"]\').indeterminate', false)
        ->assertChecked('@header')
        ;
    }

    public function test_deselecting_the_page_keeps_off_page_selections()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection(['99']);
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="header" wire:model="selection.page" />

                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <span dusk="count" x-text="$wire.selection.count()"></span>
                </div>
                HTML;
            }
        })
        ->assertNotChecked('@header')
        ->check('@header')
        ->assertSeeIn('@count', '3')
        ->uncheck('@header')
        // Only the page's values were deselected — 99 survives...
        ->assertSeeIn('@count', '1')
        ;
    }

    public function test_a_server_driven_change_merges_into_the_existing_selection_instance()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection(['1']);
            }

            public function selectTwo(): void
            {
                $this->selection->select('2');
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <button dusk="store" type="button" x-on:click="window.__selectionRef = $wire.selection">Store</button>

                    <button dusk="select-two" type="button" wire:click="selectTwo">Select two</button>

                    <button dusk="compare" type="button" x-on:click="$refs.same.textContent = window.__selectionRef === $wire.selection ? 'same' : 'different'">Compare</button>

                    <span x-ref="same" dusk="same"></span>
                </div>
                HTML;
            }
        })
        ->click('@store')
        ->waitForLivewire()->click('@select-two')
        ->assertChecked('@two')
        ->click('@compare')
        ->assertSeeIn('@same', 'same')
        ;
    }

    public function test_a_server_side_selection_renders_checked_boxes_and_server_mutations_reach_the_client()
    {
        Livewire::visit(new class extends Component {
            public Selection $selection;

            public function mount(): void
            {
                $this->selection = new Selection(['2']);
            }

            public function selectOne(): void
            {
                $this->selection->select('1');
            }

            public function render(): string
            {
                return <<<'HTML'
                <div>
                    <input type="checkbox" dusk="one" wire:model="selection" value="1" />
                    <input type="checkbox" dusk="two" wire:model="selection" value="2" />

                    <button dusk="select-one" type="button" wire:click="selectOne">Select one</button>
                </div>
                HTML;
            }
        })
        ->assertNotChecked('@one')
        ->assertChecked('@two')
        ->waitForLivewire()->click('@select-one')
        ->assertChecked('@one')
        ->assertChecked('@two')
        ;
    }
}
