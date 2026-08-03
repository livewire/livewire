<?php

namespace Livewire\Features\SupportActionMiddleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Middleware;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;
use Tests\TestComponent;

class BrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            Route::livewire('/somewhere', new class extends TestComponent {})
                ->middleware('web')
                ->name('somewhere');

            Route::livewire('/somewhere-else', new class extends TestComponent {})
                ->middleware('web')
                ->name('somewhere.else');
        };
    }

    public function test_middleware_attribute_can_redirect_inside_middleware()
    {
        Livewire::visit(new class extends Component {
            #[Middleware(RedirectMiddleware::class)]
            public function redirectSomewhere()
            {
                $this->redirect('/somewhere');
            }

            public function render() 
            {
                return <<<'HTML'
                <div>
                    <button type="button" dusk="button" wire:click="redirectSomewhere">Protected Action</button>
                </div>
                HTML;
            }
        })
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertRouteIs('somewhere.else');
    }

    public function test_middleware_attribute_from_child_can_redirect_inside_middleware()
    {
        Livewire::visit([
            new class extends Component {
                public function render() 
                {
                    return <<<'HTML'
                    <div>
                        <div dusk="parent">Parent Component</div>
                        <livewire:child />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                #[Middleware(RedirectMiddleware::class)]
                public function redirectSomewhere()
                {
                    $this->redirect('/somewhere');
                }

                public function render() 
                {
                    return <<<'HTML'
                    <div>
                        <button type="button" dusk="button" wire:click="redirectSomewhere">Protected Action</button>
                    </div>
                    HTML;
                }
            }])
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertRouteIs('somewhere.else');
    }

    public function test_middleware_attribute_can_redirect_inside_middleware_using_event_listener()
    {
        Livewire::visit(new class extends Component {
            #[Middleware(RedirectMiddleware::class)]
            #[On('redirect-somewhere')]
            public function redirectSomewhere()
            {
                $this->redirect('/somewhere');
            }

            public function render() 
            {
                return <<<'HTML'
                <div>
                    <button type="button" dusk="button" wire:click="$dispatch('redirect-somewhere')">Protected Action</button>
                </div>
                HTML;
            }
        })
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertRouteIs('somewhere.else');
    }

    public function test_can_redirect_from_action_when_middleware_attribute_passed()
    {
        Livewire::visit(new class extends Component {
            #[Middleware(AllowMiddleware::class)]
            public function redirectSomewhere()
            {
                $this->redirect('/somewhere');
            }

            public function render() 
            {
                return <<<'HTML'
                <div>
                    <button type="button" dusk="button" wire:click="redirectSomewhere">Protected Action</button>
                </div>
                HTML;
            }
        })
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertRouteIs('somewhere');
    }

    public function test_can_redirect_from_action_using_event_listener_when_middleware_attribute_passed()
    {
        Livewire::visit(new class extends Component {
            #[Middleware(AllowMiddleware::class)]
            #[On('redirect-somewhere')]
            public function redirectSomewhere()
            {
                $this->redirect('/somewhere');
            }

            public function render() 
            {
                return <<<'HTML'
                <div>
                    <button type="button" dusk="button" wire:click="$dispatch('redirect-somewhere')">Protected Action</button>
                </div>
                HTML;
            }
        })
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertRouteIs('somewhere');
    }

    public function test_can_update_property_when_middleware_attribute_passed()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Middleware(AllowMiddleware::class)]
            public function increment()
            {
                $this->count++;
            }

            public function render() 
            {
                return Blade::render(<<<'HTML'
                <div>
                    <button type="button" dusk="button" wire:click="increment">Protected Action</button>
                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@count', '1');
    }

    public function test_cannot_update_property_when_middleware_attribute_not_passed()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Middleware(DenyMiddleware::class)]
            public function increment()
            {
                $this->count++;
            }

            public function render() 
            {
                return Blade::render(<<<'HTML'
                <div>
                    <button type="button" dusk="button" wire:click="increment">Protected Action</button>
                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@count', '0');
    }

    public function test_can_update_property_using_event_listener_when_middleware_attribute_passed()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Middleware(AllowMiddleware::class)]
            #[On('increment-count')]
            public function increment()
            {
                $this->count++;
            }

            public function render() 
            {
                return Blade::render(<<<'HTML'
                <div>
                    <button type="button" dusk="button" wire:click="$dispatch('increment-count')">Protected Action</button>
                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@count', '1');
    }

    public function test_cannot_update_property_using_event_listener_when_middleware_attribute_not_passed()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Middleware(DenyMiddleware::class)]
            #[On('increment-count')]
            public function increment()
            {
                $this->count++;
            }

            public function render() 
            {
                return Blade::render(<<<'HTML'
                <div>
                    <button type="button" dusk="button" wire:click="$dispatch('increment-count')">Protected Action</button>
                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@count', '0');
    }

    public function test_middleware_attribute_can_be_triggered_from_child()
    {
        Livewire::visit([
            new class extends Component {
                #[Middleware(RedirectMiddleware::class)]
                public function redirectSomewhere()
                {
                    $this->redirect('/somewhere');
                }

                public function render() 
                {
                    return <<<'HTML'
                    <div>
                        <div dusk="parent">Parent Component</div>
                        <livewire:child />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render() 
                {
                    return <<<'HTML'
                    <div>
                        <button type="button" dusk="button" wire:click="$parent.redirectSomewhere">Protected Action</button>
                    </div>
                    HTML;
                }
            }])
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertRouteIs('somewhere.else');
    }

    public function test_middleware_attribute_can_be_triggered_from_child_using_event_listener()
    {
        Livewire::visit([
            new class extends Component {
                #[Middleware(RedirectMiddleware::class)]
                #[On('redirect-somewhere')]
                public function redirectSomewhere()
                {
                    $this->redirect('/somewhere');
                }

                public function render() 
                {
                    return <<<'HTML'
                    <div>
                        <div dusk="parent">Parent Component</div>
                        <livewire:child />
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public function render() 
                {
                    return <<<'HTML'
                    <div>
                        <button type="button" dusk="button" wire:click="$dispatch('redirect-somewhere')">Protected Action</button>
                    </div>
                    HTML;
                }
            }])
        ->waitForText('Protected Action')
        ->waitForLivewire()->click('@button')
        ->assertRouteIs('somewhere.else');
    }
}

class RedirectMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        return redirect('/somewhere-else');
    }
}

class AllowMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        return $next($request);
    }
}

class DenyMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        return back();
    }
}