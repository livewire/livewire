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

        ->waitUntilMissingText('Loading "prop"...')

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

    function test_wire_target_works_with_multiple_function_including_multiple_params()
    {
        Livewire::visit(new class extends Component {

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button wire:click="processFunction('1', 2)" dusk="process1Button">Process 1 and 2</button>
                        <button wire:click="processFunction('3', 4)" dusk="process2Button">Process 3 adn 4</button>
                        <button wire:click="resetFunction" dusk="resetButton">Reset</button>
                        <div wire:loading wire:target="resetFunction" dusk="loadingIndicator">
                            Waiting to process...
                        </div>
                        <div wire:loading wire:target="processFunction('1', 2), processFunction('3', 4)" dusk="loadingIndicator2">
                            Processing...
                        </div>
                    </div>
                HTML;
            }

            public function processFunction(string $value)
            {
                usleep(500000); // Simulate some processing time.
            }
            public function resetFunction()
            {
                usleep(500000); // Simulate reset time.
            }
        })
            ->press('@resetButton')
            ->pause(250)
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

    function test_wire_target_works_with_multiple_function_multiple_params_using_js_helper()
    {
        Livewire::visit(new class extends Component {
            public function mountAction(string $action, array $params = [], array $context = [])
            {
                usleep(500000); // Simulate some processing time.
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button wire:click="mountAction('add', {{ \Illuminate\Support\Js::from(['block' => 'name']) }}, { schemaComponent: 'tableFiltersForm.queryBuilder.rules' })" dusk="mountButton">Mount</button>
                        <div wire:loading wire:target="mountAction('add', {{ \Illuminate\Support\Js::from(['block' => 'name']) }}, { schemaComponent: 'tableFiltersForm.queryBuilder.rules' })">
                            Mounting...
                        </div>
                    </div>
                    HTML;
            }
        })
            ->assertDontSee('Mounting...')
            ->press('@mountButton')
            ->waitForText('Mounting...')
            ->assertSee('Mounting...')
            ->pause(400)
            ->waitUntilMissingText('Mounting...')
            ->assertDontSee('Mounting...')
        ;
    }

    function test_wire_target_works_with_function_JSONparse_params()
    {
        Livewire::visit(new class extends Component {

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button wire:click="processFunction(@js(['bar' => 'baz']))" dusk="processButton">Process</button>
                        <button wire:click="resetFunction" dusk="resetButton">Reset</button>
                        <div wire:loading wire:target="resetFunction" dusk="loadingIndicator">
                            Waiting to process...
                        </div>
                        <div wire:loading wire:target="processFunction" dusk="loadingIndicator2">
                            Processing...
                        </div>
                    </div>
                HTML;
            }

            public function processFunction(mixed $value)
            {
                usleep(500000); // Simulate some processing time.
            }
            public function resetFunction()
            {
                usleep(500000); // Simulate reset time.
            }
        })
            ->press('@resetButton')
            ->pause(250)
            ->waitForText('Waiting to process...')
            ->assertSee('Waiting to process...')
            ->assertDontSee('Processing...')
            ->waitUntilMissingText('Waiting to process...')
            ->press('@processButton')
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

    function test_wire_loading_targets_exclude_wire_navigate()
    {
        Livewire::visit(new class extends Component {
            public function hydrate()
            {
                sleep(1);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <a href="/otherpage" wire:navigate dusk="link" class="text-blue-500" wire:loading.class="text-red-500">Link</a>
                        <button type="button" wire:click="$refresh" dusk="refresh-button">Refresh</button>
                    </div>
                HTML;
            }
        })
        ->assertHasClass('@link', 'text-blue-500')
        ->click('@refresh-button')
        ->pause(5)
        ->assertHasClass('@link', 'text-red-500')
        ;
    }

    public function test_a_component_can_show_loading_without_showing_island_loading()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                            @endisland
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')

            ->click('@component-slow-request')
            // Wait for the Livewire request to start...
            ->pause(10)
            ->assertVisible('@component-loading')
            ->assertMissing('@island-loading')

            // Wait for the Livewire request to finish...
            ->waitUntilMissingText('Loading...')

            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')
            ;
    }

    public function test_an_island_can_show_loading_without_showing_component_loading()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                            @endisland
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')

            ->click('@island-slow-request')
            // Wait for the Livewire request to start...
            ->pause(10)
            ->assertMissing('@component-loading')
            ->assertVisible('@island-loading')

            // Wait for the Livewire request to finish...
            ->waitUntilMissingText('Island loading...')

            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')
            ;
    }

    public function test_an_island_and_component_can_show_different_loading_states()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                            @endisland
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')

            // Run the component request and then the island request...

            ->click('@component-slow-request')
            // Wait for the component request to start...
            ->pause(10)
            ->assertVisible('@component-loading')
            ->assertMissing('@island-loading')

            // Wait a bit before starting the island request...
            ->pause(200)

            ->click('@island-slow-request')
            // Wait for the island request to start...
            ->pause(10)
            ->assertVisible('@component-loading')
            ->assertVisible('@island-loading')

            // Wait for the component request to finish...
            ->waitUntilMissingText('Loading...')

            ->assertMissing('@component-loading')
            ->assertVisible('@island-loading')

            // Wait for the island request to finish...
            ->waitUntilMissingText('Island loading...')

            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')

            // Run the island request and then the component request...

            ->click('@island-slow-request')
            // Wait for the island request to start...
            ->pause(10)
            ->assertMissing('@component-loading')
            ->assertVisible('@island-loading')

            // Wait a bit before starting the component request...
            ->pause(200)

            ->click('@component-slow-request')
            // Wait for the component request to start...
            ->pause(10)
            ->assertVisible('@component-loading')
            ->assertVisible('@island-loading')

            // Wait for the island request to finish...
            ->waitUntilMissingText('Island loading...')

            ->assertVisible('@component-loading')
            ->assertMissing('@island-loading')

            // Wait for the component request to finish...
            ->waitUntilMissingText('Loading...')

            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')
            ;
    }

    public function test_a_component_can_show_targeted_loading()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>
                        <div wire:loading.block wire:target="slowRequest" dusk="component-loading-targeted">Component loading targeted...</div>
                        <div wire:loading.block wire:target="otherRequest" dusk="component-loading-targeted-other">Component loading targeted other...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                                <div wire:loading.block wire:target="slowRequest" dusk="island-loading-targeted">Island loading targeted...</div>
                                <div wire:loading.block wire:target="otherRequest" dusk="island-loading-targeted-other">Island loading targeted other...</div>
                            @endisland
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@component-loading-targeted')
            ->assertMissing('@component-loading-targeted-other')
            ->assertMissing('@island-loading')
            ->assertMissing('@island-loading-targeted')
            ->assertMissing('@island-loading-targeted-other')

            ->click('@component-slow-request')
            // Wait for the component request to start...
            ->pause(10)
            ->assertVisible('@component-loading')
            ->assertVisible('@component-loading-targeted')
            ->assertMissing('@component-loading-targeted-other')
            ->assertMissing('@island-loading')
            ->assertMissing('@island-loading-targeted')
            ->assertMissing('@island-loading-targeted-other')

            // Wait for the component request to finish...
            ->waitUntilMissingText('Loading...')

            ->assertMissing('@component-loading')
            ->assertMissing('@component-loading-targeted')
            ->assertMissing('@component-loading-targeted-other')
            ->assertMissing('@island-loading')
            ->assertMissing('@island-loading-targeted')
            ->assertMissing('@island-loading-targeted-other')
            ;
    }

    public function test_an_island_can_show_targeted_loading()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>
                        <div wire:loading.block wire:target="slowRequest" dusk="component-loading-targeted">Component loading targeted...</div>
                        <div wire:loading.block wire:target="otherRequest" dusk="component-loading-targeted-other">Component loading targeted other...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                                <div wire:loading.block wire:target="slowRequest" dusk="island-loading-targeted">Island loading targeted...</div>
                                <div wire:loading.block wire:target="otherRequest" dusk="island-loading-targeted-other">Island loading targeted other...</div>
                            @endisland
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@component-loading-targeted')
            ->assertMissing('@component-loading-targeted-other')
            ->assertMissing('@island-loading')
            ->assertMissing('@island-loading-targeted')
            ->assertMissing('@island-loading-targeted-other')

            ->click('@island-slow-request')
            // Wait for the island request to start...
            ->pause(10)
            ->assertMissing('@component-loading')
            ->assertMissing('@component-loading-targeted')
            ->assertMissing('@component-loading-targeted-other')
            ->assertVisible('@island-loading')
            ->assertVisible('@island-loading-targeted')
            ->assertMissing('@island-loading-targeted-other')

            // Wait for the island request to finish...
            ->waitUntilMissingText('Island loading...')

            ->assertMissing('@component-loading')
            ->assertMissing('@component-loading-targeted')
            ->assertMissing('@component-loading-targeted-other')
            ->assertMissing('@island-loading')
            ->assertMissing('@island-loading-targeted')
            ->assertMissing('@island-loading-targeted-other')
            ;
    }

    public function test_a_second_component_request_doesnt_cancel_loading()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                            @endisland
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')

            ->click('@component-slow-request')
            // Wait for the component request to start...
            ->pause(10)
            ->assertVisible('@component-loading')
            ->assertMissing('@island-loading')

            // Pause for a bit before starting the second request...
            ->pause(300)

            ->click('@component-slow-request')
            // Wait for the component request to start...
            ->pause(10)
            ->assertVisible('@component-loading')
            ->assertMissing('@island-loading')

            // Pause for long enough for the first request to finish if it were to continue to run...
            ->pause(300)
            ->assertVisible('@component-loading')
            ->assertMissing('@island-loading')

            // Pause for long enough for the second request to finish if it were to continue to run...
            ->pause(300)
            ->waitUntilMissingText('Loading...')
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')
            ;
    }

    public function test_a_second_island_request_doesnt_cancel_loading()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                            @endisland
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')

            ->click('@island-slow-request')
            // Wait for the island request to start...
            ->pause(10)
            ->assertMissing('@component-loading')
            ->assertVisible('@island-loading')

            // Pause for a bit before starting the second request...
            ->pause(300)

            ->click('@island-slow-request')
            // Wait for the island request to start...
            ->pause(10)
            ->assertMissing('@component-loading')
            ->assertVisible('@island-loading')

            // Pause for long enough for the first request to finish if it were to continue to run...
            ->pause(300)
            ->assertMissing('@component-loading')
            ->assertVisible('@island-loading')

            // Pause for long enough for the second request to finish if it were to continue to run...
            ->pause(300)
            ->waitUntilMissingText('Island loading...')
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')
            ;
    }

    public function test_a_cancelled_component_request_cancels_loading()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                            @endisland
                        </div>
                    </div>
                    @script
                    <script>
                        this.intercept(({ message }) => {
                            setTimeout(() => {
                                message.cancel();
                            }, 200);
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')

            ->click('@component-slow-request')
            // Wait for the component request to start...
            ->pause(10)
            ->assertVisible('@component-loading')
            ->assertMissing('@island-loading')

            // The request is scheduled to be cancelled after 200ms, so we pause for a bit longer than that...
            ->pause(300)
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')
            ;
    }

    public function test_a_cancelled_island_request_cancels_loading()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="component-slow-request">Component slow request</button>
                        <div wire:loading.block dusk="component-loading">Loading...</div>

                        <div>
                            @island
                                <button wire:click="slowRequest" dusk="island-slow-request">Island slow request</button>
                                <div wire:loading.block dusk="island-loading">Island loading...</div>
                            @endisland
                        </div>
                    </div>
                    @script
                    <script>
                        this.intercept(({ message }) => {
                            setTimeout(() => {
                                message.cancel();
                            }, 200);
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
            ->waitForLivewireToLoad()
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')

            ->click('@island-slow-request')
            // Wait for the island request to start...
            ->pause(10)
            ->assertMissing('@component-loading')
            ->assertVisible('@island-loading')

            // The request is scheduled to be cancelled after 200ms, so we pause for a bit longer than that...
            ->pause(300)
            ->assertMissing('@component-loading')
            ->assertMissing('@island-loading')
            ;
    }
}

class PostFormStub extends Form
{
    public $text = '';
}
