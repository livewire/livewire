<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use function Laravel\Prompts\select;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Attribute\AsCommand;
use Livewire\Finder\Finder;
use Livewire\Compiler\Parser\SingleFileParser;
use Livewire\Compiler\Parser\MultiFileParser;
use Livewire\Compiler\Parser\ClassComponentParser;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;

#[AsCommand(name: 'livewire:convert')]
class ConvertCommand extends Command
{
    protected $name = 'livewire:convert';

    protected $description = 'Convert a Livewire component between SFC, MFC, and class-based formats';

    protected Filesystem $files;

    protected Finder $finder;

    protected bool $isPageComponent = false;

    public function __construct()
    {
        parent::__construct();

        $this->files = app('files');

        $this->finder = app('livewire.finder');
    }

    public function handle()
    {
        $name = $this->argument('name');

        if (! $name) {
            $this->components->error('Component name is required.');
            return 1;
        }

        $name = $this->normalizeComponentName($name);

        // Detect what type the component currently is
        $sfcPath = $this->finder->resolveSingleFileComponentPath($name);
        $mfcPath = $this->finder->resolveMultiFileComponentPath($name);
        $classPaths = $this->finder->resolveClassComponentFilePaths($name);

        $isSfc = $sfcPath && $this->files->exists($sfcPath);
        $isMfc = $mfcPath && $this->files->exists($mfcPath) && $this->files->isDirectory($mfcPath);
        $isClass = $classPaths &&
                   $this->files->exists($classPaths['class']) &&
                   $this->files->exists($classPaths['view']);

        // If not found in default locations, check pages namespace
        if (! $isSfc && ! $isMfc && ! $isClass) {
            $pagesSfcPath = $this->finder->resolveSingleFileComponentPath('pages::' . $name);
            $pagesMfcPath = $this->finder->resolveMultiFileComponentPath('pages::' . $name);

            if ($pagesSfcPath && $this->files->exists($pagesSfcPath)) {
                $sfcPath = $pagesSfcPath;
                $isSfc = true;
                $this->isPageComponent = true;
            }

            if ($pagesMfcPath && $this->files->isDirectory($pagesMfcPath)) {
                $mfcPath = $pagesMfcPath;
                $isMfc = true;
                $this->isPageComponent = true;
            }
        }

        // Count how many formats exist
        $existingFormats = array_filter([$isSfc, $isMfc, $isClass]);

        if (count($existingFormats) === 0) {
            $this->components->error('Component not found.');
            return 1;
        }

        if (count($existingFormats) > 1) {
            $this->components->error('Component exists in multiple formats. Please resolve this conflict first.');
            return 1;
        }

        // Determine target format
        $targetFormat = $this->determineTargetFormat($isSfc, $isMfc, $isClass);

        if ($targetFormat === 'mfc' && $isSfc) {
            return $this->convertSingleFileToMultiFile($name, $sfcPath);
        }

        if ($targetFormat === 'sfc' && $isMfc) {
            return $this->convertMultiFileToSingleFile($name, $mfcPath);
        }

        if ($targetFormat === 'sfc' && $isClass) {
            return $this->convertClassToSingleFile($name, $classPaths);
        }

        if ($targetFormat === 'mfc' && $isClass) {
            return $this->convertClassToMultiFile($name, $classPaths);
        }

        if ($targetFormat === 'class' && $isSfc) {
            return $this->convertSingleFileToClass($name, $sfcPath);
        }

        if ($targetFormat === 'class' && $isMfc) {
            return $this->convertMultiFileToClass($name, $mfcPath);
        }

        $this->components->error('Component is already in the target format.');
        return 1;
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

    protected function determineTargetFormat(bool $isSfc, bool $isMfc = false, bool $isClass = false): string
    {
        // Explicit flags take priority
        if ($this->option('mfc')) {
            return 'mfc';
        }

        if ($this->option('sfc')) {
            return 'sfc';
        }

        if ($this->option('class')) {
            return 'class';
        }

        // Build available options based on current format
        $options = [];

        if (! $isSfc) {
            $options['sfc'] = 'Single-File Component (SFC)';
        }

        if (! $isMfc) {
            $options['mfc'] = 'Multi-File Component (MFC)';
        }

        if (! $isClass) {
            $options['class'] = 'Class Component';
        }

        // Prompt user to select target format
        return select(
            label: 'What format would you like to convert to?',
            options: $options,
        );
    }

    public function convertSingleFileToMultiFile(string $name, string $sfcPath): int
    {
        // Determine if we should use page path
        $pagesPath = config('livewire.component_namespaces.pages', resource_path('views/pages'));
        $isPageComponent = $this->isPageComponent || $this->isPathWithinDirectory($sfcPath, $pagesPath);

        if ($this->option('page') || $isPageComponent) {
            // Use pages directory
            $directory = $this->resolvePageComponentPath($name, 'mfc');
        } else {
            // Use the SFC filename (without .blade.php) as the directory name
            // This ensures we preserve emoji/naming exactly as it exists
            $sfcFilename = basename($sfcPath);
            $directoryName = str_replace('.blade.php', '', $sfcFilename);
            $directory = dirname($sfcPath) . '/' . $directoryName;
        }

        $directoryName = basename($directory);

        // Component name for files inside the directory (without emoji)
        $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $directoryName);

        $classPath = $directory . '/' . $componentName . '.php';
        $viewPath = $directory . '/' . $componentName . '.blade.php';
        $testPath = $directory . '/' . $componentName . '.test.php';
        $jsPath = $directory . '/' . $componentName . '.js';

        $parser = SingleFileParser::parse(app('livewire.compiler'), $sfcPath);

        $this->ensureDirectoryExists($directory);

        $scriptContents = $parser->generateScriptContentsForMultiFile();
        $classContents = $parser->generateClassContentsForMultiFile();
        $viewContents = $parser->generateViewContentsForMultiFile();

        $this->files->put($classPath, $classContents);
        $this->files->put($viewPath, $viewContents);

        // Check for existing test file next to SFC
        $sfcTestPath = str_replace('.blade.php', '.test.php', $sfcPath);
        $existingTestFile = $this->files->exists($sfcTestPath);

        if ($existingTestFile) {
            // Move existing test file into MFC directory
            $testContents = $this->files->get($sfcTestPath);
            $this->files->put($testPath, $testContents);
            $this->files->delete($sfcTestPath);
        } elseif ($this->option('test')) {
            // Create new test file if --test flag passed and no existing test
            $this->files->put($testPath, $this->buildMultiFileComponentTest($name));
        }

        if ($scriptContents !== null) {
            $this->files->put($jsPath, $scriptContents);
        }

        $this->files->delete($sfcPath);

        $this->components->info(sprintf('Livewire component [%s] converted to multi-file successfully.', $directory));

        // Show route suggestion if --page flag was used (and wasn't already a page)
        if ($this->option('page') && ! $isPageComponent) {
            $this->showRouteSuggestion($name);
        }

        return 0;
    }

