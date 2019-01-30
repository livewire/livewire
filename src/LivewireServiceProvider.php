<?php

namespace Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class LivewireServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton('livewire', LivewireManager::class);
    }

    public function register()
    {
        Blade::directive('livewire', function ($expression) {
            return <<<EOT
<div livewire:root="<?php echo $expression; ?>">
    <div>
        <?php
        echo "waiting...";
        ?>
    </div>
</div>
EOT;
        });

        Blade::directive('click', function ($expression) {
            return "livewire:click=\"<?php echo($expression); ?>\"";
        });

        Artisan::command('livewire', function () {
            $handler = new SocketHandler($this);

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

        Route::post('/fake-websockets/message', function () {
            $event = request('event');
            $payload = request('payload');
            $component = request('component');
            $dom = '<div>hey there</div>';
            $serialized = request('serialized');

            if ($serialized) {
                $livewire = decrypt($serialized);
            } else {
                $livewire = Livewire::activate($component, new \StdClass);
            }

            switch ($event) {
                case 'init':
                    $livewire->mounted();
                    break;
                case 'sync':
                    $livewire->sync($payload['model'], $payload['value']);
            // // If we don't return early we cost too much in rendering AND break input elements for some reason.
            // return;
                    break;
                case 'fireMethod':
                    $livewire->{$payload['method']}(...$payload['params']);
                    break;
                default:
                    throw new \Exception('Unrecongnized event: ' . $event);
                    break;
            }

            $dom = $livewire->render()->render();

            return [
                'component' => $component,
                'serialized' => encrypt($livewire),
                'dom' => $dom,
            ];
        });
    }
}
