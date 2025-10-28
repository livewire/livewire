<?php

namespace Livewire\Features\SupportDataLoading;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_data_loading_attribute_is_added_to_an_element_when_it_triggers_a_request()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function hydrate() {
                    usleep(250 * 1000); // 50ms
                }
                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh1">Refresh</button>
                    <button wire:click="$refresh" dusk="refresh2">Refresh</button>
                </div>
                HTML; }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertAttributeMissing('@refresh1', 'data-loading')
        ->assertAttributeMissing('@refresh2', 'data-loading')
        ->click('@refresh1')
        // Wait for the first request to start...
        ->pause(6)
        ->assertAttribute('@refresh1', 'data-loading', 'true')
        ->assertAttributeMissing('@refresh2', 'data-loading')

        // Wait for the first request to finish...
        ->pause(350)

        ->click('@refresh2')
        // Wait for the second request to start...
        ->pause(10)
        ->assertAttributeMissing('@refresh1', 'data-loading')
        ->assertAttribute('@refresh2', 'data-loading', 'true')

        // Wait for the second request to finish...
        ->pause(350)

        ->assertAttributeMissing('@refresh1', 'data-loading')
        ->assertAttributeMissing('@refresh2', 'data-loading')
        ;
    }

    public function test_data_loading_attribute_is_removed_from_an_element_when_its_request_has_finished_but_not_other_elements()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(200 * 1000); // 500ms
                }

                public function slowRequest2() {
                    usleep(250 * 1000); // 500ms
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="slowRequest" dusk="slow-request">Slow request</button>
                    <button wire:click="slowRequest2" dusk="slow-request2">Slow request 2</button>
                </div>
                HTML; }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertAttributeMissing('@slow-request', 'data-loading')
        ->assertAttributeMissing('@slow-request2', 'data-loading')

        ->click('@slow-request')

        // Wait for the first request to start...
        ->pause(10)
        ->assertAttribute('@slow-request', 'data-loading', 'true')
        ->assertAttributeMissing('@slow-request2', 'data-loading')

        // Wait for the first request to start...
        ->pause(50)

        // Trigger the second request...
        ->click('@slow-request2')

        // Pause for a moment to ensure Livewire has removed the attribute...
        ->pause(300)
        ->assertAttributeMissing('@slow-request', 'data-loading')
        ->assertAttribute('@slow-request2', 'data-loading', 'true')
        ;
    }

    public function test_data_loading_attribute_is_removed_from_an_element_when_its_request_is_cancelled()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    usleep(400 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="slow-request">Slow Request</button>
                    </div>

                    @script
                    <script>
                        this.intercept(({ actions, cancel }) => {
                            setTimeout(() => cancel(), 50)
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertAttributeMissing('@slow-request', 'data-loading')

        ->click('@slow-request')

        // Wait for the request to start...
        ->pause(10)
        ->assertAttribute('@slow-request', 'data-loading', 'true')

        // The interceptor cancels the request after 200ms...
        ->pause(50)
        ->assertAttributeMissing('@slow-request', 'data-loading')
        ;
    }

    public function test_data_loading_attribute_is_not_added_to_poll_directives()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function hydrate() {
                    usleep(250 * 1000); // 250ms
                }
                public function render() { return <<<'HTML'
                <div wire:poll.500ms dusk="container">
                    <div wire:loading dusk="loading">Loading...</div>
                </div>
                HTML; }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertAttributeMissing('@container', 'data-loading')

        ->waitForText('Loading...')
        ->assertAttributeMissing('@container', 'data-loading')
        ;
    }
}
