<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class AlpineListboxBrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function component_with_listbox_and_wire_model_should_not_cause_infinite_loop()
    {
        Livewire::visit(new class extends Component {
            public ?array $value = null;

            function render() {
                return <<<'HTML'
                <div>
                    <script defer src="https://unpkg.com/@alpinejs/ui@3.13.4-beta.0/dist/cdn.min.js"></script>

                    <div
                        x-data="{
                            value: null,
                            frameworks: [{
                                id: 1,
                                name: 'Laravel',
                                disabled: false,
                            }],
                            updates: 0,
                        }" x-modelable="value" wire:model.live="value" x-effect="console.log(value); updates++">
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
            ->waitForLivewire()->click('@openListbox')->pressAndWaitFor('@listboxOption', 5)
            ->assertDontSeeIn('@updatesCount', '0')
            ->assertDontSeeIn('@updatesCount', '1')
        ;
    }
}
