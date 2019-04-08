<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LivewireMakeCommand extends Command
{
    protected $signature = 'make:livewire {component}';
    protected $description = 'Create a new Livewire component and it\'s corresponding blade view.';

    protected $component;

    public function handle()
    {
        $this->component = $this->argument('component');
        $this->view = kebab_case($this->component);
        $this->filenameOfClass = "{$this->component}.php";
        $this->filenameOfView = "{$this->view}.blade.php";
        $this->directoryContainingClass = app_path('Http'.DIRECTORY_SEPARATOR.'Livewire');
        $this->directoryContainingView = head(config('view.paths')).DIRECTORY_SEPARATOR.'livewire';
        $this->pathToClass = $this->directoryContainingClass.DIRECTORY_SEPARATOR.$this->filenameOfClass;
        $this->pathToView = $this->directoryContainingView.DIRECTORY_SEPARATOR.$this->filenameOfView;

        $this->unlessFilesAlreadyExist(function () {
            $this->ensureDirectoryExists($this->directoryContainingClass);
            $this->ensureDirectoryExists($this->directoryContainingView);
            $this->makeClass();
            $this->makeView();
        });

        $this->info("👍  Files created:");
        $this->info("-> [{$this->pathToClass}]");
        $this->info("-> [{$this->pathToView}]");
    }

    protected function unlessFilesAlreadyExist($callback) {
        throw_if(File::exists($this->pathToClass), new \Exception('File already exists ['.$this->pathToClass.']'));
        throw_if(File::exists($this->pathToView), new \Exception('File already exists ['.$this->pathToView.']'));

        $callback();
    }

    protected function makeClass()
    {
        File::put($this->pathToClass, <<<EOT
<?php

namespace App\Http\Livewire;

use Livewire\LivewireComponent;

class {$this->component} extends LivewireComponent
{
    public function render()
    {
        return view('livewire.{$this->view}');
    }
}

EOT
);
    }

    protected function makeView()
    {
        $wisdom = require(__DIR__.DIRECTORY_SEPARATOR.'wisdom.php');
        $nugget = $wisdom[rand(0, count($wisdom)-1)];

        File::put($this->pathToView, <<<EOT
<div>
    {{-- {$nugget} --}}
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
