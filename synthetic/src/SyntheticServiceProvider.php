<?php

namespace Synthetic;

use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Synthetic\Synthesizers\AnonymousSynth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Synthetic\SyntheticManager;
use Synthetic\EventBus;

class SyntheticServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->alias(SyntheticManager::class, 'synthetic');
        $this->app->singleton(SyntheticManager::class);
        $this->app->singleton(EventBus::class);
        // AnonymousSynth::registerAnonymousCacheClassAutoloader();
    }

    public function boot()
    {
        $this->skipRequestPayloadTamperingMiddleware();
        $this->injectJavaScript();
        $this->directives();
        $this->features();
        $this->routes();
    }

    function skipRequestPayloadTamperingMiddleware()
    {
        ConvertEmptyStringsToNull::skipWhen(function () {
            return request()->is('synthetic/update');
        });

        TrimStrings::skipWhen(function () {
            return request()->is('synthetic/update');
        });
    }

    function injectJavaScript()
    {
        app('events')->listen(RequestHandled::class, function ($handled) {
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) return;

            $html = $handled->response->getContent();

            if (str($html)->contains('</html>')) {
                $csrf = csrf_token();
                $replacement = <<<EOT
                    <script>window.__csrf = '{$csrf}'</script>
                </html>
                EOT;
                $html = str($html)->replaceLast('</html>', $replacement);
                $handled->response->setContent($html->__toString());
            } else {
                //
            }
        });
    }

    function directives()
    {
        Blade::directive('synthetic', function ($expression) {
            return sprintf(
                "synthetic(<?php echo \%s::from(app('synthetic')->synthesize(%s))->toHtml() ?>)",
                \Illuminate\Support\Js::class, $expression
            );
        });
    }

    function features()
    {
        foreach ([
            \Synthetic\Features\SupportComputedProperties::class,
            \Synthetic\Features\SupportRedirects::class,
            \Synthetic\Features\SupportJsMethods::class,
        ] as $feature) {
            (new $feature)();
        }
    }

    function routes()
    {
        Route::get('/synthetic/synthetic.js', [JavaScriptAssets::class, 'source']);
        // Route::get('/synthetic/synthetic.js.map', [JavaScriptAssets::class, 'maps']);

        Route::post('/synthetic/new', function () {
            $name = request('name');

            return app('synthetic')->new($name);
        });

        Route::post('/synthetic/update', function () {
            $targets = request('targets');

            $responses = [];

            foreach ($targets as $target) {
                $snapshot = $target['snapshot'];
                $diff = $target['diff'];
                $calls = $target['calls'];

                $response = app('synthetic')->update($snapshot, $diff, $calls);

                unset($response['target']);

                $responses[] = $response;
            }

            return $responses;
        })->middleware('web')->name('synthetic.update');
    }
}
