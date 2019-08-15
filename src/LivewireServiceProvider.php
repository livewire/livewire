<?php

namespace Livewire;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\PhpEngine;
use Livewire\Commands\LivewireDestroyCommand;
use Livewire\Commands\LivewireMakeCommand;
use Livewire\Connection\HttpConnectionHandler;
use Livewire\Exceptions\BypassViewHandler;
use Livewire\Macros\RouteMacros;
use Livewire\Macros\RouterMacros;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('livewire', LivewireManager::class);

        $this->app->instance(LivewireComponentsFinder::class, new LivewireComponentsFinder(
            new Filesystem, app()->bootstrapPath('cache/livewire-components.php'), app_path('Http/Livewire')
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
                        // Dont' wrap most Livewire exceptions.
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
        $this->registerRoutes();
        $this->registerViews();
        $this->registerCommands();
        $this->registerRouterMacros();
        $this->registerBladeDirectives();
    }

    public function registerRoutes()
    {
        RouteFacade::get('/livewire/livewire.js', LivewireJavaScriptAssets::class);

        RouteFacade::post('/livewire/message', HttpConnectionHandler::class);

        // This will be hit periodically by Livewire to make sure the csrf_token doesn't expire.
        RouteFacade::get('/livewire/keep-alive', LivewireKeepAlive::class);
    }

    public function registerViews()
    {
        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'views', 'livewire');
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LivewireMakeCommand::class,
                LivewireDestroyCommand::class,
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
        Blade::directive('livewireAssets', function ($expression) {
            return '{!! Livewire::assets('.$expression.') !!}';
        });

        Blade::directive('livewire', [LivewireBladeDirectives::class, 'livewire']);
    }

    public function isLivewireRequest()
    {
        return request()->headers->get('X-Livewire') == true;
    }
}
