<?php

namespace Livewire\V4\Commands;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:livewire')]
class MakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:livewire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new livewire component';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Component';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('mfc')) {
            $this->writeMultiFileComponent();
        } else {
            $this->writeSingleFileComponent();
        }
    }

    /**
     * Write the view for the component.
     *
     * @return void
     */
    protected function writeSingleFileComponent()
    {
        $path = $this->viewPath(
            str_replace('.', '/', $this->getView()).'.livewire.php'
        );

        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->components->error('Component already exists.');

            return;
        }

        file_put_contents(
            $path,
            $this->buildSingleFileComponent()
        );

        $this->components->info(sprintf('%s [%s] created successfully.', 'Livewire', $path));
    }

    protected function writeMultiFileComponent()
    {
        $directory = str_replace('.', '/', $this->getView());

        $name = str($directory)->afterLast('/')->toString();

        $sfcPath = $this->viewPath(
            $directory.'.livewire.php'
        );

        $classPath = $this->viewPath(
            $directory.'/'.$name.'.livewire.php'
        );

        $viewPath = $this->viewPath(
            $directory.'/'.$name.'.blade.php'
        );

        $testPath = $this->viewPath(
            $directory.'/'.$name.'.test.php'
        );

        $jsPath = $this->viewPath(
            $directory.'/'.$name.'.js'
        );

        // First check if the single file component version of this component already exists...
        if ($this->files->exists($sfcPath) && ! $this->option('force')) {
            $this->components->error('Single file component already exists.');

            return;
        }

        if (! $this->files->isDirectory(dirname($classPath))) {
            $this->files->makeDirectory(dirname($classPath), 0777, true, true);
        }

        if ($this->files->exists($classPath) && ! $this->option('force')) {
            $this->components->error('Component already exists.');

            return;
        }

        file_put_contents(
            $classPath,
            $this->buildMultiFileComponentClass()
        );

        file_put_contents(
            $viewPath,
            $this->buildMultiFileComponentView()
        );

        file_put_contents(
            $testPath,
            $this->buildMultiFileComponentTest()
        );

        if ($this->option('js')) {
            file_put_contents(
                $jsPath,
                    $this->buildMultiFileComponentJs()
                );
        }

        $this->components->info(sprintf('%s [%s] created successfully.', 'Livewire', $classPath));
    }

    /**
     * Build the single file component.
     *
     * @return string
     */
    protected function buildSingleFileComponent()
    {
        return str_replace(
            '[quote]',
            Inspiring::quotes()->random(),
            file_get_contents($this->getSingleFileComponentStub())
        );
    }

    protected function buildMultiFileComponentClass()
    {
        return file_get_contents($this->getMultiFileComponentClassStub());
    }

    protected function buildMultiFileComponentView()
    {
        return str_replace(
            '[quote]',
            Inspiring::quotes()->random(),
            file_get_contents($this->getMultiFileComponentViewStub())
        );
    }

    protected function buildMultiFileComponentTest()
    {
        return str_replace(
            '[name]',
            $this->argument('name'),
            file_get_contents($this->getMultiFileComponentTestStub())
        );
    }

    protected function buildMultiFileComponentJs()
    {
        return file_get_contents($this->getMultiFileComponentJsStub());
    }

    /**
     * Get the view name relative to the view path.
     *
     * @return string view
     */
    protected function getView()
    {
        $segments = explode('/', str_replace('\\', '/', $this->argument('name')));

        $name = array_pop($segments);

        $path = is_string($this->option('path'))
            ? explode('/', trim($this->option('path'), '/'))
            : [
                'components',
                ...$segments,
            ];

        $path[] = $name;

        return (new Collection($path))
            ->map(fn ($segment) => Str::kebab($segment))
            ->implode('.');
    }

    protected function getStub()
    {
        //
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getSingleFileComponentStub()
    {
        return $this->resolveStubPath('/stubs/livewire-sfc.stub');
    }

    protected function getMultiFileComponentClassStub()
    {
        return $this->resolveStubPath('/stubs/livewire-mfc-class.stub');
    }

    protected function getMultiFileComponentViewStub()
    {
        return $this->resolveStubPath('/stubs/livewire-mfc-view.stub');
    }

    protected function getMultiFileComponentTestStub()
    {
        return $this->resolveStubPath('/stubs/livewire-mfc-test.stub');
    }

    protected function getMultiFileComponentJsStub()
    {
        return $this->resolveStubPath('/stubs/livewire-mfc-js.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['mfc', null, InputOption::VALUE_NONE, 'Create a multi-file component'],
            ['path', null, InputOption::VALUE_REQUIRED, 'The location where the component view should be created'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the component already exists'],
            ['js', null, InputOption::VALUE_NONE, 'Create a JavaScript file for the component'],
        ];
    }
}
