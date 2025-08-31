<?php

namespace Livewire\V4\DataLoading;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_data_loading_attribute_is_added_to_an_element_when_it_triggers_a_request()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
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
        ->assertAttribute('@refresh1', 'data-loading', 'true')
        ->assertAttributeMissing('@refresh2', 'data-loading')

        // Wait for the first request to finish...
        ->pause(100)

        ->click('@refresh2')
        ->assertAttributeMissing('@refresh1', 'data-loading')
        ->assertAttribute('@refresh2', 'data-loading', 'true')

        // Wait for the second request to finish...
        ->pause(100)

        ->assertAttributeMissing('@refresh1', 'data-loading')
        ->assertAttributeMissing('@refresh2', 'data-loading')
        ;
    }

    public function test_data_loading_attribute_is_removed_from_an_element_when_the_request_is_cancelled()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    sleep(1);
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="slowRequest" dusk="slow-request">Slow request</button>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>
                </div>
                HTML; }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertAttributeMissing('@slow-request', 'data-loading')
        ->assertAttributeMissing('@refresh', 'data-loading')

        ->click('@slow-request')
        ->assertAttribute('@slow-request', 'data-loading', 'true')
        ->assertAttributeMissing('@refresh', 'data-loading')

        // Wait for the first request to start...
        ->pause(50)

        // Trigger the refresh request...
        ->click('@refresh')

        // Pause for a moment to ensure Livewire has removed the attribute...
        ->pause(5)
        ->assertAttributeMissing('@slow-request', 'data-loading')
        ->assertAttribute('@refresh', 'data-loading', 'true')
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
