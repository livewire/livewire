<?php

namespace Livewire\Features\SupportRedirects;

use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Modelable;
use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_redirect()
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

    /** @test */
    public function session_flash_persist_when_redirecting_with_child_component_that_has_property_modelable()
    {
        config()->set('session.driver', 'file');

        Route::get('/redirect', RedirectComponent::class)->middleware('web');

        Livewire::visit([
            new class extends Component {
                public $foo = 0;

                public function render() { return <<<'HTML'
                <div>
                    <h1> Form : </h1>
                    <form wire:submit="save">
                        <livewire:child wire:model="foo" />
                        <button type="submit" dusk="submit-form">save</button>
                    </form>
                </div>
                HTML; }

                public function save()
                {
                    session()->flash('alertMessage', 'session flash data persist');
                    $this->redirect('/redirect');
                }
            },
            'child' => new class extends Component {
                #[Modelable]
                public $bar;

                public function render() { return <<<'HTML'
                <div>
                    <label>Child</label>
                    <input type="text" wire:model="bar" />
                </div>
                HTML; }
            }
        ])
        ->waitForLivewireToLoad()
        ->click('@submit-form')
        ->waitForNavigate()
        ->waitForLivewireToLoad()
        ->assertSeeIn('@session-message', 'session flash data persist');
    }
}

class RedirectComponent extends Component {
    public function render() { return <<<'HTML'
        <div>
            <h1>redirected page</h1>
            <div dusk="session-message">
                @session('alertMessage')
                    {{ $value }}
                @endsession
            </div>
        </div>
    HTML; }
}
