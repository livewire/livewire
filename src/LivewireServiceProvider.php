<?php

namespace Livewire;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
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
        $this->registerBladeDirectives();
    }

    public function registerRoutes()
    {
        Route::post('/livewire/message', HttpConnectionHandler::class);
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

    public function registerBladeDirectives()
    {
        Blade::directive('livewire', function ($expression) {
            return "<?php echo \Livewire\Livewire::mount({$expression}) ?>";
        });
    }
}
