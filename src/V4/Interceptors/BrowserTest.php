<?php

namespace Livewire\V4\Interceptors;

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

                        Livewire.intercept(() => {
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
        ->click('@refresh')
        ->assertScript('window.intercepts.length', 1)
        ->assertScript('window.intercepts[0]', 'intercept')
        ->click('@child-refresh')
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

                        this.intercept(() => {
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
        ->click('@refresh')
        ->assertScript('window.intercepts.length', 1)
        ->assertScript('window.intercepts[0]', 'intercept')
        ->click('@child-refresh')

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
        ->click('@refresh')
        ->assertScript('window.intercepts.length', 0)

        // The interceptor should be triggered when the action is performed...
        ->click('@do-something')
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

                        this.intercept(({ request, directive }) => {
                            window.intercepts.push(`init-${directive.method}`)

                            request.beforeSend(() => {
                                window.intercepts.push(`beforeSend-${directive.method}`)
                            })

                            request.afterSend(() => {
                                window.intercepts.push(`afterSend-${directive.method}`)
                            })

                            request.beforeResponse(() => {
                                window.intercepts.push(`beforeResponse-${directive.method}`)
                            })

                            request.afterResponse(() => {
                                window.intercepts.push(`afterResponse-${directive.method}`)
                            })

                            request.beforeRender(() => {
                                window.intercepts.push(`beforeRender-${directive.method}`)
                            })

                            request.afterRender(() => {
                                window.intercepts.push(`afterRender-${directive.method}`)
                            })

                            request.beforeMorph(() => {
                                window.intercepts.push(`beforeMorph-${directive.method}`)
                            })

                            request.afterMorph(() => {
                                window.intercepts.push(`afterMorph-${directive.method}`)
                            })

                            request.onError(() => {
                                window.intercepts.push(`error-${directive.method}`)
                            })

                            request.onFailure(() => {
                                window.intercepts.push(`failure-${directive.method}`)
                            })

                            request.onSuccess(() => {
                                window.intercepts.push(`success-${directive.method}`)
                            })

                            request.onCancel(() => {
                                window.intercepts.push(`cancel-${directive.method}`)
                            })

                            return () => {
                                window.intercepts.push(`returned-${directive.method}`)
                            }
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
        ->assertScript('window.intercepts.length', 11)
        ->assertScript('window.intercepts', [
            'init-$refresh',
            'beforeSend-$refresh',
            'afterSend-$refresh',
            'beforeResponse-$refresh',
            'afterResponse-$refresh',
            'success-$refresh',
            'beforeRender-$refresh',
            'beforeMorph-$refresh',
            'afterMorph-$refresh',
            'afterRender-$refresh',
            'returned-$refresh'
        ])

        // Reset...
        ->tap(fn ($b) => $b->script('window.intercepts = []'))

        // Next we will test the cancel interceptor...

        // Trigger the slow request...
        ->click('@slow-request')

        // Wait for a moment, then trigger another request which should cancel the slow request...
        ->pause(100)
        ->waitForLivewire()->click('@refresh')
        ->assertScript('window.intercepts.length', 16)
        // The below results are the combination of the slow request and the refresh request...
        ->assertScript('window.intercepts', [
            'init-slowRequest',
            'beforeSend-slowRequest',
            'afterSend-slowRequest',
            'init-$refresh',
            'beforeSend-$refresh',
            'cancel-slowRequest',
            'returned-slowRequest',
            'afterSend-$refresh',
            'beforeResponse-$refresh',
            'afterResponse-$refresh',
            'success-$refresh',
            'beforeRender-$refresh',
            'beforeMorph-$refresh',
            'afterMorph-$refresh',
            'afterRender-$refresh',
            'returned-$refresh',
        ])

        // Reset...
        ->tap(fn ($b) => $b->script('window.intercepts = []'))

        // Next we will test the error interceptor...

        // Trigger the error request...
        ->waitForLivewire()->click('@throw-error')

        ->assertScript('window.intercepts.length', 5)
        ->assertScript('window.intercepts', [
            'init-throwAnError',
            'beforeSend-throwAnError',
            'afterSend-throwAnError',
            'failure-throwAnError',
            'returned-throwAnError',
        ])
        ;
    }

    public function test_an_interceptor_can_cancel_a_message_request()
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

                        this.intercept(({ request, directive }) => {
                            window.intercepts.push(`init-${directive.method}`)

                            setTimeout(() => request.cancel(), 200)

                            request.beforeSend(() => {
                                window.intercepts.push(`beforeSend-${directive.method}`)
                            })

                            request.afterSend(() => {
                                window.intercepts.push(`afterSend-${directive.method}`)
                            })

                            request.beforeResponse(() => {
                                window.intercepts.push(`beforeResponse-${directive.method}`)
                            })

                            request.afterResponse(() => {
                                window.intercepts.push(`afterResponse-${directive.method}`)
                            })

                            request.beforeRender(() => {
                                window.intercepts.push(`beforeRender-${directive.method}`)
                            })

                            request.afterRender(() => {
                                window.intercepts.push(`afterRender-${directive.method}`)
                            })

                            request.beforeMorph(() => {
                                window.intercepts.push(`beforeMorph-${directive.method}`)
                            })

                            request.afterMorph(() => {
                                window.intercepts.push(`afterMorph-${directive.method}`)
                            })

                            request.onError(() => {
                                window.intercepts.push(`error-${directive.method}`)
                            })

                            request.onFailure(() => {
                                window.intercepts.push(`failure-${directive.method}`)
                            })

                            request.onSuccess(() => {
                                window.intercepts.push(`success-${directive.method}`)
                            })

                            request.onCancel(() => {
                                window.intercepts.push(`cancel-${directive.method}`)
                            })

                            return () => {
                                window.intercepts.push(`returned-${directive.method}`)
                            }
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
        ->waitForLivewire()->click('@slow-request')
        ->assertScript('window.intercepts.length', 5)
        ->assertScript('window.intercepts', [
            'init-slowRequest',
            'beforeSend-slowRequest',
            'afterSend-slowRequest',
            'cancel-slowRequest',
            'returned-slowRequest',
        ])
        ;
    }
}
