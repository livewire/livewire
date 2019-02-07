<?php

namespace Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use SuperClosure\Serializer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Yosymfony\ResourceWatcher\ResourceCacheMemory;
use Yosymfony\ResourceWatcher\ResourceWatcher;
use Livewire\Commands\LivewireStartCommand;
use Livewire\Commands\LivewireWatchCommand;
use Livewire\Commands\LivewireMakeCommand;

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
        Route::post('/fake-websockets/message', HttpConnectionHandler::class);
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LivewireStartCommand::class,
                LivewireWatchCommand::class,
                LivewireMakeCommand::class,
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
