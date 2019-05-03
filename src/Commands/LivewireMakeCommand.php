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
    protected $nameSplitByDirectories;

    public function handle()
    {
        $this->nameSplitByDirectories = collect(preg_split('/[\/\\\]+/', $this->argument('name')))->filter();

        if ($this->nameSplitByDirectories->count() < 1) {
            $this->error(
                sprintf('The specified name [%s] is invalid.', $this->argument('name'))
            );

            return;
        }

        $force = $this->option('force');

        $this->createClass($force);
        $this->createView($force);
    }

    protected function createClass($force = false)
    {
        $classFileName = $this->classFileName();

        if (File::exists($classFileName) && ! $force) {
            $this->error(
                sprintf('Component class already exists [%s].', $classFileName)
            );

            return;
        }

        $this->makeDirectory($classFileName);

        File::put(
            $this->classFileName(),
            <<<EOT
<?php

namespace {$this->classNamespace()};

use Livewire\LivewireComponent;

class {$this->className()} extends LivewireComponent
{
    public function render()
    {
        return view('{$this->viewName()}');
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
        $viewFileName = $this->viewFileName();

        if (File::exists($viewFileName) && ! $force) {
            $this->error(
                sprintf('Component view already exists [%s].', $viewFileName)
            );

            return;
        }

        $this->makeDirectory($viewFileName);

        File::put(
            $viewFileName,
            <<<EOT
<div>
    {{-- {$this->randomNugget()} --}}
</div>

EOT
        );

        $this->info(
            sprintf('Component view has been created successfully [%s].', $viewFileName)
        );
    }

    protected function className()
    {
        return Str::title($this->nameSplitByDirectories->last());
    }

    protected function classFileName()
    {
        return app_path(
            collect(['Http', 'Livewire'])
                ->merge($this->nameSplitByDirectories->map(function ($part) {
                    return Str::title($part);
                }))
                ->implode(DIRECTORY_SEPARATOR) . '.php'
        );
    }

    protected function classNamespace()
    {
        return collect([trim($this->getAppNamespace(), '\\'), 'Http', 'Livewire'])
            ->merge($this->nameSplitByDirectories->slice(0, -1)->map(function ($part) {
                return Str::title($part);
            }))
            ->implode('\\');
    }

    protected function viewFileName()
    {
        return collect([head(config('view.paths')), 'livewire'])
            ->concat(
                $this->nameSplitByDirectories->map(function ($part) {
                    return Str::kebab($part);
                })
            )
            ->implode(DIRECTORY_SEPARATOR).'.blade.php';
    }

    protected function viewName()
    {
        return collect(['livewire'])
            ->merge($this->nameSplitByDirectories->map(function ($part) {
                return Str::kebab($part);
            }))
            ->implode('.');
    }

    protected function randomNugget()
    {
        $wisdom = require(__DIR__ . DIRECTORY_SEPARATOR . 'wisdom.php');

        return Arr::random($wisdom);
    }

    protected function makeDirectory($path)
    {
        if ( ! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }
}
