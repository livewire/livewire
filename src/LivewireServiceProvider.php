<?php

namespace Livewire;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Commands\LivewireMakeCommand;
use Livewire\Commands\LivewireStartCommand;
use Livewire\Commands\LivewireWatchCommand;
use Livewire\Connection\HttpConnectionHandler;

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
        RouteFacade::post('/livewire/message', HttpConnectionHandler::class);
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
        Route::macro('layout', function ($layout) {
            $this->action['layout'] = isset($this->action['layout'])
                ? $this->action['layout'].$layout
                : $layout;

            return $this;
        });

        Route::macro('section', function ($section) {
            $this->action['section'] = $section;

            return $this;
        });

        Router::macro('livewire', function ($uri, $component) {
            return $this->get($uri, function () use ($component) {
                $route = $this->current();

                return app('view')->file(__DIR__ . '/livewire-view.blade.php', [
                    'layout' => $route->getAction('layout') ?? 'layouts.app',
                    'section' => $route->getAction('section') ?? 'content',
                    'component' => $component,
                ]);
            });
        });
    }

    public function registerBladeDirectives()
    {
        Blade::directive('livewire', function ($expression) {
            return "<?php list(\$dom, \$id, \$serialized) = isset(\$wrapped) ? \$wrapped->mountChild({$expression}) : \Livewire\Livewire::mount({$expression}); echo \Livewire\Livewire::injectDataForJsInComponentRootAttributes(\$dom, \$id, \$serialized); ?>";
        });
    }
}
