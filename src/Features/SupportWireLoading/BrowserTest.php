<?php

namespace Livewire\Features\SupportWireLoading;

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    function can_wire_target_to_a_form_object_property()
    {
        Livewire::visit(new class extends Component {
            public PostFormStub $form;

            public $localText = '';

            public function updating() {
                // Need to delay the update so that Dusk can catch the loading state change in the DOM.
                usleep(500000);
            }

            public function render() {
                return <<<'HTML'
                    <div>
                        <section>
                            <span
                                wire:loading.remove
                                wire:target="localText">
                                Loaded localText...
                            </span>
                            <span
                                wire:loading
                                wire:target="localText"
                            >
                                Loading localText...
                            </span>
                            <input type="text" dusk="localInput" wire:model.live.debounce.100ms="localText">
                            {{ $localText }}
                        </section>
                        <section>
                            <span
                                wire:loading.remove
                                wire:target="form.text">
                                Loaded form.text...
                            </span>
                            <span
                                wire:loading
                                wire:target="form.text"
                            >
                                Loading form.text...
                            </span>
                            <input type="text" dusk="formInput" wire:model.live.debounce.100ms="form.text">
                            {{ $form->text }}
                        </section>
                    </div>
                HTML;
            }
        })
        ->waitForText('Loaded localText')
        ->assertSee('Loaded localText')
        ->type('@localInput', 'Text')
        ->waitUntilMissingText('Loaded localText')
        ->assertDontSee('Loaded localText')
        ->waitForText('Loaded localText')
        ->assertSee('Loaded localText')

        ->waitForText('Loaded form.text')
        ->assertSee('Loaded form.text')
        ->type('@formInput', 'Text')
        ->waitUntilMissingText('Loaded form.text')
        ->assertDontSee('Loaded form.text')
        ->waitForText('Loaded form.text')
        ->assertSee('Loaded form.text')
        ;
    }

    /** @test */
    function wire_loading_attr_doesnt_conflict_with_exist_one()
    {
        Livewire::visit(new class extends Component {
            public $localText = '';

            public function updating() {
                // Need to delay the update so that Dusk can catch the loading state change in the DOM.
                usleep(500000);
            }

            public function render() {
                return <<<'HTML'
                    <div>
                        <section>
                            <button
                                disabled
                                dusk="button"
                                wire:loading.attr="disabled"
                                wire:target="localText">
                                Submit
                            </button>
                            <input type="text" dusk="localInput" wire:model.live.debounce.100ms="localText">
                            {{ $localText }}
                        </section>
                    </div>
                HTML;
            }
        })
        ->waitForText('Submit')
        ->assertSee('Submit')
        ->assertAttribute('@button', 'disabled', 'true')
        ->type('@localInput', 'Text')
        ->assertAttribute('@button', 'disabled', 'true')
        ->waitForText('Text')
        ->assertAttribute('@button', 'disabled', 'true')
        ;
    }
}

class PostFormStub extends Form
{
    public $text = '';
}
