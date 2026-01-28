<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Attribute\AsCommand;
use Livewire\Finder\Finder;
use Livewire\Compiler\Parser\SingleFileParser;
use Livewire\Compiler\Compiler;
use Illuminate\Support\Str;
use Illuminate\Foundation\Inspiring;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;

#[AsCommand(name: 'make:livewire')]
class MakeCommand extends Command
{
    protected $name = 'make:livewire';

    protected $description = 'Create a new Livewire component';

    protected Filesystem $files;

    protected Finder $finder;

    protected Compiler $compiler;

    public function __construct()
    {
        parent::__construct();

        $this->files = app('files');

        $this->finder = app('livewire.finder');

        $this->compiler = app('livewire.compiler');
    }

    public function handle()
    {
        $name = $this->argument('name');

        if (! $name) {
            $name = text('What should the component be named?', required: true);
        }

        $name = $this->normalizeComponentName($name);

        // If --test flag is provided and component already exists, just create the test
        if ($this->option('test') && $this->componentExistsInAnyForm($name)) {
            return $this->createTestForExistingComponent($name);
        }

        // Check if component already exists in ANY form before proceeding
        if ($this->componentExistsInAnyForm($name)) {
            $this->components->error('Component already exists.');
            return 1;
        }

        $type = $this->determineComponentType(
            config('livewire.make_command.type', 'sfc'),
        );

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

    protected function normalizeComponentName(string $name): string
    {
        return (string) str($name)
            ->replace('/', '.')
            ->replace('\\', '.')
            ->explode('.')
            ->map(fn ($i) => str($i)->kebab())
            ->implode('.');
    }

    protected function determineComponentType($fallback): string
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

        return $fallback;
    }

    protected function createClassBasedComponent(string $name): int
    {
        [$namespace, $componentName] = $this->finder->parseNamespaceAndName($name);

        if ($namespace !== null) {
            $classNamespaceDetails = $this->finder->getClassNamespace($namespace);

            if ($classNamespaceDetails === null) {
                $this->components->error('Namespace not found.');
                return 1;
            }
        } else {
            $classNamespaceDetails = null;
        }

        $paths = $this->finder->resolveClassComponentFilePaths($name);

        $this->ensureDirectoryExists(dirname($paths['class']));
        $this->ensureDirectoryExists(dirname($paths['view']));

        $classContent = $this->buildClassBasedComponentClass($componentName, $classNamespaceDetails);
        $viewContent = $this->buildClassBasedComponentView();

        $this->files->put($paths['class'], $classContent);
        $this->files->put($paths['view'], $viewContent);

        $this->components->info(sprintf('Livewire component [%s] created successfully.', $paths['class']));

        return 0;
    }

    protected function createSingleFileComponent(string $name): int
    {
        $path = $this->finder->resolveSingleFileComponentPathForCreation($name);

        if ($path === null) {
            [$namespace] = $this->finder->parseNamespaceAndName($name);
            $this->components->error(sprintf(
                "Namespace [%s] is not registered. Register it in config/livewire.php under 'component_namespaces' or use Livewire::addNamespace() in a service provider.",
                $namespace
            ));
            return 1;
        }

        // Check if we're converting from a multi-file component
        $mfcPath = $this->finder->resolveMultiFileComponentPath($name);
        if ($mfcPath && $this->files->exists($mfcPath) && $this->files->isDirectory($mfcPath)) {
            // Skip interactive prompts in testing environment
            if (app()->runningUnitTests()) {
                $this->components->error('Component already exists.');
                return 1;
            }

            $convert = confirm('Component already exists as a multi-file component. Would you like to convert it to a single-file component?');

            if ($convert) {
                return $this->call('livewire:convert', ['name' => $name, '--sfc' => true]);
            }

            $this->components->error('Component already exists.');
            return 1;
        }

        if ($this->files->exists($path)) {
            // Skip interactive prompts in testing environment
            if (app()->runningUnitTests()) {
                $this->components->error('Component already exists.');
                return 1;
            }

            $upgrade = confirm('Component already exists. Would you like to upgrade this component to a multi-file component?');

            if ($upgrade) {
                return $this->call('livewire:convert', ['name' => $name, '--mfc' => true]);
            }

            $this->components->error('Component already exists.');
            return 1;
        }

        $this->ensureDirectoryExists(dirname($path));

        $content = $this->buildSingleFileComponent();

        $this->files->put($path, $content);

        // Create test file if --test flag is present or configured in make_command.with.test
        if ($this->option('test') || config('livewire.make_command.with.test')) {
            $testPath = $this->getSingleFileComponentTestPath($path);
            $testContent = $this->buildSingleFileComponentTest($name);
            $this->files->put($testPath, $testContent);
        }

        $this->components->info(sprintf('Livewire component [%s] created successfully.', $path));

        return 0;
    }

