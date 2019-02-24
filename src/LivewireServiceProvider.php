<?php

namespace Livewire;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\ServiceProvider;
use Livewire\Commands\LivewireMakeCommand;
use Livewire\Commands\LivewireStartCommand;
use Livewire\Commands\LivewireWatchCommand;
use Livewire\Connection\HttpConnectionHandler;
use Livewire\Macros\RouteMacros;
use Livewire\Macros\RouterMacros;

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('livewire', LivewireManager::class);
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
        // I'm guessing it's not cool to rely on the users "web" middleware stack.
        // @todo - figure out what to do here re: middleware.
        RouteFacade::post('/livewire/message', HttpConnectionHandler::class)->middleware('web');
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LivewireMakeCommand::class,
                LivewireStartCommand::class,
                LivewireWatchCommand::class,
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
        Blade::directive('on', [LivewireBladeDirectives::class, 'on']);
        Blade::directive('endlivewire', [LivewireBladeDirectives::class, 'endlivewire']);
    }
}
