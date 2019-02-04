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

class LivewireServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('livewire', LivewireManager::class);

        Blade::directive('livewire', function ($expression) {
            $prefix = Livewire::prefix();

            return <<<EOT
<div {$prefix}:root="<?php echo $expression; ?>">
    <div>
        <?php
        echo "waiting...";
        ?>
    </div>
</div>
EOT;
        });

        Blade::directive('click', function ($expression) {
            $prefix = Livewire::prefix();
            return "{$prefix}:click=\"<?php echo($expression); ?>\"";
        });

        Artisan::command('livewire:watch', function () {
            $finder = new Finder();
            $finder->files()
                ->name('*.php')
                ->in([
                    app_path('Http/Livewire'),
                    resource_path('views/livewire'),
                ]);

            $watcher = new \Yosymfony\ResourceWatcher\ResourceWatcher(
                new \Yosymfony\ResourceWatcher\ResourceCacheMemory(),
                $finder,
                new \Yosymfony\ResourceWatcher\Crc32ContentHash()
            );

            sorryagain:

            $process = new Process('php artisan livewire');
            $process->start(function ($type, $output) use ($process) {
                if ($type === $process::OUT) {
                    $this->line($output);
                } else {
                    $this->alert($output);
                }
            });

            while ($process->isRunning()) {
                $result = $watcher->findChanges();
                if ($result->hasChanges()) {
                    $process->stop();
                    $this->info('restarted');
                    goto sorryagain;
                }
                usleep(500000);
            }
        });

        Artisan::command('livewire', function () {
            $handler = new SocketConnectionHandler($this);

            IoServer::factory(
                new HttpServer(new WsServer($handler)),
                8080
            )->run();
        });

        Artisan::command('make:livewire {component}', function ($component) {
            $directory = app_path('Http/Livewire');
            $file = $directory . '/' . $component . '.php';

            if (File::exists($file)) {
                $this->error('Whoops, looks like the view already exists: [' . $filePath . ']');
            }

            if (! File::exists($directory)) {
                File::makeDirectory($directory);
            }

            File::put($file, <<<EOT
<?php

namespace App\Http\Livewire;

class $component extends Livewire
{
    public function render()
    {
        //
    }
}

EOT
);

            $this->info("Livewire component [{$component}] successfully created");
        });

        Route::post('/fake-websockets/message', HttpConnectionHandler::class);
    }
}
