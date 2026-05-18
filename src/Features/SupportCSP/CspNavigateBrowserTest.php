<?php

namespace Livewire\Features\SupportCSP;

use Tests\BrowserTestCase;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;

class CspNavigateBrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['livewire.csp_safe' => true]);

            View::addNamespace('csp-test-views', __DIR__ . '/test-views');

            Livewire::component('csp-first-page', CspFirstPage::class);
            Livewire::component('csp-second-page', CspSecondPage::class);
            Livewire::component('csp-lazy-page', CspLazyPage::class);
            Livewire::component('csp-lazy-child', CspLazyChild::class);

            // Apply CSP middleware globally so it covers both page routes
            // and the Livewire update endpoint (needed for lazy-loaded assets)...
            app('router')->pushMiddlewareToGroup('web', CspMiddleware::class);

            Route::get('/csp-first', CspFirstPage::class)->middleware('web');
            Route::get('/csp-second', CspSecondPage::class)->middleware('web');
            Route::get('/csp-lazy', CspLazyPage::class)->middleware('web');

            Route::get('/csp-test-asset.js', function () {
                return response('window.__cspLazyAssetLoaded = true;', 200, ['Content-Type' => 'application/javascript']);
            });
        };
    }

    public function test_wire_navigate_does_not_trigger_csp_violations()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/csp-first')
                ->assertSee('On CSP first')
                ->assertConsoleLogHasNoErrors()
                ->click('@link.to.second')
                ->waitForText('On CSP second')
                ->assertConsoleLogHasNoErrors()
            ;
        });
    }

    public function test_wire_navigate_back_button_does_not_trigger_csp_violations()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/csp-first')
                ->assertSee('On CSP first')
                ->assertConsoleLogHasNoErrors()
                ->click('@link.to.second')
                ->waitForText('On CSP second')
                ->assertConsoleLogHasNoErrors()
                ->back()
                ->waitForText('On CSP first')
                ->assertConsoleLogHasNoErrors()
            ;
        });
    }

    public function test_lazy_loaded_assets_do_not_trigger_csp_violations()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/csp-lazy')
                ->waitForText('Lazy child loaded')
                ->assertConsoleLogHasNoErrors()
            ;
        });
    }
}

class CspMiddleware
{
    public function handle($request, $next)
    {
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'nonce-{$nonce}'",
            "style-src 'unsafe-inline' 'self'",
            "connect-src 'self'",
        ]));

        return $response;
    }
}

#[Layout('csp-test-views::csp-layout')]
class CspFirstPage extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On CSP first</div>
            <a href="/csp-second" wire:navigate dusk="link.to.second">Go to second</a>
        </div>
        HTML;
    }
}

#[Layout('csp-test-views::csp-layout')]
class CspSecondPage extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On CSP second</div>
            <a href="/csp-first" wire:navigate dusk="link.to.first">Go to first</a>
            <button wire:click="increment" dusk="increment">Count: {{ $count }}</button>
        </div>
        HTML;
    }
}

#[Layout('csp-test-views::csp-layout')]
class CspLazyPage extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div>
            <div>On CSP lazy page</div>
            <livewire:csp-lazy-child />
        </div>
        HTML;
    }
}

#[Lazy]
class CspLazyChild extends Component
{
    public function render()
    {
        return <<<'HTML'
        <div>
            <div dusk="lazy-output">Lazy child loaded</div>

            @assets
            <script src="/csp-test-asset.js" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}"></script>
            @endassets
        </div>
        HTML;
    }
}
