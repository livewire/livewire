<?php

namespace Livewire;

use Exception;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Livewire\Macros\RouteMacros;
use Livewire\Macros\RouterMacros;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Livewire\Commands\LivewireCPCommand;
use Livewire\Commands\LivewireMVCommand;
use Livewire\Commands\LivewireRMCommand;
use Livewire\Commands\LivewireCopyCommand;
use Livewire\Commands\LivewireMakeCommand;
use Livewire\Commands\LivewireMoveCommand;
use Livewire\Commands\MakeLivewireCommand;
use Livewire\Exceptions\BypassViewHandler;
use Illuminate\View\Engines\CompilerEngine;
use Livewire\Commands\LivewireTouchCommand;
use Livewire\Commands\LivewireDeleteCommand;
use Livewire\Connection\HttpConnectionHandler;
use Livewire\Commands\LivewireMakeCommandParser;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Livewire\Commands\ComponentParser;

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('livewire', LivewireManager::class);

        $this->app->instance(LivewireComponentsFinder::class, new LivewireComponentsFinder(
            new Filesystem,
            app()->bootstrapPath('cache/livewire-components.php'),
            ComponentParser::generatePathFromNamespace(config('livewire.class_namespace', 'App\\Http\\Livewire'))
        ));

        $this->allowCertainExceptionsToBypassTheBladeViewHandler();
    }

    public function allowCertainExceptionsToBypassTheBladeViewHandler()
    {
        // Errors thrown while a view is rendering are caught by the Blade
        // compiler and wrapped in an "ErrorException". This makes Livewire errors
        // harder to read, AND causes issues like `abort(404)` not actually working.
        $this->app['view.engine.resolver']->register('blade', function () {
            return new class($this->app['blade.compiler']) extends CompilerEngine {
                protected function handleViewException(Exception $e, $obLevel)
                {
                    $uses = array_flip(class_uses_recursive($e));

                    if (
                        // Don't wrap "abort(404)".
                        $e instanceof NotFoundHttpException
                        // Don't wrap "abort(500)".
                        || $e instanceof HttpException
                        // Don't wrap most Livewire exceptions.
                        || isset($uses[BypassViewHandler::class])
                    ) {
                        // This is because there is no "parent::parent::".
                        PhpEngine::handleViewException($e, $obLevel);

                        return;
                    }

                    parent::handleViewException($e, $obLevel);
                }
            };
        });
    }

    public function boot()
    {
        if ($this->app['livewire']->isLivewireRequest()) {
            $this->bypassMiddleware([
                TrimStrings::class,
                ConvertEmptyStringsToNull::class,
            ]);
        }

        $this->registerRoutes();
        $this->registerViews();
        $this->registerCommands();
        $this->registerRouterMacros();
        $this->registerBladeDirectives();
        $this->registerPublishables();
    }

    public function registerRoutes()
    {
        RouteFacade::get('/livewire/livewire.js', [LivewireJavaScriptAssets::class, 'unminified']);
        RouteFacade::get('/livewire/livewire.min.js', [LivewireJavaScriptAssets::class, 'minified']);

        RouteFacade::post('/livewire/message/{name}', HttpConnectionHandler::class);
    }

    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'views', config('livewire.view-path', 'livewire'));
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeLivewireCommand::class,
                LivewireMakeCommand::class,
                LivewireTouchCommand::class,
                LivewireMoveCommand::class,
                LivewireMVCommand::class,
                LivewireCopyCommand::class,
                LivewireCPCommand::class,
                LivewireDeleteCommand::class,
                LivewireRMCommand::class,
            ]);

            Artisan::command('livewire:discover', function () {
                app(LivewireComponentsFinder::class)->build();

                $this->info('Livewire auto-discovery manifest rebuilt!');
            });
        }
    }

    public function registerRouterMacros()
    {
        Route::mixin(new RouteMacros);
        Router::mixin(new RouterMacros);
    }

    public function registerBladeDirectives()
    {
        Blade::directive('livewireAssets', [LivewireBladeDirectives::class, 'livewireAssets']);
        Blade::directive('livewire', [LivewireBladeDirectives::class, 'livewire']);
    }

    protected function bypassMiddleware(array $middlewareToExclude)
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

        $openKernel = new ObjectPrybar($kernel);

        $middleware = $openKernel->getProperty('middleware');

        $openKernel->setProperty('middleware', array_diff($middleware, $middlewareToExclude));
    }

    protected function registerPublishables()
    {
        $this->publishes([
            __DIR__.'/../config/livewire.php' => base_path('config/livewire.php'),
        ], ['livewire', 'livewire:config']);

        $this->publishes([
            __DIR__.'/../dist' => public_path('vendor/livewire'),
        ], ['livewire', 'livewire:assets']);
    }
}
