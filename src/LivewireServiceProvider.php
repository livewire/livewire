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
use SuperClosure\Serializer;

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

        Artisan::comand('livewire:watch', function () {

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
