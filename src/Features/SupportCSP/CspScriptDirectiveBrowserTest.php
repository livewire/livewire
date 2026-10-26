<?php

namespace Livewire\Features\SupportCSP;

use Tests\BrowserTestCase;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;

class CspScriptDirectiveBrowserTest extends BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['livewire.csp_safe' => true]);

            View::addNamespace('csp-test-views', __DIR__ . '/test-views');

            Livewire::component('csp-script-page', CspScriptPage::class);

            app('router')->pushMiddlewareToGroup('web', CspScriptMiddleware::class);

            Route::get('/csp-script', CspScriptPage::class)->middleware('web');
        };
    }

    public function test_script_directive_runs_under_csp_safe_mode()
    {
        // A @script body is a statement list, usually with callbacks. The CSP
        // evaluator parses a single expression, so this can never go through
        // it. It has to run as a real, nonced script element instead...
        $this->browse(function ($browser) {
            $browser
                ->visit('/csp-script')
                ->waitForText('On CSP script page')
                ->assertConsoleLogHasNoErrors()
                ->assertScript('window.__cspScriptRan', true)
                ->assertScript('window.__cspScriptSawWire', true)
                ->assertScript('window.__cspScriptSawJs', true)
                ->assertScript('window.__cspScriptThisIsWire', true)
                ->waitForText('Count: 1')
            ;
        });
    }

    public function test_script_directive_with_await_runs_under_csp_safe_mode()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/csp-script')
                ->waitForText('On CSP script page')
                ->assertConsoleLogHasNoErrors()
                ->waitUsing(5, 100, fn () => $browser->script('return window.__cspAsyncScriptRan === true')[0])
            ;
        });
    }
}

class CspScriptMiddleware
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
class CspScriptPage extends Component
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
            <div>On CSP script page</div>
            <div dusk="count">Count: {{ $count }}</div>

            @script
            <script>
                // Statements, an arrow function, a global, and the scope Livewire promises...
                let runs = 0

                window.addEventListener('csp-script-test', () => { runs++ })
                window.dispatchEvent(new CustomEvent('csp-script-test'))

                window.__cspScriptRan = runs === 1
                window.__cspScriptSawWire = typeof $wire !== 'undefined' && typeof $wire.increment === 'function'
                window.__cspScriptSawJs = typeof $js === 'function'
                window.__cspScriptThisIsWire = this === $wire

                $wire.increment()
            </script>
            @endscript

            @script
            <script>
                await new Promise((resolve) => setTimeout(resolve, 10))

                window.__cspAsyncScriptRan = true
            </script>
            @endscript
        </div>
        HTML;
    }
}
