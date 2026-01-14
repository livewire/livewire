<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

use function Laravel\Prompts\confirm;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Attribute\AsCommand;
use Livewire\Finder\Finder;
use Livewire\Compiler\Parser\SingleFileParser;
use Livewire\Compiler\Parser\MultiFileParser;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\Command;

#[AsCommand(name: 'livewire:convert')]
class ConvertCommand extends Command
{
    protected $name = 'livewire:convert';

    protected $description = 'Convert a Livewire component between single-file and multi-file formats';

    protected Filesystem $files;

    protected Finder $finder;

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

        $isSfc = $sfcPath && $this->files->exists($sfcPath);
        $isMfc = $mfcPath && $this->files->exists($mfcPath) && $this->files->isDirectory($mfcPath);

        if (! $isSfc && ! $isMfc) {
            $this->components->error('Component not found.');
            return 1;
        }

        if ($isSfc && $isMfc) {
            $this->components->error('Component exists in both single-file and multi-file formats. Please resolve this conflict first.');
            return 1;
        }

        // Determine target format
        $targetFormat = $this->determineTargetFormat($isSfc);

        if ($targetFormat === 'mfc' && $isSfc) {
            return $this->convertSingleFileToMultiFile($name, $sfcPath);
        }

        if ($targetFormat === 'sfc' && $isMfc) {
            return $this->convertMultiFileToSingleFile($name, $mfcPath);
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

    protected function determineTargetFormat(bool $isSfc): string
    {
        if ($this->option('mfc')) {
            return 'mfc';
        }

        if ($this->option('sfc')) {
            return 'sfc';
        }

        // If no option specified, auto-detect and convert to opposite
        return $isSfc ? 'mfc' : 'sfc';
    }

    public function convertSingleFileToMultiFile(string $name, string $sfcPath): int
    {
        // Use the SFC filename (without .blade.php) as the directory name
        // This ensures we preserve emoji/naming exactly as it exists
        $sfcFilename = basename($sfcPath);
        $directoryName = str_replace('.blade.php', '', $sfcFilename);
        $directory = dirname($sfcPath) . '/' . $directoryName;

        // Component name for files inside the directory (without emoji)
        $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $directoryName);

        $classPath = $directory . '/' . $componentName . '.php';
        $viewPath = $directory . '/' . $componentName . '.blade.php';
        $testPath = $directory . '/' . $componentName . '.test.php';
        $jsPath = $directory . '/' . $componentName . '.js';
        $cssPath = $directory . '/' . $componentName . '.css';
        $globalCssPath = $directory . '/' . $componentName . '.global.css';

        $parser = SingleFileParser::parse(app('livewire.compiler'), $sfcPath);

        $this->ensureDirectoryExists($directory);

        $scriptContents = $parser->generateScriptContentsForMultiFile();
        $styleContents = $parser->generateStyleContentsForMultiFile();
        $globalStyleContents = $parser->generateGlobalStyleContentsForMultiFile();
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

        if ($styleContents !== null) {
            $this->files->put($cssPath, $styleContents);
        }

        if ($globalStyleContents !== null) {
            $this->files->put($globalCssPath, $globalStyleContents);
        }

        $this->files->delete($sfcPath);

        $this->components->info(sprintf('Livewire component [%s] converted to multi-file successfully.', $directory));

        return 0;
    }

    public function convertMultiFileToSingleFile(string $name, string $mfcPath): int
    {
        // Use the MFC directory name as the SFC filename (with .blade.php)
        // This ensures we preserve emoji/naming exactly as it exists
        $directoryName = basename($mfcPath);
        $sfcPath = dirname($mfcPath) . '/' . $directoryName . '.blade.php';

        // Component name for files inside the directory (without emoji)
        $componentName = str_replace(['⚡', '⚡︎', '⚡️'], '', $directoryName);

        $classPath = $mfcPath . '/' . $componentName . '.php';
        $viewPath = $mfcPath . '/' . $componentName . '.blade.php';
        $testPath = $mfcPath . '/' . $componentName . '.test.php';
        $jsPath = $mfcPath . '/' . $componentName . '.js';

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

    protected function shouldUseEmoji(): bool
    {
        if ($this->option('emoji') !== null) {
            return filter_var($this->option('emoji'), FILTER_VALIDATE_BOOLEAN);
        }

        return config('livewire.make_command.emoji', true);
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
            ['test', null, InputOption::VALUE_NONE, 'Create a test file when converting to multi-file (if one does not exist)'],
            ['emoji', null, InputOption::VALUE_REQUIRED, 'Use emoji in file/directory names (true or false)'],
        ];
    }
}
