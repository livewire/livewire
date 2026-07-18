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
