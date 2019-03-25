<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LivewireMakeCommand extends Command
{
    protected $signature = 'livewire:make {component}';

    protected $description = '@todo';

    protected $messages = [
        'file_exists' => '@todo - Whoops, looks like the file already exists: [{filePath}]',
        'file_created' => '@todo - Livewire component [{component}] successfully created',
    ];

    public function handle()
    {
        $this->makeFile();

        $this->makeView();

        $this->info(str_replace(
            '{component}',
            $this->argument('component'),
            $this->messages['file_created']
        ));
    }

    protected function makeFile()
    {
        $filePath = sprintf('%s/%s.php',
            $directory = app_path('Http/Livewire'),
            $component = rtrim($this->argument('component'), '.php')
        );

        if (File::exists($filePath)) {
            $this->error(str_replace('{filePath}', $filePath, $this->messages['file_exists']));
            return;
        }

        $this->ensureDirectoryExists($directory);

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

    protected function makeView()
    {
        $filePath = sprintf('%s/%s.blade.php',
            $directory = array_first(config('view.paths')) . '/livewire',
            kebab_case($this->argument('component'))
        );

        if (File::exists($filePath)) {
            $this->error(str_replace('{filePath}', $filePath, $this->messages['file_exists']));
            return;
        }

        $this->ensureDirectoryExists($directory);

        File::put($filePath, <<<EOT
<div>
    {{-- Go effing nuts. --}}
</div>

EOT
);
    }

    protected function ensureDirectoryExists($directory)
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory);
        }
    }
}
