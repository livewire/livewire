<?php

namespace Livewire;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\ServiceProvider;
use Livewire\Commands\LivewireMakeCommand;
use Livewire\Connection\HttpConnectionHandler;
use Livewire\LivewireComponentsFinder;
use Livewire\Macros\RouteMacros;
use Livewire\Macros\RouterMacros;

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('livewire', LivewireManager::class);

        $this->app->instance(LivewireComponentsFinder::class, new LivewireComponentsFinder(
            new Filesystem, app()->bootstrapPath('cache/livewire-components.php'), app_path('Http/Livewire')
        ));
    }

    public function boot()
    {
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerRouterMacros();
        $this->registerBladeDirectives();
    }

    public function registerRoutes()
    {
        // Don't register route for non-Livewire calls.
        if (request()->headers->get('X-Livewire') == true) {
            // This should be the middleware stack of the original request.
            $middleware = decrypt(request('middleware'), $unserialize = true);

            RouteFacade::post('/livewire/message', HttpConnectionHandler::class)
                ->middleware($middleware);
        }

        if (request()->headers->get('X-Livewire-Keep-Alive') == true) {
            // This will be hit periodically by Livewire to make sure the csrf_token doesn't expire.
            RouteFacade::get('/livewire/keep-alive', function () {
                return response(200);
            })->middleware('web');
        }

    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LivewireMakeCommand::class,
            ]);
        }
    }

    public function registerRouterMacros()
    {
        Route::mixin(new RouteMacros);
        Router::mixin(new RouterMacros);
    }

    public function registerBladeDirectives()
    {
        Blade::directive('livewire', [LivewireBladeDirectives::class, 'livewire']);
    }
}
