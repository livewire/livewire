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
    protected $signature = 'livewire:make {component} {--view : Generate a view for the livewire component}';

    protected $description = '@todo';

    protected $messages = [
        'file_exists' => '@todo - Whoops, looks like the file already exists: [{filePath}]',
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

        // @todo - strip out .php if added on the end
        $this->makeFile($filePath, $component);

        if ($this->option('view')) {
            $filePath = sprintf('%s/%s.blade.php',
                $directory = array_first(config('view.paths')) . '/livewire',
                kebab_case($this->argument('component'))
            );

            if (File::exists($filePath)) {
                $this->error(str_replace('{filePath}', $filePath, $this->messages['file_exists']));
                return;
            }

            $this->ensureDirectoryExists($directory);

            // @todo - strip out .blade.php if added on the end
            $this->makeView($filePath);
        }

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
        $viewName = kebab_case($component);

        File::put($filePath, <<<EOT
<?php

namespace App\Http\Livewire;

use Livewire\LivewireComponent;

class {$component} extends LivewireComponent
{
    public function render()
    {
        return view('livewire.{$viewName}');
    }
}

EOT
);
    }

    protected function makeView($filePath)
    {
        File::put($filePath, <<<EOT
<div>
    {{-- Go effing nuts. --}}
</div>

EOT
);
    }
}