    public function convertMultiFileToSingleFile(string $name, string $mfcPath): int
    {
        // Determine if we should use page path
        $pagesPath = config('livewire.component_namespaces.pages', resource_path('views/pages'));
        $isPageComponent = $this->isPageComponent || $this->isPathWithinDirectory($mfcPath, $pagesPath);

        if ($this->option('page') || $isPageComponent) {
            // Use pages directory
            $sfcPath = $this->resolvePageComponentPath($name, 'sfc');
        } else {
            // Use the MFC directory name as the SFC filename (with .blade.php)
            // This ensures we preserve emoji/naming exactly as it exists
            $directoryName = basename($mfcPath);
            $sfcPath = dirname($mfcPath) . '/' . $directoryName . '.blade.php';
        }

        $directoryName = basename($mfcPath);

        // Component name for files inside the directory (without emoji)
        $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $directoryName);

        $classPath = $mfcPath . '/' . $componentName . '.php';
        $viewPath = $mfcPath . '/' . $componentName . '.blade.php';
        $testPath = $mfcPath . '/' . $componentName . '.test.php';

        if (! $this->files->exists($classPath)) {
            $this->components->error('Multi-file component class file not found.');
            return 1;
        }

        if (! $this->files->exists($viewPath)) {
            $this->components->error('Multi-file component view file not found.');
            return 1;
        }

        $testFileExists = $this->files->exists($testPath);

