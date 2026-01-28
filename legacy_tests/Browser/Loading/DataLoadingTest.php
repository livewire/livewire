<?php

namespace LegacyTests\Browser\Loading;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class DataLoadingTest extends BrowserTestCase
{
    public function test_wire_submit_applies_data_loading_to_submit_button()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function save()
            {
                sleep(1);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <form wire:submit="save" dusk="form">
                            <input wire:model="title" type="text" dusk="input">
                            <button type="submit" dusk="submit">Save</button>
                        </form>
                    </div>
                HTML;
            }
        })
            // Initially, neither form nor button should have data-loading
            ->assertAttributeMissing('@form', 'data-loading')
            ->assertAttributeMissing('@submit', 'data-loading')

            // Click submit and verify data-loading is applied to button, not form
            ->waitForLivewire(function ($browser) {
                $browser->click('@submit')
                    ->pause(50)
                    ->assertAttributeMissing('@form', 'data-loading')
                    ->assertAttribute('@submit', 'data-loading', 'true');
            })

            // After request completes, data-loading should be removed
            ->assertAttributeMissing('@form', 'data-loading')
            ->assertAttributeMissing('@submit', 'data-loading')
        ;
    }

    public function test_wire_submit_with_enter_key_applies_data_loading_to_submit_button()
    {
        Livewire::visit(new class extends Component {
            public $title = '';

            public function save()
            {
                sleep(1);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <form wire:submit="save" dusk="form">
                            <input wire:model="title" type="text" dusk="input">
                            <button type="submit" dusk="submit">Save</button>
                        </form>
                    </div>
                HTML;
            }
        })
            ->assertAttributeMissing('@submit', 'data-loading')

            // Press Enter in input field
            ->waitForLivewire(function ($browser) {
                $browser->type('@input', 'test')
                    ->keys('@input', '{enter}')
                    ->pause(50)
                    ->assertAttribute('@submit', 'data-loading', 'true');
            })

            ->assertAttributeMissing('@submit', 'data-loading')
        ;
    }

    public function test_wire_submit_with_multiple_submit_buttons()
    {
        Livewire::visit(new class extends Component {
            public $action = '';

            public function save()
            {
                $this->action = 'save';
                sleep(1);
            }

            public function draft()
            {
                $this->action = 'draft';
                sleep(1);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <form wire:submit="save" dusk="form">
                            <input wire:model="action" type="text" dusk="input">
                            <button type="submit" dusk="save-button">Save</button>
                            <button type="submit" wire:click.prevent="draft" dusk="draft-button">Draft</button>
                        </form>
                    </div>
                HTML;
            }
        })
            // Click draft button - only that button should get data-loading
            ->waitForLivewire(function ($browser) {
                $browser->click('@draft-button')
                    ->pause(50)
                    ->assertAttributeMissing('@save-button', 'data-loading')
                    ->assertAttribute('@draft-button', 'data-loading', 'true');
            })

            // Click save button - only that button should get data-loading
            ->waitForLivewire(function ($browser) {
                $browser->click('@save-button')
                    ->pause(50)
                    ->assertAttribute('@save-button', 'data-loading', 'true')
                    ->assertAttributeMissing('@draft-button', 'data-loading');
            })
        ;
    }

    public function test_wire_click_still_applies_data_loading_to_clicked_element()
    {
        Livewire::visit(new class extends Component {
            public function doSomething()
            {
                sleep(1);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button wire:click="doSomething" dusk="button">Click me</button>
                    </div>
                HTML;
            }
        })
            ->assertAttributeMissing('@button', 'data-loading')

            ->waitForLivewire(function ($browser) {
                $browser->click('@button')
                    ->pause(50)
                    ->assertAttribute('@button', 'data-loading', 'true');
            })

            ->assertAttributeMissing('@button', 'data-loading')
        ;
    }
}
