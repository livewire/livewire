<?php

namespace Livewire\Features\SupportNavigate;

use ReflectionObject;
use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook() {
        return function() {
            Route::get('/first', FirstPage::class);
            Route::get('/second', SecondPage::class);
        };
    }

    /** @test */
    function can_navigate_to_page_without_reloading()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@link.to.second')
                ->waitFor('@link.to.first')
                ->assertSee('On second')
                ->assertScript('return window._lw_dusk_test')
                ->click('@link.to.first')
                ->waitFor('@link.to.second')
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first');
        });
    }
}

class FirstPage extends Component
{
    function render()
    {
        return <<<'HTML'
        <div>
            <div>On first</div>

            <a href="/second" wire:navigate dusk="link.to.second">Go to second page</a>
        </div>
        HTML;
    }
}

class SecondPage extends Component
{
    function render()
    {
        return <<<'HTML'
        <div>
            <div>On second</div>

            <a href="/first" wire:navigate dusk="link.to.first">Go to first page</a>
        </div>
        HTML;
    }
}
