<?php

namespace Livewire;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Commands\LivewireMakeCommand;
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
        $this->registerDocsRoutes();

        // I'm guessing it's not cool to rely on the users "web" middleware stack.
        // @todo - figure out what to do here re: middleware.
        RouteFacade::post('/livewire/message', HttpConnectionHandler::class)->middleware('web');
    }

    public function registerDocsRoutes()
    {
        collect(scandir(__DIR__ . '/../docs'))
            ->filter(function ($file) { return preg_match('/\.md$/', $file); })
            ->map(function ($file) {
                preg_match('/([0-9]*)_(.*).md/', $file, $matches);

                return [
                    'file' => $file,
                    'order' => $matches[1],
                    'path' => sprintf('/livewire/docs/%s', $matches[2]),
                    'contents' => $contents = file_get_contents(__DIR__ . '/../docs/' . $file),
                    'title' => trim(str_after(strtok($contents, "\n"), '#')),
                ];
            })
            ->sortBy('order')
            ->tap(function ($tapped) use (&$collection) { $collection = $tapped; })
            ->each(function ($file) use ($collection) {
                RouteFacade::get($file['path'], function () use ($file, $collection) {
                    $template = __DIR__ . '/../docs/template.blade.php';
                    $css = file_get_contents(__DIR__ . '/../docs/template.css');

                    $parsed = (new \GitDown\GitDown(
                        '8e97a88f6778e690af1501f608f3856ba0a439a4'
                    ))->parseAndCache($file['contents']);

                    return View::file($template, [
                        'svgPath' => __DIR__ . '/../docs/logo.svg',
                        'css' => $css,
                        'title' => $file['title'],
                        'content' => $parsed,
                        'links' => $collection->pluck('title', 'path'),
                    ]);
                });
            });
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LivewireMakeCommand::class,
            ]);

            Artisan::command('livewire:docs', function () {
                exec(sprintf('open "%s"', url('/livewire/docs/quickstart')));
            })->describe('Open the docs in your browser.');
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
