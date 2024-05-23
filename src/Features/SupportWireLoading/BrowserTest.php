<?php

namespace Livewire\Features\SupportWireLoading;

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    function test_can_wire_target_to_a_form_object_property()
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

    function test_wire_loading_remove_works_with_renderless_methods()
    {
        Livewire::visit(new class extends Component {
            #[\Livewire\Attributes\Renderless]
            public function doSomething() {
                // Need to delay the update so that Dusk can catch the loading state change in the DOM.
                usleep(500000);
            }

            public function render() {
                return <<<'HTML'
                    <div>
                        <button wire:click="doSomething" dusk="button">
                            <span wire:loading.remove>Do something</span>
                            <span wire:loading>...</span>
                        </button>
                    </div>
                HTML;
            }
        })
        ->waitForText('Do something')
        ->click('@button')
        ->waitForText('...')
        ->assertDontSee('Do something')
        ->waitForText('Do something')
        ->assertDontSee('...')
        ;
    }

    function test_wire_loading_attr_doesnt_conflict_with_exist_one()
    {
        Livewire::visit(new class extends Component {
            public $localText = '';

            public function updating() {
                // Need to delay the update so that Dusk can catch the loading state change in the DOM.
                usleep(250000);
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

    function test_wire_loading_delay_is_removed_after_being_triggered_once()
    {
        /**
         * The (broken) scenario:
         *   - first request takes LONGER than the wire:loading.delay, so the loader shows (and hides) once
         *   - second request takes SHORTER than the wire:loading.delay, the loader shows, but never hides
         */
        Livewire::visit(new class extends Component {
            public $stuff;

            public $count = 0;

            public function updating() {
                // Need to delay the update, but only on the first request
                if ($this->count === 0) {
                    usleep(500000);
                }

                $this->count++;
            }


            public function render() {
                return <<<'HTML'
                    <div>
                        <div wire:loading.delay>
                            <span>Loading...</span>
                        </div>
                        <input wire:model.live="stuff" dusk="input" type="text">
                    </div>
                HTML;
            }
        })
        ->type('@input', 'Hello Caleb')
        ->waitForText('Loading...')
        ->assertSee('Loading...')
        ->waitUntilMissingText('Loading...')
        ->assertDontSee('Loading...')
        ->type('@input', 'Bye Caleb')
        ->pause(500) // wait for the loader to show when it shouldn't (second request is fast)
        ->assertDontSee('Loading...')
        ;
    }

	function test_wire_loading_targets_single_correct_element()
    {
		/*
		 * Previously
		 */
        Livewire::visit(new class extends Component {

			public $myModel;

			public function mount()
			{
				$this->myModel = [
					'prop' => 'one',
					'prop2' => 'two',
				];
			}

			public function updating() {
                // Need to delay the update so that Dusk can catch the loading state change in the DOM.
                sleep(2);
            }

			public function render()
			{
			    return <<<'HTML'
                <div>
                	<input type="text" wire:model.live="myModel.prop" dusk="input">
                	<div wire:loading wire:target="myModel.prop">Loading "prop"...</div>
                	<input type="text" wire:model.live="myModel.prop2" dusk="input2">
                	<div wire:loading wire:target="myModel.prop2">Loading "prop2"...</div>
                	<div wire:loading wire:target="myModel">Loading "myModel"...</div>
                </div>
                HTML;
            }

        })
        ->type('@input', 'Foo')
		->waitForText('Loading "prop"...')
        ->assertSee('Loading "prop"...')
        ->assertSee('Loading "myModel"...')
        ->assertDontSee('Loading "prop2"...')

        ->type('@input2', 'Hello Caleb')
		->waitForText('Loading "prop2"...')
        ->assertSee('Loading "prop2"...')
        ->assertSee('Loading "myModel"...')
        ->assertDontSee('Loading "prop"...')
        ;
    }

    function test_inverted_wire_target_hides_loading_for_specified_action()
    {
        Livewire::visit(new class extends Component {

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button wire:click="process1Function" dusk="process1Button">Process 1</button>
                        <button wire:click="process2Function" dusk="process2Button">Process 2</button>
                        <button wire:click="resetFunction" dusk="resetButton">Reset</button>
                        <div wire:loading wire:target.except="process1Function, process2Function" dusk="loadingIndicator">
                            Waiting to process...
                        </div>
                        <div wire:loading wire:target.except="resetFunction" dusk="loadingIndicator2">
                            Processing...
                        </div>
                    </div>
                HTML;
            }

            public function process1Function()
            {
                usleep(500000); // Simulate some processing time.
            }

            public function process2Function()
            {
                usleep(500000); // Simulate some processing time.
            }

            public function resetFunction()
            {
                usleep(500000); // Simulate reset time.
            }
        })
        ->press('@resetButton')
        ->waitForText('Waiting to process...')
        ->assertSee('Waiting to process...')
        ->assertDontSee('Processing...')
        ->waitUntilMissingText('Waiting to process...')
        ->press('@process1Button')
        ->pause(250)
        ->assertDontSee('Waiting to process...')
        ->assertSee('Processing...')
        ->press('@resetButton')
        ->waitForText('Waiting to process...')
        ->assertSee('Waiting to process...')
        ->waitUntilMissingText('Waiting to process...')
        ->press('@process2Button')
        ->pause(250)
        ->assertDontSee('Waiting to process...')
        ->assertSee('Processing...')
        ;
    }

    /**
    function test_inverted_wire_target_hides_loading_for_file_upload()
    {
        Storage::persistentFake('tmp-for-tests');
        Livewire::visit(new class extends Component {
            use WithFileUploads;

            public $file1, $file2;

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input type="file" wire:model="file1" dusk="file1Input">
                        <input type="file" wire:model="file2" dusk="file2Input">
                        <button wire:click="resetFunction" dusk="resetButton">Reset</button>
                        <div wire:loading wire:target.except="file1" dusk="loadingIndicator">
                            Waiting to process...
                        </div>
                    </div>
                HTML;
            }

            public function resetFunction()
            {
                usleep(500000); // Simulate reset time.
            }
        })
        ->pause(10000000)
        ->press('@resetButton')
        ->waitForText('Waiting to process...')
        ->assertSee('Waiting to process...')
        ->waitUntilMissingText('Waiting to process...')
        ->attach('@file1Input', __DIR__ . '/browser_test_image.png')
        ->assertDontSee('Waiting to process...')
        ->attach('@file2Input', __DIR__ . '/browser_test_image.png')
        ->waitForText('Waiting to process...')
        ->assertSee('Waiting to process...')
        ;
    }
    */

	function test_wire_loading_doesnt_error_when_class_contains_two_consecutive_spaces()
    {
        Livewire::visit(new class extends Component {

			public $myModel;

			public function mount()
			{
				$this->myModel = [
					'prop' => 'one',
				];
			}

			public function updating() {
                // Need to delay the update so that Dusk can catch the loading state change in the DOM.
                sleep(2);
            }

			public function render()
			{
			    return <<<'HTML'
                <div>
                	<input type="text" wire:model.live="myModel.prop" dusk="input">
                	<div wire:loading.class="foo  bar" wire:target="myModel.prop">{{ $myModel['prop'] }}</div>
                </div>
                HTML;
            }

        })
        ->type('@input', 'Foo')
		->waitForText('Foo')
        ->assertSee('Foo')
        ;
    }
}

class PostFormStub extends Form
{
    public $text = '';
}
