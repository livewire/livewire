<?php

namespace Livewire\Features\SupportRedirects;

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
        ->click('@button')
        ->assertUrlIs('https://livewire.laravel.com/')
        ;
    }
}
