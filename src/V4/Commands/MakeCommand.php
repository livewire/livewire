<?php

namespace Livewire\V4\Commands;

use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Livewire\V4\Compiler\SingleFileComponentCompiler;
use Illuminate\Support\Str;

use Illuminate\Support\Collection;
use Illuminate\Foundation\Inspiring;
use Illuminate\Console\GeneratorCommand;

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
        // Add ⚡ prefix to the component filename
        $view = str_replace('.', '/', $this->getView());
        $segments = explode('/', $view);
        $componentName = array_pop($segments);
        $componentName = '⚡' . $componentName;
        $segments[] = $componentName;
        $view = implode('/', $segments);
        
        $path = $this->viewPath(
            $view.'.blade.php'
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
        
        // Add ⚡ prefix to the directory name
        $segments = explode('/', $directory);
        $componentDirName = array_pop($segments);
        $componentDirName = '⚡' . $componentDirName;
        $segments[] = $componentDirName;
        $directory = implode('/', $segments);

        // Component name (without ⚡ for the files inside)
        $name = str($componentDirName)->replaceFirst('⚡', '')->toString();

        // Check for single file component with ⚡ prefix
        // Build the path for the single-file component (e.g., components/⚡counter.blade.php)
        $sfcDirectory = implode('/', array_slice($segments, 0, -1));
        $sfcFilename = '⚡' . $name . '.blade.php';
        $sfcPath = $this->viewPath(
            ($sfcDirectory ? $sfcDirectory . '/' : '') . $sfcFilename
        );

        $classPath = $this->viewPath(
            $directory.'/'.$name.'.php'
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
            $confirmed = confirm('Component already exists. Do you want to convert it to a multi-file component?');

            if ($confirmed) {
                if (! $this->files->isDirectory(dirname($classPath))) {
                    $this->files->makeDirectory(dirname($classPath), 0777, true, true);
                }

                $this->upgradeSingleFileComponentToMultiFileComponent($sfcPath, $classPath, $viewPath, $testPath, $jsPath);

                return;
            }
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

    protected function upgradeSingleFileComponentToMultiFileComponent($sfcPath, $classPath, $viewPath, $testPath, $jsPath)
    {
        $sfcContents = file_get_contents($sfcPath);

        $parsed = app(SingleFileComponentCompiler::class)->parseComponent($sfcContents);

        file_put_contents(
            $classPath,
            $parsed->getClassSource()
        );

        file_put_contents(
            $viewPath,
            $parsed->getViewSource()
        );

        file_put_contents(
            $testPath,
            $this->buildMultiFileComponentTest()
        );

        if ($parsed->hasScripts()) {
            $source = $parsed->getScriptSource();

            // Remove leading line break
            $source = ltrim($source, "\r\n");

            // Detect and remove common indentation
            $lines = explode("\n", $source);
            if (!empty($lines)) {
                // Find the indentation of the first non-empty line
                $firstLineIndent = 0;
                foreach ($lines as $line) {
                    if (trim($line) !== '') {
                        $firstLineIndent = strlen($line) - strlen(ltrim($line));
                        break;
                    }
                }

                // Remove that amount of indentation from all lines
                if ($firstLineIndent > 0) {
                    $lines = array_map(function($line) use ($firstLineIndent) {
                        // Only remove indentation if the line has at least that much whitespace
                        if (strlen($line) >= $firstLineIndent && substr($line, 0, $firstLineIndent) === str_repeat(' ', $firstLineIndent)) {
                            return substr($line, $firstLineIndent);
                        }
                        return $line;
                    }, $lines);
                }

                $source = implode("\n", $lines);
            }

            file_put_contents(
                $jsPath,
                $source
            );
        }

        $this->files->delete($sfcPath);

        $this->components->info(sprintf('%s [%s] converted successfully.', 'Livewire', $classPath));
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
