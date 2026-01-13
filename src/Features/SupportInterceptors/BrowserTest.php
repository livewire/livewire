<?php

namespace Livewire\Features\SupportInterceptors;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_a_global_interceptor_can_be_registered()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="refresh">Refresh</button>
                        <livewire:child />
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        Livewire.interceptMessage(() => {
                            window.intercepts.push('intercept')
                            console.log('intercept', window.intercepts)
                        })
                    </script>
                    @endscript
                    HTML;
                }
            },
            'child' => new class extends \Livewire\Component {
                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="child-refresh">Child Refresh</button>
                    </div>
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertScript('window.intercepts.length', 0)
        ->waitForLivewire()->click('@refresh')
        ->assertScript('window.intercepts.length', 1)
        ->assertScript('window.intercepts[0]', 'intercept')
        ->waitForLivewire()->click('@child-refresh')
        ->assertScript('window.intercepts.length', 2)
        ->assertScript('window.intercepts[1]', 'intercept')
        ;
    }

    public function test_a_component_interceptor_can_be_registered()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="refresh">Refresh</button>
                        <livewire:child />
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.interceptMessage(() => {
                            window.intercepts.push('intercept')
                            console.log('intercept', window.intercepts)
                        })
                    </script>
                    @endscript
                    HTML;
                }
            },
            'child' => new class extends \Livewire\Component {
                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="child-refresh">Child Refresh</button>
                    </div>
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertScript('window.intercepts.length', 0)
        ->waitForLivewire()->click('@refresh')
        ->assertScript('window.intercepts.length', 1)
        ->assertScript('window.intercepts[0]', 'intercept')
        ->waitForLivewire()->click('@child-refresh')
        // The child component should not have been intercepted...
        ->assertScript('window.intercepts.length', 1)
        ->assertScript('window.intercepts[0]', 'intercept')
        ;
    }

    public function test_an_action_scoped_component_interceptor_can_be_registered()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function doSomething() {}

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="refresh">Refresh</button>
                        <button wire:click="doSomething" dusk="do-something">Do Something</button>
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.intercept('doSomething', () => {
                            window.intercepts.push('intercept')
                            console.log('intercept', window.intercepts)
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertScript('window.intercepts.length', 0)

        // The interceptor should not be triggered when the component is refreshed...
        ->waitForLivewire()->click('@refresh')
        ->assertScript('window.intercepts.length', 0)

        // The interceptor should be triggered when the action is performed...
        ->waitForLivewire()->click('@do-something')
        ->assertScript('window.intercepts.length', 1)
        ->assertScript('window.intercepts[0]', 'intercept')
        ;
    }

    public function test_an_interceptor_can_have_multiple_callbacks()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    sleep(1);
                }

                public function throwAnError() {
                    throw new \Exception('Test error');
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="refresh">Refresh</button>
                        <button wire:click="slowRequest" dusk="slow-request">Slow Request</button>
                        <button wire:click="throwAnError" dusk="throw-error">Throw Error</button>
                    </div>
                    @script
                    <script>
                        window.intercepts = []

                        this.interceptMessage(({ message, onSend, onCancel, onError, onSuccess }) => {
                            let action = [...message.actions][0]
                            let method = action.name
                            let directive = action.origin.directive

                            window.intercepts.push(`onInit-${directive.method}`)

                            onSend(() => {
                                window.intercepts.push(`onSend-${directive.method}`)
                            })

                            onCancel(() => {
                                window.intercepts.push(`onCancel-${directive.method}`)
                            })

                            onError(() => {
                                window.intercepts.push(`onError-${directive.method}`)
                            })

                            onSuccess(({ onSync, onMorph, onRender }) => {
                                window.intercepts.push(`onSuccess-${directive.method}`)

                                onSync(() => {
                                    window.intercepts.push(`onSync-${directive.method}`)
                                })

                                onMorph(() => {
                                    window.intercepts.push(`onMorph-${directive.method}`)
                                })

                                onRender(() => {
                                    window.intercepts.push(`onRender-${directive.method}`)
                                })
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertScript('window.intercepts.length', 0)

        // The interceptor should not be triggered when the component is refreshed...
        ->waitForLivewire()->click('@refresh')
        ->assertScript('window.intercepts.length', 6)
        ->assertScript('window.intercepts', [
            'onInit-$refresh',
            'onSend-$refresh',
            'onSuccess-$refresh',
            'onSync-$refresh',
            'onMorph-$refresh',
            'onRender-$refresh',
        ])

        // Reset...
        ->tap(fn ($b) => $b->script('window.intercepts = []'))

        // Next we will test the cancel interceptor...

        // Trigger the slow request...
        ->click('@slow-request')
        // Wait for a moment, then trigger another request...
        ->pause(100)
        ->waitForLivewire()->click('@refresh')
        ->assertScript('window.intercepts.length', 12)
        // The below results are the combination of the slow request and the refresh request...
        ->assertScript('window.intercepts', [
            'onInit-slowRequest',
            'onSend-slowRequest',
            'onSuccess-slowRequest',
            'onSync-slowRequest',
            'onMorph-slowRequest',
            'onRender-slowRequest',
            'onInit-$refresh',
            'onSend-$refresh',
            'onSuccess-$refresh',
            'onSync-$refresh',
            'onMorph-$refresh',
            'onRender-$refresh',
        ])

        // Reset...
        ->tap(fn ($b) => $b->script('window.intercepts = []'))

        // Next we will test the error interceptor...

        // Trigger the error request...
        ->waitForLivewire()->click('@throw-error')
        ->assertScript('window.intercepts.length', 3)
        ->assertScript('window.intercepts', [
            'onInit-throwAnError',
            'onSend-throwAnError',
            'onError-throwAnError',
        ])
        ;
    }

    public function test_an_interceptor_can_cancel_a_message_before_it_is_sent()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    sleep(1);
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="slow-request">Slow Request</button>
                    </div>

                    @script
                    <script>
                        window.intercepts = []

                        this.interceptMessage(({ message, cancel, onSend, onCancel, onError, onSuccess }) => {
                            let action = [...message.actions][0]
                            let method = action.name
                            let directive = action.origin.directive

                            window.intercepts.push(`onInit-${directive.method}`)

                            onCancel(() => {
                                window.intercepts.push(`onCancel-${directive.method}`)
                            })

                            cancel()

                            onSend(() => {
                                window.intercepts.push(`onSend-${directive.method}`)
                            })

                            onError(() => {
                                window.intercepts.push(`onError-${directive.method}`)
                            })

                            onSuccess(({ onSync, onMorph, onRender }) => {
                                window.intercepts.push(`onSuccess-${directive.method}`)

                                onSync(() => {
                                    window.intercepts.push(`onSync-${directive.method}`)
                                })

                                onMorph(() => {
                                    window.intercepts.push(`onMorph-${directive.method}`)
                                })

                                onRender(() => {
                                    window.intercepts.push(`onRender-${directive.method}`)
                                })
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertScript('window.intercepts.length', 0)

        // The interceptor has a timeout set to cancel the request after 200ms...
        ->click('@slow-request')
        // Wait for the requests to be corralled...
        ->pause(250)
        ->assertScript('window.intercepts.length', 2)
        ->assertScript('window.intercepts', [
            'onInit-slowRequest',
            'onCancel-slowRequest',
        ])
        ;
    }

    public function test_an_interceptor_can_cancel_a_message_request_while_in_flight()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function slowRequest() {
                    sleep(1);
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="slowRequest" dusk="slow-request">Slow Request</button>
                    </div>

                    @script
                    <script>
                        window.intercepts = []

                        this.interceptMessage(({ message, cancel, onSend, onCancel, onError, onSuccess }) => {
                            let action = [...message.actions][0]
                            let method = action.name
                            let directive = action.origin.directive

                            window.intercepts.push(`onInit-${directive.method}`)

                            setTimeout(() => cancel(), 200)

                            onSend(() => {
                                window.intercepts.push(`onSend-${directive.method}`)
                            })

                            onCancel(() => {
                                window.intercepts.push(`onCancel-${directive.method}`)
                            })

                            onError(() => {
                                window.intercepts.push(`onError-${directive.method}`)
                            })

                            onSuccess(({ onSync, onMorph, onRender }) => {
                                window.intercepts.push(`onSuccess-${directive.method}`)

                                onSync(() => {
                                    window.intercepts.push(`onSync-${directive.method}`)
                                })

                                onMorph(() => {
                                    window.intercepts.push(`onMorph-${directive.method}`)
                                })

                                onRender(() => {
                                    window.intercepts.push(`onRender-${directive.method}`)
                                })
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertScript('window.intercepts.length', 0)

        ->click('@slow-request')
        // The interceptor has a timeout set to cancel the request after 200ms...
        ->pause(250)
        ->assertScript('window.intercepts.length', 3)
        ->assertScript('window.intercepts', [
            'onInit-slowRequest',
            'onSend-slowRequest',
            'onCancel-slowRequest',
        ])
        ;
    }

    public function test_a_redirect_can_be_intercepted_and_prevented()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function redirectToWebsite()
                {
                    $this->redirect('https://google.com');
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="redirectToWebsite" dusk="redirect-to-website">Redirect to Website</button>
                    </div>
                    @script
                    <script>
                        window.stopRedirect = true

                        Livewire.interceptRequest(({ onRedirect }) => {
                            onRedirect(({ url, preventDefault }) => {
                                if (window.stopRedirect) {
                                    preventDefault()
                                    window.stopRedirect = false
                                }
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                    }
                }
            ])
            ->waitForLivewireToLoad()
            ->waitForLivewire()->click('@redirect-to-website')
            ->assertHostIsNot('www.google.com')
            ->waitForLivewire()->click('@redirect-to-website')
            ->assertHostIs('www.google.com')
            ;
    }

    public function test_action_interceptors_can_hook_into_action_lifecycle()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public $counter = 0;

                public function increment()
                {
                    $this->counter++;
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="increment" dusk="increment">Increment</button>
                        <span dusk="counter">{{ $counter }}</span>
                    </div>
                    @script
                    <script>
                        window.interceptLogs = []

                        Livewire.interceptAction(({ action, onSend, onSuccess, onFinish }) => {
                            window.interceptLogs.push('intercept:' + action.name)

                            onSend(() => {
                                window.interceptLogs.push('onSend:' + action.name)
                            })

                            onSuccess((result) => {
                                window.interceptLogs.push('onSuccess:' + action.name)
                            })

                            onFinish(() => {
                                window.interceptLogs.push('onFinish:' + action.name)
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertScript('window.interceptLogs.length', 0)
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@counter', '1')
        ->assertScript('window.interceptLogs.length', 4)
        ->assertScript('window.interceptLogs[0]', 'intercept:increment')
        ->assertScript('window.interceptLogs[1]', 'onSend:increment')
        ->assertScript('window.interceptLogs[2]', 'onSuccess:increment')
        ->assertScript('window.interceptLogs[3]', 'onFinish:increment')
        ;
    }

    public function test_action_interceptors_can_handle_errors()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public function throwError()
                {
                    throw new \Exception('Test error');
                }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <button wire:click="throwError" dusk="throw">Throw Error</button>
                    </div>
                    @script
                    <script>
                        window.errorLogs = []

                        Livewire.interceptAction(({ action, onSend, onError, onFinish }) => {
                            onSend(() => {
                                window.errorLogs.push('onSend')
                            })

                            onError(({ response }) => {
                                window.errorLogs.push('onError:' + response.status)
                            })

                            onFinish(() => {
                                window.errorLogs.push('onFinish')
                            })
                        })
                    </script>
                    @endscript
                    HTML;
                }
            }
        ])
        ->waitForLivewireToLoad()
        ->assertScript('window.errorLogs.length', 0)
        ->waitForLivewire()->click('@throw')
        ->pause(100) // Give time for error handling
        ->assertScript('window.errorLogs.length', 3)
        ->assertScript('window.errorLogs[0]', 'onSend')
        ->assertScript('window.errorLogs[1]', 'onError:500')
        ->assertScript('window.errorLogs[2]', 'onFinish')
        ;
    }
}
