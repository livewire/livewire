<?php

namespace Livewire\V4\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;
use Livewire\V4\Compiler\SingleFileComponentCompiler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;

#[AsCommand(name: 'make:livewire')]
class MakeCommand extends Command
{
    protected $name = 'make:livewire';

    protected $description = 'Create a new Livewire component';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        if (! $name) {
            $name = text('What should the component be named?', required: true);
        }

        $name = $this->normalizeComponentName($name);

        $type = $this->determineComponentType();

        switch ($type) {
            case 'class':
                return $this->createClassBasedComponent($name);
            case 'mfc':
                return $this->createMultiFileComponent($name);
            case 'sfc':
            default:
                return $this->createSingleFileComponent($name);
        }
    }

    protected function determineComponentType(): string
    {
        if ($this->option('class')) {
            return 'class';
        }

        if ($this->option('mfc')) {
            return 'mfc';
        }

        if ($this->option('sfc')) {
            return 'sfc';
        }

        if ($this->option('type')) {
            return $this->option('type');
        }

        return config('livewire.make_command.type', 'sfc');
    }

    protected function shouldUseEmoji(): bool
    {
        if ($this->option('emoji') !== null) {
            return filter_var($this->option('emoji'), FILTER_VALIDATE_BOOLEAN);
        }

        return config('livewire.make_command.emoji', true);
    }

    protected function createClassBasedComponent(string $name): int
    {
        $finder = app('livewire.finder');
        $paths = $finder->resolveClassComponentFilePaths($name);

        if ($this->files->exists($paths['class'])) {
            $this->components->error('Component already exists.');
            return 1;
        }

        $this->ensureDirectoryExists(dirname($paths['class']));
        $this->ensureDirectoryExists(dirname($paths['view']));

        $classContent = $this->buildClassBasedComponentClass($name);
        $viewContent = $this->buildClassBasedComponentView();

        $this->files->put($paths['class'], $classContent);
        $this->files->put($paths['view'], $viewContent);

        $this->components->info(sprintf('Livewire component [%s] created successfully.', $paths['class']));

        return 0;
    }

    protected function createSingleFileComponent(string $name): int
    {
        $finder = app('livewire.finder');
        $path = $finder->resolveSingleFileComponentPathForCreation($name);

        if ($this->files->exists($path)) {
            // Check if we should offer to upgrade to multi-file component
            if ($this->shouldUseEmoji() && str_contains(basename($path), '⚡')) {
                $upgrade = confirm('Component already exists. Would you like to upgrade this component to a multi-file component?');
                if ($upgrade) {
                    return $this->upgradeSingleFileToMultiFile($name, $path);
                }
            }

            $this->components->error('Component already exists.');
            return 1;
        }

        $this->ensureDirectoryExists(dirname($path));

        $content = $this->buildSingleFileComponent();

        $this->files->put($path, $content);

        $this->components->info(sprintf('Livewire component [%s] created successfully.', $path));

        return 0;
    }

    protected function createMultiFileComponent(string $name): int
    {
        $finder = app('livewire.finder');
        $directory = $finder->resolveMultiFileComponentPathForCreation($name);

        // Get the component name without emoji for file names inside the directory
        $componentName = basename($directory);
        if ($this->shouldUseEmoji()) {
            $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $componentName);
        }

        // Define file paths
        $classPath = $directory . '/' . $componentName . '.php';
        $viewPath = $directory . '/' . $componentName . '.blade.php';
        $testPath = $directory . '/' . $componentName . '.test.php';
        $jsPath = $directory . '/' . $componentName . '.js';

        // Check if we're upgrading from a single-file component
        $sfcPath = $finder->resolveSingleFileComponentPathForCreation($name);
        if ($this->files->exists($sfcPath)) {
            $upgrade = confirm('Component already exists as a single-file component. Would you like to upgrade it to a multi-file component?');

            if ($upgrade) {
                return $this->upgradeSingleFileToMultiFile($name, $sfcPath);
            }

            $this->components->error('Component already exists.');

            return 1;
        }

        if ($this->files->exists($directory)) {
            $this->components->error('Component already exists.');

            return 1;
        }

        $this->ensureDirectoryExists($directory);

        $classContent = $this->buildMultiFileComponentClass();
        $viewContent = $this->buildMultiFileComponentView();
        $testContent = $this->buildMultiFileComponentTest($name);
        $jsContent = $this->buildMultiFileComponentJs();

        $this->files->put($classPath, $classContent);
        $this->files->put($viewPath, $viewContent);
        $this->files->put($testPath, $testContent);

        if ($this->option('js')) {
            $this->files->put($jsPath, $jsContent);
        }

        $this->components->info(sprintf('Livewire component [%s] created successfully.', $directory));

        return 0;
    }

    protected function upgradeSingleFileToMultiFile(string $name, string $sfcPath): int
    {
        $finder = app('livewire.finder');
        $directory = $finder->resolveMultiFileComponentPathForCreation($name);

        $componentName = basename($directory);

        if ($this->shouldUseEmoji()) {
            $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $componentName);
        }

        $classPath = $directory . '/' . $componentName . '.php';
        $viewPath = $directory . '/' . $componentName . '.blade.php';
        $testPath = $directory . '/' . $componentName . '.test.php';
        $jsPath = $directory . '/' . $componentName . '.js';

        $sfcContents = $this->files->get($sfcPath);
        $parsed = app(SingleFileComponentCompiler::class)->parseComponent($sfcContents);

        $this->ensureDirectoryExists($directory);

        $this->files->put($classPath, $parsed->getClassSource());
        $this->files->put($viewPath, $parsed->getViewSource());
        $this->files->put($testPath, $this->buildMultiFileComponentTest($name));

        if ($parsed->hasScripts()) {
            $jsSource = $this->cleanupJavaScriptIndentation($parsed->getScriptSource());
            $this->files->put($jsPath, $jsSource);
        }

        $this->files->delete($sfcPath);

        $this->components->info(sprintf('Livewire component [%s] upgraded successfully.', $directory));

        return 0;
    }

    protected function cleanupJavaScriptIndentation(string $source): string
    {
        // Remove leading line break
        $source = ltrim($source, "\r\n");

        // Detect and remove common indentation
        $lines = explode("\n", $source);

        if (! empty($lines)) {
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

        return $source;
    }

    protected function buildClassBasedComponentClass(string $name): string
    {
        $stub = $this->files->get($this->getStubPath('livewire.stub'));

        $segments = explode('.', $name);

        $className = Str::studly(end($segments));

        $namespaceSegments = array_slice($segments, 0, -1);

        $namespace = 'App\\Livewire';

        if (! empty($namespaceSegments)) {
            $namespace .= '\\' . collect($namespaceSegments)
                ->map(fn($segment) => Str::studly($segment))
                ->implode('\\');
        }

        $viewName = 'livewire.' . collect($segments)
            ->map(fn($segment) => Str::kebab($segment))
            ->implode('.');

        $stub = str_replace('[namespace]', $namespace, $stub);
        $stub = str_replace('[class]', $className, $stub);
        $stub = str_replace('[view]', $viewName, $stub);

        return $stub;
    }

    protected function buildClassBasedComponentView(): string
    {
        $stub = $this->files->get($this->getStubPath('livewire.view.stub'));

        $stub = str_replace('[quote]', Inspiring::quotes()->random(), $stub);

        return $stub;
    }

    protected function buildSingleFileComponent(): string
    {
        $stub = $this->files->get($this->getStubPath('livewire-sfc.stub'));

        $stub = str_replace('[quote]', Inspiring::quotes()->random(), $stub);

        return $stub;
    }

    protected function buildMultiFileComponentClass(): string
    {
        return $this->files->get($this->getStubPath('livewire-mfc-class.stub'));
    }

    protected function buildMultiFileComponentView(): string
    {
        $stub = $this->files->get($this->getStubPath('livewire-mfc-view.stub'));

        $stub = str_replace('[quote]', Inspiring::quotes()->random(), $stub);

        return $stub;
    }

    protected function buildMultiFileComponentTest(string $name): string
    {
        $stub = $this->files->get($this->getStubPath('livewire-mfc-test.stub'));

        $componentName = collect(explode('.', $name))
            ->map(fn($segment) => Str::kebab($segment))
            ->implode('.');

        $stub = str_replace('[component-name]', $componentName, $stub);

        return $stub;
    }

    protected function buildMultiFileComponentJs(): string
    {
        return $this->files->get($this->getStubPath('livewire-mfc-js.stub'));
    }

    protected function getStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath('stubs/' . $stub);

        if ($this->files->exists($customPath)) {
            return $customPath;
        }

        return __DIR__ . '/stubs/' . $stub;
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }

    protected function normalizeComponentName(string $name): string
    {
        $name = str_replace('/', '.', $name);

        $name = str_replace('\\', '.', $name);

        $segments = explode('.', $name);
        $segments = array_map(fn($segment) => Str::kebab($segment), $segments);

        return implode('.', $segments);
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the component'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['sfc', null, InputOption::VALUE_NONE, 'Create a single-file component'],
            ['mfc', null, InputOption::VALUE_NONE, 'Create a multi-file component'],
            ['class', null, InputOption::VALUE_NONE, 'Create a class-based component'],
            ['type', null, InputOption::VALUE_REQUIRED, 'Component type (sfc, mfc, or class)'],
            ['emoji', null, InputOption::VALUE_REQUIRED, 'Use emoji in file/directory names (true or false)'],
            ['js', null, InputOption::VALUE_NONE, 'Create a JavaScript file for multi-file components'],
        ];
    }
}