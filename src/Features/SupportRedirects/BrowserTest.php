<?php

namespace Livewire\Features\SupportRedirects;

use Livewire\Attributes\On;
use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\Route;

class BrowserTest extends BrowserTestCase
{
    public function test_can_redirect()
    {
        Livewire::visit([new class extends Component {
            public function redirectToWebsite()
            {
                $this->redirect('https://livewire.laravel.com');
            }

            public function render() { return <<<HTML
            <div>
                <button type="button" dusk="button" wire:click="redirectToWebsite">Redirect to Livewire</button>
            </div>
            HTML; }
        }])
        ->waitForText('Redirect to Livewire')
        ->waitForLivewire()->click('@button')
        ->assertUrlIs('https://livewire.laravel.com/')
        ;
    }

    public function test_session_flash_persists_when_redirecting_from_request_with_multiple_components_in_the_same_request()
    {
        config()->set('session.driver', 'file');

        Route::get('/redirect', RedirectComponent::class)->middleware('web');

        Livewire::visit([
            new class extends Component {
                public $foo = 0;

                public function doRedirect()
                {
                    session()->flash('alert', 'Session flash data');

                    $this->redirect('/redirect');
                }

                public function render() { return <<<'HTML'
                <div>
                    <h1>Parent</h1>

                    <button wire:click="doRedirect" dusk="button">Do redirect</button>

                    <livewire:child :$foo />
                </div>
                HTML; }
            },
            'child' => new class extends Component {
                #[Reactive]
                public $foo;

                public function render() { return <<<'HTML'
                <div>
                    <label>Child</label>
                </div>
                HTML; }
            }
        ])
        ->click('@button')
        ->waitForTextIn('@session-message', 'Session flash data');
    }

    public function test_session_flash_clearing_on_subsequent_requests()
    {
        config()->set('session.driver', 'file');

        Livewire::visit([
            new class extends Component {
                public $foo = 0;

                public function mount()
                {
                    session()->flash('alert', 'Session flash data');
                }

                #[On('rerender')]
                public function rerender() {
                    $this->foo++;
                }

                public function render() { return <<<'HTML'
                <div>
                    <h1>Parent</h1>

                    <div dusk="foo">{{$foo}}</div>

                    <livewire:child />

                    <div dusk="session-message">
                        @if(session()->has('alert'))
                            Flash exists
                        @else
                            Flash cleared
                        @endif
                    </div>
                </div>
                HTML; }
            },
            'child' => new class extends Component {

                public function rerenderParent()
                {
                    $this->dispatch('rerender');
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="rerenderParent" dusk="button">Make subsequent request</button>
                </div>
                HTML; }
            }
        ])
            ->waitForTextIn('@foo', '0')
            ->waitForTextIn('@session-message', 'Flash exists')
            ->waitForLivewire()->click('@button')
            ->waitForTextIn('@foo', '1')
            ->waitForTextIn('@session-message', 'Flash cleared');
    }
}

class RedirectComponent extends Component {
    public function render() { return <<<'HTML'
        <div>
            <h1>Redirected page</h1>

            <div dusk="session-message">
                @session('alert')
                    {{ $value }}
                @endsession
            </div>
        </div>
    HTML; }
}
