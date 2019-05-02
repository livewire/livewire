<?php

namespace Livewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LivewireMakeCommand extends Command
{
    use DetectsApplicationNamespace;

    protected $signature = 'make:livewire {name} {--force}';
    protected $description = 'Create a new Livewire component and it\'s corresponding blade view.';

    /** @var Collection */
    protected $splittedName;

    public function handle()
    {
        $this->splittedName = collect(preg_split('/[\/\\\]+/', $this->argument('name')))->filter();

        $force = $this->option('force');

        $this->createClass($force);
        $this->createView($force);
    }

    protected function getClassName()
    {
        return Str::title($this->splittedName->last());
    }

    protected function getClassFileName()
    {
        return app_path(
            collect(['Http', 'Livewire'])
                ->merge($this->splittedName->map(function ($part) {
                    return Str::title($part);
                }))
                ->implode(DIRECTORY_SEPARATOR) . '.php'
        );
    }

    protected function getClassNamespace()
    {
        return collect([trim($this->getAppNamespace(), '\\'), 'Http', 'Livewire'])
            ->merge($this->splittedName->slice(0, -1)->map(function ($part) {
                return Str::title($part);
            }))
            ->implode('\\');
    }

    protected function getViewFileName()
    {
        return head(config('view.paths'))
            . DIRECTORY_SEPARATOR
            . 'livewire'
            . DIRECTORY_SEPARATOR
            . $this->splittedName->map(function ($part) {
                return Str::kebab($part);
            })->implode(DIRECTORY_SEPARATOR)
            . '.blade.php';
    }

    protected function getViewName()
    {
        return collect(['livewire'])
            ->merge($this->splittedName->map(function ($part) {
                return Str::kebab($part);
            }))
            ->implode('.');
    }

    protected function getRandomNugget()
    {
        $wisdom = require(__DIR__ . DIRECTORY_SEPARATOR . 'wisdom.php');

        return Arr::random($wisdom);
    }

    protected function createClass($force = false)
    {
        $classFileName = $this->getClassFileName();

        if (File::exists($classFileName) && !$force) {
            $this->error(
                sprintf('Component class already exists [%s].', $classFileName)
            );

            return;
        }

        $this->makeDirectory($classFileName);

        File::put($this->getClassFileName(), <<<EOT
<?php

namespace {$this->getClassNamespace()};

use Livewire\LivewireComponent;

class {$this->getClassName()} extends LivewireComponent
{
    public function render()
    {
        return view('{$this->getViewName()}');
    }
}

EOT
        );

        $this->info(
            sprintf('Component class has been created successfully [%s].', $classFileName)
        );
    }

    protected function createView($force = false)
    {
        $viewFileName = $this->getViewFileName();

        if (File::exists($viewFileName) && !$force) {
            $this->error(
                sprintf('Component view already exists [%s].', $viewFileName)
            );

            return;
        }

        $this->makeDirectory($viewFileName);

        File::put($viewFileName, <<<EOT
<div>
    {{-- {$this->getRandomNugget()} --}}
</div>

EOT
        );

        $this->info(
            sprintf('Component view has been created successfully [%s].', $viewFileName)
        );
    }

    protected function makeDirectory($path)
    {
        if (!File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }
}
