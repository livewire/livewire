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
            $serialized = request('serialized');

            if ($serialized) {
                $livewire = decrypt($serialized);
            } else {
                $livewire = Livewire::activate($component, new \StdClass);
            }

            try {
                switch ($event) {
                    case 'init':
                        $livewire->mounted();
                        break;
                    case 'form-input':
                        $livewire->formInput($payload['form'], $payload['input'], $payload['value']);
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
            } catch (ValidationException $e) {
                $errors = $e->validator->errors();
            }

            $dom = $livewire->view($errors ?? null)->render();
            $refreshForms = $livewire->formsThatNeedInputRefreshing();
            $livewire->clearFormRefreshes();

            return [
                'component' => $component,
                'serialized' => encrypt($livewire),
                'refreshForms' => $refreshForms,
                'dom' => $dom,
            ];
        });
    }
}
