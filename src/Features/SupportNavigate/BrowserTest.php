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
            Livewire::component('first-page', FirstPage::class);
            Livewire::component('second-page', SecondPage::class);
            Route::get('/first', FirstPage::class)->middleware('web');
            Route::get('/second', SecondPage::class)->middleware('web');
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

    /** @test */
    function can_redirect_without_reloading_from_a_page_that_was_loaded_by_wire_navigate()
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
                ->click('@redirect.to.first')
                ->waitFor('@link.to.second')
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first');
        });
    }

    /** @test */
    function can_redirect_without_reloading_using_the_helper_from_a_page_that_was_loaded_normally()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/first')
                ->tap(fn ($b) => $b->script('window._lw_dusk_test = true'))
                ->assertScript('return window._lw_dusk_test')
                ->assertSee('On first')
                ->click('@redirect.to.second')
                ->waitFor('@link.to.first')
                ->assertSee('On second')
                ->assertScript('return window._lw_dusk_test');
        });
    }
}

class FirstPage extends Component
{
    function redirectToPageTwoUsingNavigate()
    {
        return $this->redirect('/second', navigate: true);
    }

    function render()
    {
        return <<<'HTML'
        <div>
            <div>On first</div>

            <a href="/second" wire:navigate dusk="link.to.second">Go to second page</a>
            <button type="button" wire:click="redirectToPageTwoUsingNavigate" dusk="redirect.to.second">Redirect to second page</button>
        </div>
        HTML;
    }
}

class SecondPage extends Component
{
    function redirectToPageOne() {
        return redirect('/first');
    }

    function render()
    {
        return <<<'HTML'
        <div>
            <div>On second</div>

            <a href="/first" wire:navigate dusk="link.to.first">Go to first page</a>
            <button type="button" wire:click="redirectToPageOne" dusk="redirect.to.first">Redirect to first page</button>
        </div>
        HTML;
    }
}
