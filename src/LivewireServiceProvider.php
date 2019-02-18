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
use Illuminate\Routing\RouteRegistrar;

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
        RouteFacade::post('/livewire/message', HttpConnectionHandler::class)->middleware('web');
        // @todo - make sure web middleware's cool.
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

        Router::macro('layout', function ($layout) {
            return (new class($this) extends RouteRegistrar {
                public function __construct(\Illuminate\Routing\Router $router)
                {
                    array_push($this->allowedAttributes, 'layout', 'section');

                    parent::__construct($router);

                    return $this;
                }
            })->layout($layout);
        });

        Router::macro('section', function ($section) {
            return (new class($this) extends RouteRegistrar {
                public function __construct(\Illuminate\Routing\Router $router)
                {
                    array_push($this->allowedAttributes, 'layout', 'section');

                    parent::__construct($router);

                    return $this;
                }
            })->section($section);
        });

        Router::macro('livewire', function ($uri, $component) {
            return $this->get($uri, function (...$params) use ($component) {
                $componentClass = app('livewire')->getComponentClass($component);

                $route = $this->current();
                // Cache the current route action (this callback actually), just to be safe.
                $cache = $route->getAction('uses');

                // We'll set the route action to be the "created" method from the chosen
                // Livewire component, to get the proper implicit bindings.
                $route->uses($componentClass . '@created');
                // This is normally handled in the "SubstituteBindings" middleware, but
                // because that middleware has already ran, we need to run them again.
                $this->substituteBindings($route);
                $this->substituteImplicitBindings($route);

                // Now we take all that we have gathered and convert it into a nice
                // array of parameters to pass into the "created" method.
                if ((new \ReflectionClass($componentClass))->hasMethod('created')) {
                    $method = (new \ReflectionClass($componentClass))->getMethod('created');
                    $options = $route->resolveMethodDependencies($route->parameters(), $method);
                } else {
                    $options = [];
                }

                // Restore the original route action.
                $route->uses($cache);

                return app('view')->file(__DIR__ . '/livewire-view.blade.php', [
                    'layout' => $route->getAction('layout') ?? 'layouts.app',
                    'section' => $route->getAction('section') ?? 'content',
                    'component' => $componentClass,
                    'componentOptions' => array_values($options),
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
