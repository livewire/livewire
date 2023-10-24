<?php

namespace Livewire\Tests;

use Closure;
use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;


/** @group morphing */
class WireNavigateUrlUpdateOnRedirectBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            Route::get('/test-start', LivewireWithWireNavigateHref::class)->name('test-start');
            Route::get('/requested-content', fn () => '<span id="requested">REQUESTED</span>')->middleware(RedirectRequestedContent::class)->name('test-requested');
            Route::get('/delivered-content', fn () => '<span id="delivered">DELIVERED</span>')->name('test-delivered');
        };
    }

    /** @test */
    public function wire_navigate_updates_url_to_actual_redirect_target()
    {
        /**
         * Clicking on a wire:navigate link with a redirect target should update the route according to the new target.
         * This test fails at the moment.
         */
        $this->browse(function ($browser) {
            $browser
                ->visit('/test-start')
                ->assertSee('Click')
                ->click('@requested-content-link')
                ->waitFor('#delivered')
                ->assertSee('DELIVERED')
                ->assertRouteIs('test-delivered');
        });
    }

    /** @test */
    public function wire_navigate_keeps_target_if_not_redirected()
    {
        /**
         * Clicking on a wire:navigate link without a redirect target should keep the route according to the clicked link.
         * This test works right now and should keep working after the implementation of the feature.
         */
        $this->browse(function ($browser) {
            $browser
                ->visit('/test-start')
                ->assertSee('Click')
                ->click('@delivered-content-link')
                ->waitFor('#delivered')
                ->assertSee('DELIVERED')
                ->assertRouteIs('test-delivered');
        });
    }
}

class LivewireWithWireNavigateHref extends Component
{
    function render()
    {
        return <<<'HTML'
            <div>
                <a wire:navigate dusk="requested-content-link" href="/requested-content">Click me for requested content</a>
                <a wire:navigate dusk="delivered-content-link" href="/delivered-content">Click me for delivered content</a>
            </div>
        HTML;
    }
}

class RedirectRequestedContent
{
    public function handle(Request $request, Closure $next): Response
    {
        return redirect('/delivered-content');
    }
}