    protected function createMultiFileComponent(string $name): int
    {
        $directory = $this->finder->resolveMultiFileComponentPathForCreation($name);

        if ($directory === null) {
            [$namespace] = $this->finder->parseNamespaceAndName($name);
            $this->components->error(sprintf(
                "Namespace [%s] is not registered. Register it in config/livewire.php under 'component_namespaces' or use Livewire::addNamespace() in a service provider.",
                $namespace
            ));
            return 1;
        }

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
        $sfcPath = $this->finder->resolveSingleFileComponentPathForCreation($name);
        if ($this->files->exists($sfcPath)) {
            // Skip interactive prompts in testing environment
            if (app()->runningUnitTests()) {
                $this->components->error('Component already exists.');
                return 1;
            }

            $upgrade = confirm('Component already exists as a single-file component. Would you like to upgrade it to a multi-file component?');

            if ($upgrade) {
                return $this->call('livewire:convert', ['name' => $name, '--mfc' => true]);
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

        if ($this->option('test') || config('livewire.make_command.with.test')) {
            $this->files->put($testPath, $testContent);
        }

        if ($this->option('js') || config('livewire.make_command.with.js')) {
            $this->files->put($jsPath, $jsContent);
        }

        $this->components->info(sprintf('Livewire component [%s] created successfully.', $directory));

        return 0;
    }


    protected function shouldUseEmoji(): bool
    {
        if ($this->option('emoji') !== null) {
            return filter_var($this->option('emoji'), FILTER_VALIDATE_BOOLEAN);
        }

        return config('livewire.make_command.emoji', true);
    }

    protected function buildClassBasedComponentClass(string $componentName, ?array $classNamespaceDetails = null): string
    {
        $stub = $this->files->get($this->getStubPath('livewire.stub'));

        $segments = explode('.', $componentName);

        $className = Str::studly(end($segments));

        $namespaceSegments = array_slice($segments, 0, -1);

        if ($classNamespaceDetails !== null) {
            $namespace = $classNamespaceDetails['classNamespace'];
            $viewPath = $classNamespaceDetails['classViewPath'];
        } else {
            $namespace = config('livewire.class_namespace', 'App\\Livewire');
            $viewPath = config('livewire.view_path', resource_path('views/livewire'));
        }

        if (! empty($namespaceSegments)) {
            $namespace .= '\\' . collect($namespaceSegments)
                ->map(fn($segment) => Str::studly($segment))
                ->implode('\\');
        }

        // Get the configured view path and extract the view namespace from it
        $viewNamespace = $this->extractViewNamespace($viewPath);

        $viewName = collect($segments)
            ->map(fn($segment) => Str::kebab($segment))
            ->prepend($viewNamespace)
            ->filter()
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

    protected function getSingleFileComponentTestPath(string $sfcPath): string
    {
        // Convert: /path/⚡foo.blade.php → /path/⚡foo.test.php
        return str_replace('.blade.php', '.test.php', $sfcPath);
    }

    protected function buildSingleFileComponentTest(string $name): string
    {
        // Use same stub as MFC, same format
        $stub = $this->files->get($this->getStubPath('livewire-mfc-test.stub'));

        $componentName = collect(explode('.', $name))
            ->map(fn($segment) => Str::kebab($segment))
            ->implode('.');

        $stub = str_replace('[component-name]', $componentName, $stub);

        return $stub;
    }

    protected function getClassBasedComponentTestPath(string $name): string
    {
        $segments = explode('.', $name);

        $className = Str::studly(end($segments)) . 'Test';

        $namespaceSegments = array_slice($segments, 0, -1);

        $path = base_path('tests/Feature/Livewire');

        if (! empty($namespaceSegments)) {
            $path .= '/' . collect($namespaceSegments)
                ->map(fn($segment) => Str::studly($segment))
                ->implode('/');
        }

        return $path . '/' . $className . '.php';
    }

    protected function buildClassBasedComponentTest(string $name): string
    {
        // Use same Pest-style stub as MFC/SFC for consistency
        $stub = $this->files->get($this->getStubPath('livewire-mfc-test.stub'));

        $componentName = collect(explode('.', $name))
            ->map(fn($segment) => Str::kebab($segment))
            ->implode('.');

        $stub = str_replace('[component-name]', $componentName, $stub);

        return $stub;
    }

    protected function getStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath('stubs/' . $stub);

        if ($this->files->exists($customPath)) {
            return $customPath;
        }

        return __DIR__ . '/' . $stub;
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }

    protected function extractViewNamespace(string $viewPath): string
    {
        // Convert the view path to a namespace
        // e.g., resource_path('views/livewire') => 'livewire'
        // e.g., resource_path('views/not-livewire') => 'not-livewire'
        $viewsPath = resource_path('views');

        if ($viewPath === $viewsPath) {
            return '';
        }

        // Remove the base views path to get the relative path
        $relativePath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $viewPath);
        $relativePath = str_replace($viewsPath . '/', '', $relativePath);

        // Convert directory separators to dots for the namespace
        return str_replace(['/', '\\'], '.', $relativePath);
    }

    protected function componentExistsInAnyForm(string $name): bool
    {
        $finder = $this->finder;

        // Check for multi-file component
        $mfcPath = $finder->resolveMultiFileComponentPath($name);
        if ($mfcPath && $this->files->exists($mfcPath) && $this->files->isDirectory($mfcPath)) {
            return true;
        }

        // Check for single-file component
        $sfcPath = $finder->resolveSingleFileComponentPathForCreation($name);
        if ($sfcPath !== null && $this->files->exists($sfcPath)) {
            return true;
        }

        // Check for class-based component
        $paths = $finder->resolveClassComponentFilePaths($name);
        if (isset($paths['class']) && $this->files->exists($paths['class'])) {
            return true;
        }

        return false;
    }

    protected function createTestForExistingComponent(string $name): int
    {
        $finder = $this->finder;

        // Check for multi-file component first
        $mfcPath = $finder->resolveMultiFileComponentPath($name);
        if ($mfcPath && $this->files->exists($mfcPath) && $this->files->isDirectory($mfcPath)) {
            $componentName = basename($mfcPath);
            if ($this->shouldUseEmoji()) {
                $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $componentName);
            }

            $testPath = $mfcPath . '/' . $componentName . '.test.php';

            if ($this->files->exists($testPath)) {
                $this->components->error('Test file already exists.');
                return 1;
            }

            $testContent = $this->buildMultiFileComponentTest($name);
            $this->files->put($testPath, $testContent);

            $this->components->info(sprintf('Livewire test [%s] created successfully.', $testPath));

            return 0;
        }

        // Check for single-file component
        $sfcPath = $finder->resolveSingleFileComponentPathForCreation($name);
        if ($sfcPath !== null && $this->files->exists($sfcPath)) {
            $testPath = $this->getSingleFileComponentTestPath($sfcPath);

            if ($this->files->exists($testPath)) {
                $this->components->error('Test file already exists.');
                return 1;
            }

            $testContent = $this->buildSingleFileComponentTest($name);
            $this->files->put($testPath, $testContent);

            $this->components->info(sprintf('Livewire test [%s] created successfully.', $testPath));

            return 0;
        }

        // Check for class-based component
        $paths = $finder->resolveClassComponentFilePaths($name);
        if (isset($paths['class']) && $this->files->exists($paths['class'])) {
            // For class-based components, create test in tests/Feature/Livewire directory
            $testPath = $this->getClassBasedComponentTestPath($name);

            $this->ensureDirectoryExists(dirname($testPath));

            if ($this->files->exists($testPath)) {
                $this->components->error('Test file already exists.');
                return 1;
            }

            $testContent = $this->buildClassBasedComponentTest($name);
            $this->files->put($testPath, $testContent);

            $this->components->info(sprintf('Livewire test [%s] created successfully.', $testPath));

            return 0;
        }

        $this->components->error('Component not found.');
        return 1;
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
            ['test', null, InputOption::VALUE_NONE, 'Create a test file'],
            ['emoji', null, InputOption::VALUE_REQUIRED, 'Use emoji in file/directory names (true or false)'],
            ['js', null, InputOption::VALUE_NONE, 'Create a JavaScript file for multi-file components'],
        ];
    }
}
