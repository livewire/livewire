<?php

namespace Livewire\Features\SupportRequestInteractions;

use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_a_new_component_level_user_action_does_not_cancel_an_old_component_level_user_action_for_the_same_component_it_is_instead_queued_for_execution_after_the_old_action()
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`))
                            onCancel(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`))
                            onSuccess(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`))
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
                'firstRequest-component succeeded',
                'secondRequest-component started',
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
                    usleep(500 * 1000); // Keep the poll in flight while Dusk observes it...
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`))
                            onCancel(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`))
                            onSuccess(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->waitUntil("window.intercepts.includes('pollRequest-component sent')")
            ->assertScript('window.intercepts', [
                'pollRequest-component started',
                'pollRequest-component sent',
            ])

            ->click('@user-request')
            ->waitUntil('window.intercepts.length >= 6')
            ->assertScript('window.intercepts.slice(0, 6)', [
                'pollRequest-component started',
                'pollRequest-component sent',
                'pollRequest-component cancelled',
                'userRequest-component started',
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`))
                            onCancel(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`))
                            onSuccess(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`))
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
            ->pause(400)
            ->assertScript('window.intercepts.length', 3)
            ->assertScript('window.intercepts', [
                'userRequest-component started',
                'userRequest-component sent',
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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
            ->pause(300)
            ->assertScript('window.intercepts.length', 3)
            ->assertScript('window.intercepts', [
                'firstPollRequest-component started',
                'firstPollRequest-component sent',
                'firstPollRequest-component succeeded',
            ])
            ;
    }

    public function test_a_new_island_level_user_action_does_not_cancel_an_old_island_level_user_action_for_the_same_island_it_is_instead_queued_for_execution_after_the_old_action()
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`))
                            onCancel(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`))
                            onSuccess(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`))
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
                'firstRequest-foo succeeded',
                'secondRequest-foo started',
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
                    usleep(500 * 1000); // Keep the poll in flight while Dusk observes it...
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`))
                            onCancel(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`))
                            onSuccess(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`))
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        )
            ->waitForLivewireToLoad()

            ->waitUntil("window.intercepts.includes('pollRequest-foo sent')")
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            ->click('@user-request')
            ->waitUntil('window.intercepts.length >= 6')
            ->assertScript('window.intercepts.slice(0, 6)', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'pollRequest-foo cancelled',
                'userRequest-foo started',
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`))
                            onCancel(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`))
                            onSuccess(() => window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`))
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
            ->pause(400)
            ->assertScript('window.intercepts.length', 3)
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
                'userRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_new_island_level_poll_directive_action_does_not_cancel_an_old_island_level_poll_directive_action_instead_it_is_cancelled_for_the_same_island()
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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
            ->pause(300)
            ->assertScript('window.intercepts.length', 3)
            ->assertScript('window.intercepts', [
                'firstPollRequest-foo started',
                'firstPollRequest-foo sent',
                'firstPollRequest-foo succeeded',
            ])
            ;
    }

    public function test_a_island_level_user_action_does_not_cancel_a_component_level_user_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function userRequest() {
                    usleep(500 * 1000); // Keep both requests in flight while Dusk observes them...
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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

            ->waitUntil("window.intercepts.includes('userRequest-component sent')")
            ->assertScript('window.intercepts', [
                'userRequest-component started',
                'userRequest-component sent',
            ])

            ->click('@island-request')
            ->waitUntil('window.intercepts.length >= 6')
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
                    usleep(500 * 1000); // Keep both requests in flight while Dusk observes them...
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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

            ->waitUntil("window.intercepts.includes('userRequest-foo sent')")
            ->assertScript('window.intercepts', [
                'userRequest-foo started',
                'userRequest-foo sent',
            ])

            ->click('@component-request')
            ->waitUntil('window.intercepts.length >= 6')
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
                    usleep(500 * 1000); // Keep both polls in flight while Dusk observes them...
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.700ms="pollRequest">
                        @island('foo')
                            <div wire:poll.750ms="pollRequest">
                                Island content
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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

            ->waitUntil("window.intercepts.includes('pollRequest-component succeeded') && window.intercepts.includes('pollRequest-foo succeeded')")
            ->assertScript('window.intercepts.slice(0, 4)', [
                'pollRequest-component started',
                'pollRequest-component sent',
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])
            ->assertScript("window.intercepts.some(event => event.endsWith('cancelled'))", false)
            ;
    }

    public function test_a_component_level_poll_directive_action_does_not_cancel_a_island_level_poll_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(500 * 1000); // Keep both polls in flight while Dusk observes them...
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.750ms="pollRequest">
                        @island('foo')
                            <div wire:poll.700ms="pollRequest">
                                Island content
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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

            ->waitUntil("window.intercepts.includes('pollRequest-foo succeeded') && window.intercepts.includes('pollRequest-component succeeded')")
            ->assertScript('window.intercepts.slice(0, 4)', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
                'pollRequest-component started',
                'pollRequest-component sent',
            ])
            ->assertScript("window.intercepts.some(event => event.endsWith('cancelled'))", false)
            ;
    }

    public function test_a_island_level_user_directive_action_does_not_cancel_a_component_level_poll_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(500 * 1000); // Keep the poll in flight while Dusk starts the user request...
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div wire:poll.500ms="pollRequest">
                        @island('foo')
                            <div>
                                <button wire:click="userRequest" dusk="island-request">Island Request</button>
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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

            // Wait for the component poll to have fired (started + sent)...
            ->waitUntil('window.intercepts.length >= 2')

            // Start the island user request...
            ->click('@island-request')

            // Wait for everything to settle: both requests started, sent, and succeeded.
            // The full ordered sequence asserts that neither cancelled the other...
            ->waitUntil('window.intercepts.length >= 6')
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
                    usleep(500 * 1000); // Keep the poll in flight while Dusk starts the user request...
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="userRequest" dusk="component-request">Component Request</button>
                        @island('foo')
                            <div wire:poll.500ms="pollRequest">
                                Island content
                            </div>
                        @endisland
                    </div
                    @script
                    <script>
                        window.intercepts = []

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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

            ->waitUntil("window.intercepts.includes('pollRequest-foo sent')")
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            // Start the component user request...
            ->click('@component-request')
            ->waitUntil('window.intercepts.length >= 6')
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
                    usleep(500 * 1000); // Keep the poll in flight while Dusk starts the user request...
                }

                public function userRequest() {
                    // Don't sleep the user request...
                }

                public function render() {
                    return <<<'HTML'
                    <div>

                        @island('foo')
                            <div wire:poll.500ms="pollRequest">
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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

            ->waitUntil("window.intercepts.includes('pollRequest-foo sent')")
            ->assertScript('window.intercepts', [
                'pollRequest-foo started',
                'pollRequest-foo sent',
            ])

            // Start the bar island user request...
            ->click('@bar-island-request')
            ->waitUntil('window.intercepts.length >= 6')
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

    public function test_headers_added_via_request_hook_are_sent_with_requests()
    {
        Livewire::visit(new class extends Component {
            public $customHeader = 'none';

            function captureHeader()
            {
                $this->customHeader = request()->header('X-Custom-Test-Header', 'missing');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="header">{{ $customHeader }}</span>

                    <button wire:click="captureHeader" dusk="button">Capture</button>

                    <script>
                        document.addEventListener('livewire:init', () => {
                            Livewire.hook('request', ({ options }) => {
                                options.headers['X-Custom-Test-Header'] = 'test-header-value'
                            })
                        })
                    </script>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@header', 'none')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@header', 'test-header-value');
    }

    public function test_a_island_level_poll_directive_action_does_not_cancel_another_island_level_user_directive_action_for_the_same_component()
    {
        Livewire::visit(
            new class extends Component {
                public function pollRequest() {
                    usleep(1000 * 1000); // Keep the poll in flight until the user request overlaps it...
                }

                public function userRequest() {
                    usleep(1000 * 1000); // Keep the user request in flight until the poll overlaps it...
                }

                public function render() {
                    return <<<'HTML'
                    <div>

                        @island('foo')
                            <div wire:poll.600ms="pollRequest">
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

                        this.interceptMessage(({ message, onSend, onCancel, onSuccess }) => {
                            let action = [...message.actions][0]

                            window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} started`)

                            onSend(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} sent`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onCancel(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} cancelled`)
                                console.log(JSON.stringify(window.intercepts))
                            })
                            onSuccess(() => {
                                window.intercepts.push(`${action.name}-${action.metadata.island?.name || 'component'} succeeded`)
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

            // Start the bar island user request...
            ->click('@bar-island-request')
            ->waitUntil("window.intercepts.includes('userRequest-bar sent')")
            ->assertScript('window.intercepts', [
                'userRequest-bar started',
                'userRequest-bar sent',
            ])

            ->waitUntil('window.intercepts.length >= 6')
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
