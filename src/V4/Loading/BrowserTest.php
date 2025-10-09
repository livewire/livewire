<?php

namespace Livewire\V4\Loading;

use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    // A component can show loading
    // An island can show loading
    // An island and component can show different loading states
    // A component can show targeted loading
    // An island can show targeted loading
    // A second component request doesn't cancel loading
    // A second island request doesn't cancel loading
    // A second component request with a different target cancels loading
    // A second island request with a different target cancels loading
    // A cancelled component request cancels loading
    // A cancelled island request cancels loading

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
                        this.intercept(({ cancel }) => {
                            setTimeout(() => {
                                cancel();
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
                        this.intercept(({ cancel }) => {
                            setTimeout(() => {
                                cancel();
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