        $parser = MultiFileParser::parse(app('livewire.compiler'), $mfcPath);

        // Generate the single-file component contents
        $sfcContents = $parser->generateContentsForSingleFile();

        $this->ensureDirectoryExists(dirname($sfcPath));

        $this->files->put($sfcPath, $sfcContents);

        // Move test file out before deleting directory
        if ($testFileExists) {
            $testContents = $this->files->get($testPath);
            $sfcTestPath = str_replace('.blade.php', '.test.php', $sfcPath);
            $this->files->put($sfcTestPath, $testContents);
        }

        // Delete the multi-file directory
        $this->files->deleteDirectory($mfcPath);

        $this->components->info(sprintf('Livewire component [%s] converted to single-file successfully.', $sfcPath));

        // Show route suggestion if --page flag was used (and wasn't already a page)
        if ($this->option('page') && ! $isPageComponent) {
            $this->showRouteSuggestion($name);
        }

        return 0;
    }

    public function convertClassToSingleFile(string $name, array $classPaths): int
    {
        $classPath = $classPaths['class'];
        $viewPath = $classPaths['view'];

        // Validate files exist
        if (! $this->files->exists($classPath)) {
            $this->components->error('Class component class file not found.');
            return 1;
        }

        if (! $this->files->exists($viewPath)) {
            $this->components->error('Class component view file not found.');
            return 1;
        }

        // Parse the class component
        try {
            $parser = ClassComponentParser::parse(
                app('livewire.compiler'),
                $classPath,
                $viewPath
            );
        } catch (\Exception $e) {
            $this->components->error('Failed to parse component: ' . $e->getMessage());
            return 1;
        }

        // Validate that the component can be converted
        $errors = $parser->canConvert();
        if (! empty($errors)) {
            foreach ($errors as $error) {
                $this->components->error($error);
            }
            return 1;
        }

        // Determine the SFC path based on --page flag
        if ($this->option('page')) {
            $sfcPath = $this->resolvePageComponentPath($name, 'sfc');
        } else {
            $sfcPath = $this->finder->resolveSingleFileComponentPathForCreation($name);
        }

        // Generate the SFC contents
        $sfcContents = $parser->generateContentsForSingleFile();

        // Ensure directory exists
        $this->ensureDirectoryExists(dirname($sfcPath));

        // Write the new SFC file
        $this->files->put($sfcPath, $sfcContents);

        // Handle test file if it exists
        $testPath = $this->findClassComponentTestPath($name);
        if ($testPath && $this->files->exists($testPath)) {
            $sfcTestPath = str_replace('.blade.php', '.test.php', $sfcPath);
            $testContents = $this->files->get($testPath);
            $this->files->put($sfcTestPath, $testContents);
            $this->files->delete($testPath);
        }

        // Delete the original class and view files
        $this->files->delete($classPath);
        $this->files->delete($viewPath);

        // Clean up empty directories
        $this->cleanupEmptyDirectory(dirname($classPath));
        $this->cleanupEmptyDirectory(dirname($viewPath));

        $this->components->info(sprintf(
            'Livewire class component [%s] converted to single-file successfully: %s',
            $name,
            $sfcPath
        ));

        // Show route suggestion if --page flag was used
        if ($this->option('page')) {
            $this->showRouteSuggestion($name);
        }

        return 0;
    }

    public function convertClassToMultiFile(string $name, array $classPaths): int
    {
        $classPath = $classPaths['class'];
        $viewPath = $classPaths['view'];

        // Validate files exist
        if (! $this->files->exists($classPath)) {
            $this->components->error('Class component class file not found.');
            return 1;
        }

        if (! $this->files->exists($viewPath)) {
            $this->components->error('Class component view file not found.');
            return 1;
        }

        // Parse the class component
        try {
            $parser = ClassComponentParser::parse(
                app('livewire.compiler'),
                $classPath,
                $viewPath
            );
        } catch (\Exception $e) {
            $this->components->error('Failed to parse component: ' . $e->getMessage());
            return 1;
        }

        // Validate that the component can be converted
        $errors = $parser->canConvert();
        if (! empty($errors)) {
            foreach ($errors as $error) {
                $this->components->error($error);
            }
            return 1;
        }

        // Determine the MFC directory path based on --page flag
        if ($this->option('page')) {
            $mfcDirectory = $this->resolvePageComponentPath($name, 'mfc');
        } else {
            $mfcDirectory = $this->finder->resolveMultiFileComponentPathForCreation($name);
        }
        $directoryName = basename($mfcDirectory);

        // Component name for files inside the directory (without emoji)
        $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $directoryName);

        $mfcClassPath = $mfcDirectory . '/' . $componentName . '.php';
        $mfcViewPath = $mfcDirectory . '/' . $componentName . '.blade.php';
        $mfcTestPath = $mfcDirectory . '/' . $componentName . '.test.php';

        // Generate the MFC contents
        $mfcClassContents = $parser->generateClassContentsForMultiFile();
        $mfcViewContents = $parser->generateViewContentsForMultiFile();

        // Create the MFC directory
        $this->ensureDirectoryExists($mfcDirectory);

        // Write the new MFC files
        $this->files->put($mfcClassPath, $mfcClassContents);
        $this->files->put($mfcViewPath, $mfcViewContents);

        // Handle test file if it exists
        $testPath = $this->findClassComponentTestPath($name);
        if ($testPath && $this->files->exists($testPath)) {
            $testContents = $this->files->get($testPath);
            $this->files->put($mfcTestPath, $testContents);
            $this->files->delete($testPath);
        } elseif ($this->option('test')) {
            $this->files->put($mfcTestPath, $this->buildMultiFileComponentTest($name));
        }

        // Delete the original class and view files
        $this->files->delete($classPath);
        $this->files->delete($viewPath);

        // Clean up empty directories
        $this->cleanupEmptyDirectory(dirname($classPath));
        $this->cleanupEmptyDirectory(dirname($viewPath));

        $this->components->info(sprintf(
            'Livewire class component [%s] converted to multi-file successfully: %s',
            $name,
            $mfcDirectory
        ));

        // Show route suggestion if --page flag was used
        if ($this->option('page')) {
            $this->showRouteSuggestion($name);
        }

        return 0;
    }

    public function convertSingleFileToClass(string $name, string $sfcPath): int
    {
        $parser = SingleFileParser::parse(app('livewire.compiler'), $sfcPath);

        // Determine class name and namespace from component name
        $segments = explode('.', $name);
        $className = Str::studly(array_pop($segments));
        $namespaceSegments = array_map(fn ($s) => Str::studly($s), $segments);

        $baseNamespace = config('livewire.class_namespace', 'App\\Livewire');
        $namespace = empty($namespaceSegments)
            ? $baseNamespace
            : $baseNamespace . '\\' . implode('\\', $namespaceSegments);

        // Determine file paths
        $classBasePath = config('livewire.class_path', app_path('Livewire'));
        $viewBasePath = config('livewire.view_path', resource_path('views/livewire'));

        // Build the view name from configured path (kebab-case)
        $viewNamespace = $this->extractViewNamespace($viewBasePath);
        $viewName = $viewNamespace . '.' . str_replace('.', '.', $name);

        // Generate class and view contents
        $result = $parser->generateClassComponentContents($className, $namespace, $viewName);

        $classSubPath = empty($namespaceSegments)
            ? ''
            : '/' . implode('/', $namespaceSegments);
        $viewSubPath = empty($segments)
            ? ''
            : '/' . implode('/', $segments);

        $classPath = $classBasePath . $classSubPath . '/' . $className . '.php';
        $viewPath = $viewBasePath . $viewSubPath . '/' . Str::kebab($className) . '.blade.php';

        // Ensure directories exist
        $this->ensureDirectoryExists(dirname($classPath));
        $this->ensureDirectoryExists(dirname($viewPath));

        // Write the files
        $this->files->put($classPath, $result['class']);
        $this->files->put($viewPath, $result['view']);

        // Handle test file
        $sfcTestPath = str_replace('.blade.php', '.test.php', $sfcPath);
        if ($this->files->exists($sfcTestPath)) {
            $testBasePath = base_path('tests/Feature/Livewire');
            $testSubPath = empty($namespaceSegments)
                ? ''
                : '/' . implode('/', $namespaceSegments);
            $testPath = $testBasePath . $testSubPath . '/' . $className . 'Test.php';

            $this->ensureDirectoryExists(dirname($testPath));
            $testContents = $this->files->get($sfcTestPath);
            $this->files->put($testPath, $testContents);
            $this->files->delete($sfcTestPath);
        }

        // Delete the SFC file
        $this->files->delete($sfcPath);

        $this->components->info(sprintf(
            'Livewire single-file component [%s] converted to class component successfully.',
            $name
        ));
        $this->components->bulletList([
            'Class: ' . $classPath,
            'View: ' . $viewPath,
        ]);

        return 0;
    }

    public function convertMultiFileToClass(string $name, string $mfcPath): int
    {
        $parser = MultiFileParser::parse(app('livewire.compiler'), $mfcPath);

        // Determine class name and namespace from component name
        $segments = explode('.', $name);
        $className = Str::studly(array_pop($segments));
        $namespaceSegments = array_map(fn ($s) => Str::studly($s), $segments);

        $baseNamespace = config('livewire.class_namespace', 'App\\Livewire');
        $namespace = empty($namespaceSegments)
            ? $baseNamespace
            : $baseNamespace . '\\' . implode('\\', $namespaceSegments);

        // Determine file paths
        $classBasePath = config('livewire.class_path', app_path('Livewire'));
        $viewBasePath = config('livewire.view_path', resource_path('views/livewire'));

        // Build the view name from configured path (kebab-case)
        $viewNamespace = $this->extractViewNamespace($viewBasePath);
        $viewName = $viewNamespace . '.' . str_replace('.', '.', $name);

        // Generate class and view contents
        $result = $parser->generateClassComponentContents($className, $namespace, $viewName);

        $classSubPath = empty($namespaceSegments)
            ? ''
            : '/' . implode('/', $namespaceSegments);
        $viewSubPath = empty($segments)
            ? ''
            : '/' . implode('/', $segments);

        $classPath = $classBasePath . $classSubPath . '/' . $className . '.php';
        $viewPath = $viewBasePath . $viewSubPath . '/' . Str::kebab($className) . '.blade.php';

        // Ensure directories exist
        $this->ensureDirectoryExists(dirname($classPath));
        $this->ensureDirectoryExists(dirname($viewPath));

        // Write the files
        $this->files->put($classPath, $result['class']);
        $this->files->put($viewPath, $result['view']);

        // Handle test file
        $directoryName = basename($mfcPath);
        $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $directoryName);
        $mfcTestPath = $mfcPath . '/' . $componentName . '.test.php';

        if ($this->files->exists($mfcTestPath)) {
            $testBasePath = base_path('tests/Feature/Livewire');
            $testSubPath = empty($namespaceSegments)
                ? ''
                : '/' . implode('/', $namespaceSegments);
            $testPath = $testBasePath . $testSubPath . '/' . $className . 'Test.php';

            $this->ensureDirectoryExists(dirname($testPath));
            $testContents = $this->files->get($mfcTestPath);
            $this->files->put($testPath, $testContents);
        }

        // Delete the MFC directory
        $this->files->deleteDirectory($mfcPath);

        $this->components->info(sprintf(
            'Livewire multi-file component [%s] converted to class component successfully.',
            $name
        ));
        $this->components->bulletList([
            'Class: ' . $classPath,
            'View: ' . $viewPath,
        ]);

        return 0;
    }

    protected function buildMultiFileComponentTest(string $name): string
    {
        $stub = $this->files->get($this->getStubPath('livewire-mfc-test.stub'));

        $componentName = collect(explode('.', $name))
            ->map(fn ($segment) => Str::kebab($segment))
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

        // Look for the stub in the MakeCommand directory
        return dirname(__FILE__) . '/' . $stub;
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }

    protected function findClassComponentTestPath(string $name): ?string
    {
        $segments = explode('.', $name);
        $className = Str::studly(end($segments)) . 'Test';
        $namespaceSegments = array_slice($segments, 0, -1);

        $path = base_path('tests/Feature/Livewire');

        if (! empty($namespaceSegments)) {
            $path .= '/' . collect($namespaceSegments)
                ->map(fn ($segment) => Str::studly($segment))
                ->implode('/');
        }

        $testPath = $path . '/' . $className . '.php';

        return $this->files->exists($testPath) ? $testPath : null;
    }

    protected function cleanupEmptyDirectory(string $path): void
    {
        // Get the base paths we should not delete beyond
        $classBasePath = config('livewire.class_path', app_path('Livewire'));
        $viewBasePath = config('livewire.view_path', resource_path('views/livewire'));

        // Only clean if directory exists and is empty
        if ($this->files->isDirectory($path)) {
            $files = $this->files->files($path);
            $directories = $this->files->directories($path);

            if (empty($files) && empty($directories)) {
                // Don't delete the base Livewire directories
                $normalizedPath = rtrim(str_replace('\\', '/', $path), '/');
                $normalizedClassBase = rtrim(str_replace('\\', '/', $classBasePath), '/');
                $normalizedViewBase = rtrim(str_replace('\\', '/', $viewBasePath), '/');

                if ($normalizedPath === $normalizedClassBase || $normalizedPath === $normalizedViewBase) {
                    return;
                }

                $this->files->deleteDirectory($path);

                // Recursively check parent directory
                $parent = dirname($path);
                $this->cleanupEmptyDirectory($parent);
            }
        }
    }

    protected function shouldUseEmoji(): bool
    {
        if ($this->option('emoji') !== null) {
            return filter_var($this->option('emoji'), FILTER_VALIDATE_BOOLEAN);
        }

        return config('livewire.make_command.emoji', true);
    }

    protected function extractViewNamespace(string $viewPath): string
    {
        // Convert the view path to a namespace
        // e.g., resource_path('views/livewire') => 'livewire'
        // e.g., resource_path('views/not-livewire') => 'not-livewire'
        $viewsPath = resource_path('views');

        // Remove the base views path to get the relative path
        $relativePath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $viewPath);
        $relativePath = str_replace($viewsPath . '/', '', $relativePath);

        // Convert directory separators to dots for the namespace
        return str_replace(['/', '\\'], '.', $relativePath);
    }

    protected function isPathWithinDirectory(string $path, string $directory): bool
    {
        $realPath = realpath($path);
        $realDirectory = realpath($directory);

        if ($realPath === false || $realDirectory === false) {
            // If paths can't be resolved, compare normalized paths
            $normalizedPath = rtrim(str_replace('\\', '/', $path), '/');
            $normalizedDirectory = rtrim(str_replace('\\', '/', $directory), '/');

            return str_starts_with($normalizedPath, $normalizedDirectory . '/');
        }

        return str_starts_with(
            str_replace('\\', '/', $realPath),
            str_replace('\\', '/', $realDirectory) . '/'
        );
    }

    protected function resolvePageComponentPath(string $name, string $format): string
    {
        $pagesPath = config('livewire.component_namespaces.pages', resource_path('views/pages'));

        // Convert component name to path segments
        $segments = explode('.', $name);
        $fileName = array_pop($segments);

        // Add emoji if configured
        $prefix = $this->shouldUseEmoji() ? '⚡' : '';

        $path = $pagesPath;
        if (! empty($segments)) {
            $path .= '/' . implode('/', $segments);
        }

        if ($format === 'sfc') {
            return $path . '/' . $prefix . $fileName . '.blade.php';
        }

        // MFC format - return directory path
        return $path . '/' . $prefix . $fileName;
    }

    protected function showRouteSuggestion(string $name): void
    {
        $componentName = 'pages::' . $name;

        $this->newLine();
        $this->components->warn('Route Update Required:');
        $this->components->bulletList([
            "Old: Route::get('/your-path', YourComponent::class)",
            "New: Route::livewire('/your-path', '{$componentName}')",
        ]);
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the component'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['sfc', null, InputOption::VALUE_NONE, 'Convert to single-file component'],
            ['mfc', null, InputOption::VALUE_NONE, 'Convert to multi-file component'],
            ['class', null, InputOption::VALUE_NONE, 'Convert to traditional class-based component'],
            ['page', null, InputOption::VALUE_NONE, 'Convert as a full-page component (place in pages namespace)'],
            ['test', null, InputOption::VALUE_NONE, 'Create a test file when converting to multi-file (if one does not exist)'],
            ['emoji', null, InputOption::VALUE_REQUIRED, 'Use emoji in file/directory names (true or false)'],
        ];
    }
}
