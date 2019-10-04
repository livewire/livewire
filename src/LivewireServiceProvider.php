<?php

namespace Livewire;

use Exception;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Livewire\Commands\CpCommand;
use Livewire\Commands\MvCommand;
use Livewire\Commands\RmCommand;
use Livewire\Macros\RouteMacros;
use Livewire\Macros\RouterMacros;
use Livewire\Commands\CopyCommand;
use Livewire\Commands\MakeCommand;
use Livewire\Commands\MoveCommand;
use Livewire\Commands\TouchCommand;
use Livewire\Commands\DeleteCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Engines\PhpEngine;
use Livewire\Commands\ComponentParser;
use Livewire\Commands\DiscoverCommand;
use Illuminate\Support\ServiceProvider;
use Livewire\Commands\MakeLivewireCommand;
use Livewire\Exceptions\BypassViewHandler;
use Illuminate\View\Engines\CompilerEngine;
use Livewire\Connection\HttpConnectionHandler;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;

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

        RouteFacade::post('/livewire/message/{name}', HttpConnectionHandler::class)
            ->middleware(config('livewire.middleware_group', 'web'));
    }

    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'views', config('livewire.view-path', 'livewire'));
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CopyCommand::class,
                CpCommand::class,
                DeleteCommand::class,
                DiscoverCommand::class,
                MakeCommand::class,
                MakeLivewireCommand::class,
                MoveCommand::class,
                MvCommand::class,
                RmCommand::class,
                TouchCommand::class,
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
        $this->publishesToGroups([
            __DIR__.'/../config/livewire.php' => base_path('config/livewire.php'),
        ], ['livewire', 'livewire:config']);

        $this->publishesToGroups([
            __DIR__.'/../dist' => public_path('vendor/livewire'),
        ], ['livewire', 'livewire:assets']);
    }

    protected function publishesToGroups(array $paths, $groups = null)
    {
        if (is_null($groups)) {
            $this->publishes($paths);

            return;
        }

        foreach ((array) $groups as $group) {
            $this->publishes($paths, $group);
        }
    }
}
