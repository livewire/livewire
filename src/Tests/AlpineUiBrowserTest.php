<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class AlpineUiBrowserTest extends \Tests\BrowserTestCase
{
    public function test_component_with_listbox_and_wire_model_live_should_not_cause_infinite_loop()
    {
        Livewire::visit(new class extends Component {
            public ?array $foo = null;

            function render() {
                return <<<'HTML'
                <div>
                    <script src="https://unpkg.com/@alpinejs/ui@3.13.4-beta.0/dist/cdn.min.js"></script>

                    <button wire:click="$refresh">refresh</button>

                    <div
                        x-data="{
                            value: null,
                            frameworks: [{
                                id: 1,
                                name: 'Laravel',
                                disabled: false,
                            }],
                            updates: 0,
                        }" x-modelable="value" wire:model.live="foo" x-effect="console.log(value); updates++">
                        <div>updates: <span x-text="updates" dusk="updatesCount"></span></div>

                        <div x-listbox x-model="value">
                            <label x-listbox:label>Backend framework</label>

                            <button x-listbox:button dusk="openListbox">
                                <span x-text="value ? value.name : 'Select framework'"></span>
                            </button>

                            <ul x-listbox:options x-cloak>
                                <template x-for="framework in frameworks" :key="framework.id">
                                    <li
                                        x-listbox:option
                                        :value="framework"
                                        :disabled="framework.disabled"
                                        dusk="listboxOption">
                                        <span x-text="framework.name"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->click('@openListbox')
            ->assertSeeIn('@updatesCount', '1')
            ->pressAndWaitFor('@listboxOption', 250)
            ->assertSeeIn('@updatesCount', '2')
        ;
    }

    public function test_component_with_combobox_and_wire_model_live_should_not_cause_infinite_loop()
    {
        Livewire::visit(new class extends Component {
            public ?array $value = null;

            function render() {
                return <<<'HTML'
                <div>
                    <script src="https://unpkg.com/@alpinejs/ui@3.13.4-beta.0/dist/cdn.min.js"></script>

                    <div
                        x-data="{
                            query: '',
                            selected: null,
                            frameworks: [{
                                id: 1,
                                name: 'Laravel',
                                disabled: false,
                            }, ],
                            get filteredFrameworks() {
                                return this.query === '' ?
                                    this.frameworks :
                                    this.frameworks.filter((framework) => {
                                        return framework.name.toLowerCase().includes(this.query.toLowerCase())
                                    })
                            },
                            updates: 0,
                        }" x-modelable="selected" wire:model.live="value" x-effect="console.log(selected); updates++">
                        <div>updates: <span x-text="updates" dusk="updatesCount"></span></div>

                        <div x-combobox x-model="selected">
                            <div>
                                <div>
                                    <input
                                        x-combobox:input
                                        :display-value="framework => framework?.name"
                                        @change="query = $event.target.value;"
                                        placeholder="Search..." />
                                    <button x-combobox:button dusk="openCombobox">
                                        open combobox
                                    </button>
                                </div>

                                <div x-combobox:options x-cloak>
                                    <ul>
                                        <template
                                            x-for="framework in filteredFrameworks"
                                            :key="framework.id"
                                            hidden>
                                            <li
                                                x-combobox:option
                                                :value="framework"
                                                :disabled="framework.disabled"
                                                dusk="comboboxOption">
                                                <span x-text="framework.name"></span>
                                            </li>
                                        </template>
                                    </ul>

                                    <p x-show="filteredFrameworks.length == 0">No frameworks match your query.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->click('@openCombobox')
            ->assertSeeIn('@updatesCount', '1')
            ->pressAndWaitFor('@comboboxOption', 250)
            ->assertSeeIn('@updatesCount', '2')
        ;
    }
}
