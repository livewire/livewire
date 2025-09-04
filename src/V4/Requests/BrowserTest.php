<?php

namespace Livewire\V4\Requests;

use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_a_new_component_level_user_action_cancels_an_old_component_level_user_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function firstRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function secondRequest() {
                    // Don't sleep the second request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="firstRequest" dusk="first-request">First Request</button>
                        <button wire:click="secondRequest" dusk="second-request">Second Request</button>
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`))
                            request.onCancel(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`))
                            request.onSuccess(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->click('@first-request')

            // Wait for the first request to have started before checking the intercepts...
            ->pause(10)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'firstRequest-component started',
                'firstRequest-component sent',
            ])

            ->waitForLivewire()->click('@second-request')
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'firstRequest-component started',
                'firstRequest-component sent',
                'secondRequest-component started',
                'firstRequest-component cancelled',
                'secondRequest-component sent',
                'secondRequest-component succeeded',
            ])
            ;
    }

    public function test_a_new_component_level_user_action_cancels_an_old_component_level_poll_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(100 * 1000); // 100ms
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.200ms="pollRequest">
                        <button wire:click="userRequest" dusk="user-request">User Request</button>
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`))
                            request.onCancel(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`))
                            request.onSuccess(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the poll to have started..
            ->pause(210)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
            ])

            ->waitForLivewire()->click('@user-request')
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
                'userRequest-component started',
                'pollRequest-component cancelled',
                'userRequest-component sent',
                'userRequest-component succeeded',
            ])
            ;
    }

    public function test_a_new_component_level_poll_action_does_not_cancel_an_old_component_level_user_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    // Don't sleep the poll request...
                }

                public function userRequest() {
                    usleep(200 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.400ms="pollRequest">
                        <button wire:click="userRequest" dusk="user-request">User Request</button>
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`))
                            request.onCancel(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`))
                            request.onSuccess(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->pause(250)
            ->click('@user-request')

            // Wait for the user request to have started...
            ->pause(10)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'userRequest-component started',
                'userRequest-component sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for the poll to have started and be cancelled, and then the user request to finish..
            ->pause(250)
            ->assertScript('window.intercepts.length', 5)
            ->assertScript('window.intercepts', [
                'userRequest-component started',
                'userRequest-component sent',
                'pollRequest-component started',
                'pollRequest-component cancelled',
                'userRequest-component succeeded',
            ])
            ;
    }

    public function test_a_new_component_level_poll_action_does_not_cancel_an_old_component_level_poll_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function firstPollRequest() {
                    usleep(200 * 1000); // 500ms
                }

                public function secondPollRequest() {
                    // Don't sleep the second poll request...
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.400ms="firstPollRequest">
                        <div wire:poll.500ms="secondPollRequest"></div>
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the first poll request to have started...
            ->pause(410)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'firstPollRequest-component started',
                'firstPollRequest-component sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for the second poll to have started and be cancelled, and then the first poll request to finish..
            ->pause(250)
            ->assertScript('window.intercepts.length', 5)
            ->assertScript('window.intercepts', [
                'firstPollRequest-component started',
                'firstPollRequest-component sent',
                'secondPollRequest-component started',
                'secondPollRequest-component cancelled',
                'firstPollRequest-component succeeded',
            ])
            ;
    }

    // Do islands for the 4 tests above...

    public function test_a_new_island_level_user_action_cancels_an_old_island_level_user_action_for_the_same_island()
    {
        Livewire::visit(
            new class extends Component {
                public function firstRequest() {
                    usleep(500 * 1000); // 500ms
                }

                public function secondRequest() {
                    // Don't sleep the second request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        @island('foo')
                            <button wire:click="firstRequest" dusk="first-request">First Request</button>
                            <button wire:click="secondRequest" dusk="second-request">Second Request</button>
                        @endisland
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`))
                            request.onCancel(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`))
                            request.onSuccess(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->click('@first-request')

            // Wait for the first request to have started before checking the intercepts...
            ->pause(10)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'firstRequest-foo started',
                'firstRequest-foo sent',
            ])

            ->waitForLivewire()->click('@second-request')
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'firstRequest-foo started',
                'firstRequest-foo sent',
                'secondRequest-foo started',
                'firstRequest-foo cancelled',
                'secondRequest-foo sent',
                'secondRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_new_island_level_user_action_cancels_an_old_island_level_poll_directive_action_for_the_same_island()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(100 * 1000); // 100ms
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        @island('foo')
                            <div wire:poll.200ms="pollRequest">
                                <button wire:click="userRequest" dusk="user-request">User Request</button>
                            </div>
                        @endisland
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`))
                            request.onCancel(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`))
                            request.onSuccess(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the poll to have started..
            ->pause(210)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            ->waitForLivewire()->click('@user-request')
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'userRequest-foo started',
                'pollRequest-foo cancelled',
                'userRequest-foo sent',
                'userRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_new_island_level_user_action_cancels_an_old_island_level_poll_parameter_action_for_the_same_island()
    {
        Livewire::visit(
            new class extends Component {
                // Need to use hydrate because we can't target a method for polling...
                public function hydrate() {
                    usleep(100 * 1000); // 100ms
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        @island('foo', poll: '400ms')
                            <div>
                                <button wire:click="userRequest" dusk="user-request">User Request</button>
                            </div>
                        @endisland
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`))
                            request.onCancel(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`))
                            request.onSuccess(() => window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the poll to have started..
            ->pause(410)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                '$refresh-foo started',
                '$refresh-foo sent',
            ])

            ->waitForLivewire()->click('@user-request')
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                '$refresh-foo started',
                '$refresh-foo sent',
                'userRequest-foo started',
                '$refresh-foo cancelled',
                'userRequest-foo sent',
                'userRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_new_island_level_poll_directive_action_does_not_cancel_an_old_island_level_user_action_for_the_same_island()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    // Don't sleep the poll request...
                }

                public function userRequest() {
                    usleep(200 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        @island('foo')
                            <div wire:poll.400ms="pollRequest">
                                <button wire:click="userRequest" dusk="user-request">User Request</button>
                            </div>
                        @endisland
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name} started`)

                            request.afterSend(() => window.intercepts.push(`${action.method}-${action.context.island?.name} sent`))
                            request.onCancel(() => window.intercepts.push(`${action.method}-${action.context.island?.name} cancelled`))
                            request.onSuccess(() => window.intercepts.push(`${action.method}-${action.context.island?.name} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->pause(250)
            ->click('@user-request')

            // Wait for the user request to have started...
            ->pause(10)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for the poll to have started and be cancelled, and then the user request to finish..
            ->pause(250)
            ->assertScript('window.intercepts.length', 5)
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
                'pollRequest-foo started',
                'pollRequest-foo cancelled',
                'userRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_new_island_level_poll_parameter_action_does_not_cancel_an_old_island_level_user_action_for_the_same_island()
    {
        Livewire::visit(
            new class extends Component {
                // Need to use hydrate because we can't target a method for polling...
                public function hydrate() {
                    // Don't sleep the hydrate request...
                }

                public function userRequest() {
                    usleep(200 * 1000); // 500ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        @island('foo', poll: '400ms')
                            <div>
                                <button wire:click="userRequest" dusk="user-request">User Request</button>
                            </div>
                        @endisland
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name} started`)

                            request.afterSend(() => window.intercepts.push(`${action.method}-${action.context.island?.name} sent`))
                            request.onCancel(() => window.intercepts.push(`${action.method}-${action.context.island?.name} cancelled`))
                            request.onSuccess(() => window.intercepts.push(`${action.method}-${action.context.island?.name} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->pause(250)
            ->click('@user-request')

            // Wait for the user request to have started...
            ->pause(10)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for the poll to have started and be cancelled, and then the user request to finish..
            ->pause(250)
            ->assertScript('window.intercepts.length', 5)
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
                '$refresh-foo started',
                '$refresh-foo cancelled',
                'userRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_new_component_level_poll_directive_action_does_not_cancel_an_old_component_level_poll_directive_action_instead_it_is_cancelled_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function firstPollRequest() {
                    usleep(200 * 1000); // 500ms
                }

                public function secondPollRequest() {
                    // Don't sleep the second poll request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        @island('foo')
                            <div wire:poll.400ms="firstPollRequest">
                                <div wire:poll.500ms="secondPollRequest"></div>
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the first poll request to have started...
            ->pause(410)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'firstPollRequest-foo started',
                'firstPollRequest-foo sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for the second poll to have started and be cancelled, and then the first poll request to finish..
            ->pause(250)
            ->assertScript('window.intercepts.length', 5)
            ->assertScript('window.intercepts', [
                'firstPollRequest-foo started',
                'firstPollRequest-foo sent',
                'secondPollRequest-foo started',
                'secondPollRequest-foo cancelled',
                'firstPollRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_new_component_level_poll_parameter_action_does_not_cancel_an_old_component_level_poll_parameter_action_instead_it_is_cancelled_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                // Need to use hydrate because we can't target a method for polling...
                public function hydrate() {
                    usleep(250 * 1000); // 250ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        @island('foo', poll: '200ms')
                            <div>
                                Island content
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []
                        window.count = 0

                        this.intercept(({ action, request}) => {
                            window.count++

                            // Use a local count so that the count is not shared between interceptors
                            let count = window.count

                            window.intercepts.push(`${action.method}-${count}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${count}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${count}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${count}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the first poll request to have started...
            ->pause(210)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                '$refresh-1-foo started',
                '$refresh-1-foo sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for the second poll to have started and be cancelled, and then the first poll request to finish..
            ->pause(250)
            ->assertScript('window.intercepts.length', 5)
            ->assertScript('window.intercepts', [
                '$refresh-1-foo started',
                '$refresh-1-foo sent',
                '$refresh-2-foo started',
                '$refresh-2-foo cancelled',
                '$refresh-1-foo succeeded',
            ])
            ;
    }

    public function test_a_island_level_user_action_does_not_cancel_a_component_level_user_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function userRequest() {
                    usleep(150 * 1000); // 150ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="userRequest" dusk="component-request">Component Request</button>
                        @island('foo')
                            <button wire:click="userRequest" dusk="island-request">Island Request</button>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->click('@component-request')

            // Wait for the user request to have started...
            ->pause(50)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'userRequest-component started',
                'userRequest-component sent',
            ])

            ->click('@island-request')
            // Wait for the island request to have started...
            ->pause(50)
            ->assertScript('window.intercepts.length', 4)
            ->assertScript('window.intercepts', [
                'userRequest-component started',
                'userRequest-component sent',
                'userRequest-foo started',
                'userRequest-foo sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for both requests to have finished...
            ->pause(200)
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'userRequest-component started',
                'userRequest-component sent',
                'userRequest-foo started',
                'userRequest-foo sent',
                'userRequest-component succeeded',
                'userRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_component_level_user_action_does_not_cancel_a_island_level_user_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function userRequest() {
                    usleep(150 * 1000); // 150ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="userRequest" dusk="component-request">Component Request</button>
                        @island('foo')
                            <button wire:click="userRequest" dusk="island-request">Island Request</button>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->click('@island-request')

            // Wait for the island request to have started...
            ->pause(50)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
            ])

            ->click('@component-request')
            // Wait for the user request to have started...
            ->pause(50)
            ->assertScript('window.intercepts.length', 4)
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
                'userRequest-component started',
                'userRequest-component sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for both requests to have finished...
            ->pause(200)
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
                'userRequest-component started',
                'userRequest-component sent',
                'userRequest-foo succeeded',
                'userRequest-component succeeded',
            ])
            ;
    }

    public function test_a_island_level_poll_directive_action_does_not_cancel_a_component_level_poll_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(150 * 1000); // 150ms
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.400ms="pollRequest">
                        @island('foo')
                            <div wire:poll.500ms="pollRequest">
                                Island content
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the component poll to have started...
            ->pause(410)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
            ])

            // Wait for the island poll to have started...
            ->pause(100)
            ->assertScript('window.intercepts.length', 4)
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for both requests to have finished...
            ->pause(200)
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'pollRequest-component succeeded',
                'pollRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_component_level_poll_directive_action_does_not_cancel_a_island_level_poll_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(150 * 1000); // 150ms
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.500ms="pollRequest">
                        @island('foo')
                            <div wire:poll.400ms="pollRequest">
                                Island content
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the island poll to have started...
            ->pause(410)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            // Wait for the component poll to have started...
            ->pause(100)
            ->assertScript('window.intercepts.length', 4)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'pollRequest-component started',
                'pollRequest-component sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for both requests to have finished...
            ->pause(200)
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'pollRequest-component started',
                'pollRequest-component sent',
                'pollRequest-foo succeeded',
                'pollRequest-component succeeded',
            ])
            ;
    }

    public function test_a_island_level_user_directive_action_does_not_cancel_a_component_level_poll_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(150 * 1000); // 150ms
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.400ms="pollRequest">
                        @island('foo')
                            <div>
                                <button wire:click="userRequest" dusk="island-request">Island Request</button>
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the component poll to have started...
            ->pause(410)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
            ])

            // Start the island user request...
            ->click('@island-request')
            // Wait for the island user request to have started...
            ->pause(50)
            ->assertScript('window.intercepts.length', 4)
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
                'userRequest-foo started',
                'userRequest-foo sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for both requests to have finished...
            ->pause(150)
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
                'userRequest-foo started',
                'userRequest-foo sent',
                'pollRequest-component succeeded',
                'userRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_component_level_user_directive_action_does_not_cancel_a_island_level_poll_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(150 * 1000); // 150ms
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="userRequest" dusk="component-request">Component Request</button>
                        @island('foo')
                            <div wire:poll.400ms="pollRequest">
                                Island content
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the island poll to have started...
            ->pause(410)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            // Start the component user request...
            ->click('@component-request')
            // Wait for the component user request to have started...
            ->pause(50)
            ->assertScript('window.intercepts.length', 4)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'userRequest-component started',
                'userRequest-component sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for both requests to have finished...
            ->pause(150)
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'userRequest-component started',
                'userRequest-component sent',
                'pollRequest-foo succeeded',
                'userRequest-component succeeded',
            ])
            ;
    }

    public function test_a_island_level_user_directive_action_does_not_cancel_another_island_level_poll_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(150 * 1000); // 150ms
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>

                        @island('foo')
                            <div wire:poll.400ms="pollRequest">
                                Island content
                            </div>
                        @endisland

                        @island('bar')
                            <div>
                                <button wire:click="userRequest" dusk="bar-island-request">Bar Island Request</button>
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait for the foo island poll to have started...
            ->pause(410)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            // Start the bar island user request...
            ->click('@bar-island-request')
            // Wait for the bar island user request to have started...
            ->pause(50)
            ->assertScript('window.intercepts.length', 4)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'userRequest-bar started',
                'userRequest-bar sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for both requests to have finished...
            ->pause(150)
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'userRequest-bar started',
                'userRequest-bar sent',
                'pollRequest-foo succeeded',
                'userRequest-bar succeeded',
            ])
            ;
    }

    public function test_a_island_level_poll_directive_action_does_not_cancel_another_island_level_user_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(200 * 1000); // 200ms
                }

                public function userRequest() {
                    usleep(200 * 1000); // 200ms
                }

                public function render() {
                    return <<<'HTML'
                    <div>

                        @island('foo')
                            <div wire:poll.450ms="pollRequest">
                                Island content
                            </div>
                        @endisland

                        @island('bar')
                            <div>
                                <button wire:click="userRequest" dusk="bar-island-request">Bar Island Request</button>
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept(({ action, request}) => {
                            window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} started`)

                            request.afterSend(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onCancel(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            request.onSuccess(() => {
                                window.intercepts.push(`${action.method}-${action.context.island?.name || 'component'} succeeded`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            // Wait a bit before starting the bar island user request, so the poll happens soon after...
            ->pause(300)

            // Start the bar island user request...
            ->click('@bar-island-request')
            // Wait for the bar island user request to have started...
            ->pause(50)
            ->assertScript('window.intercepts.length', 2)
            ->assertScript('window.intercepts', [
                'userRequest-bar started',
                'userRequest-bar sent',
            ])

            // Wait for the foo island poll to have started...
            ->pause(110)
            ->assertScript('window.intercepts.length', 4)
            ->assertScript('window.intercepts', [
                'userRequest-bar started',
                'userRequest-bar sent',
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            // Timing is essential in this test as dusk is single threaded, so even if a request is cancelled,
            // the server will still handle it and take however long it needs. So we need to calculate the
            // time it takes for the first request to finished as if it was successful, plus the time for
            // the second request...

            // Wait for both requests to have finished...
            ->pause(300)
            ->assertScript('window.intercepts.length', 6)
            ->assertScript('window.intercepts', [
                'userRequest-bar started',
                'userRequest-bar sent',
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'userRequest-bar succeeded',
                'pollRequest-foo succeeded',
            ])
            ;
    }
}
