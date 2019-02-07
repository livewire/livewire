<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Livewire\SocketConnectionHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class LivewireMakeCommand extends Command
{
    protected $signature = 'livewire:make {component}';

    protected $description = '@todo';

    protected $messages = [
        'file_exists' => '@todo - Whoops, looks like the view already exists: [{filePath}]',
        'file_created' => '@todo - Livewire component [{component}] successfully created',
    ];

    public function handle()
    {
        $filePath = sprintf('%s/%s.php',
            $directory = app_path('Http/Livewire'),
            $component = $this->argument('component')
        );

        if (File::exists($filePath)) {
            $this->error(str_replace('{filePath}', $filePath, $this->messages['file_exists']));
            return;
        }

        $this->ensureDirectoryExists($directory);

        $this->makeFile($filePath, $component);

        $this->info(str_replace('{component}', $component, $this->messages['file_created']));
    }

    protected function ensureDirectoryExists($directory)
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory);
        }
    }

    protected function makeFile($filePath, $component)
    {
        File::put($filePath, <<<EOT
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
    }
}
