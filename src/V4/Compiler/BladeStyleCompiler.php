<?php

namespace Livewire\V4\Compiler;

use Illuminate\Support\Facades\File;
use Livewire\Mechanisms\Mechanism;

class BladeStyleCompiler extends Mechanism
{
    protected SingleFileComponentCompiler $compiler;
    protected array $compiledComponentCache = [];

    public function __construct(?SingleFileComponentCompiler $compiler = null)
    {
        $this->compiler = $compiler ?: new SingleFileComponentCompiler();
    }

    /**
     * Get the compiled class for a component, compiling if necessary.
     * Works exactly like Laravel's Blade caching - pure timestamp checking.
     */
    public function getCompiledClass(string $componentName): string
    {
        // Resolve component name to view path
        $viewPath = app('livewire.resolver')->resolve($componentName);

        // Check if we already have this component in memory cache for this request
        if (isset($this->compiledComponentCache[$viewPath])) {
            return $this->compiledComponentCache[$viewPath];
        }

        // Get the expected compiled paths
        $className = $this->getCompiledClassName($viewPath);
        $classPath = $this->getCompiledClassPath($viewPath);
        $compiledViewPath = $this->getCompiledViewPath($viewPath);

        // Check if compilation is needed using pure timestamp checking (like Blade)
        if ($this->needsCompilation($viewPath, $classPath, $compiledViewPath)) {
            // Use the underlying compiler to do the actual compilation
            $this->compiler->compile($viewPath);
        }

        // Load the class if it's not already loaded
        if (!class_exists($className)) {
            require_once $classPath;
        }

        // Cache in memory for this request
        $this->compiledComponentCache[$viewPath] = $className;

        return $className;
    }

    /**
     * Check if compilation is needed using pure file timestamp logic (like Blade).
     */
    protected function needsCompilation(string $viewPath, string $classPath, string $compiledViewPath): bool
    {
        // If either compiled file doesn't exist, we need compilation
        if (!File::exists($classPath) || !File::exists($compiledViewPath)) {
            return true;
        }

        // Get modification times
        $sourceModified = File::lastModified($viewPath);
        $classModified = File::lastModified($classPath);
        $viewModified = File::lastModified($compiledViewPath);

        // If source is newer than either compiled file, we need compilation
        return $sourceModified >= $classModified || $sourceModified >= $viewModified;
    }

    /**
     * Get the compiled class name for a view path.
     */
    protected function getCompiledClassName(string $viewPath): string
    {
        $content = File::get($viewPath);
        $hash = $this->generateHash($viewPath, $content);

        $name = $this->getComponentNameFromPath($viewPath);
        $className = str_replace(['-', '.'], '', ucwords($name, '-.'));

        return "Livewire\\Compiled\\{$className}_{$hash}";
    }

    /**
     * Get the compiled class file path.
     */
    protected function getCompiledClassPath(string $viewPath): string
    {
        $className = $this->getCompiledClassName($viewPath);
        $relativePath = str_replace(['Livewire\\Compiled\\', '\\'], ['', '/'], $className) . '.php';

        return $this->compiler->getCacheDirectory() . '/classes/' . $relativePath;
    }

    /**
     * Get the compiled view file path.
     */
    protected function getCompiledViewPath(string $viewPath): string
    {
        $content = File::get($viewPath);
        $hash = $this->generateHash($viewPath, $content);
        $name = $this->getComponentNameFromPath($viewPath);

        return $this->compiler->getCacheDirectory() . "/views/{$name}_{$hash}.blade.php";
    }

    /**
     * Generate hash for cache invalidation.
     */
    protected function generateHash(string $viewPath, string $content): string
    {
        return substr(md5($viewPath . $content . filemtime($viewPath)), 0, 8);
    }

    /**
     * Get component name from file path.
     */
    protected function getComponentNameFromPath(string $viewPath): string
    {
        $basename = basename($viewPath);
        $supportedExtensions = ['.livewire.php'];

        foreach ($supportedExtensions as $extension) {
            if (str_ends_with($basename, $extension)) {
                $basename = substr($basename, 0, -strlen($extension));
                break;
            }
        }
        
        // Strip ⚡ from the component name
        $basename = str_replace('⚡', '', $basename);

        return str_replace([' ', '_'], '-', $basename);
    }

    /**
     * Clear compiled cache for specific component or all.
     */
    public function clearCache(?string $componentName = null): void
    {
        if ($componentName) {
            $viewPath = app('livewire.resolver')->resolve($componentName);
            unset($this->compiledComponentCache[$viewPath]);

            // Delete the compiled files
            $classPath = $this->getCompiledClassPath($viewPath);
            $viewPath = $this->getCompiledViewPath($viewPath);

            if (File::exists($classPath)) {
                File::delete($classPath);
            }
            if (File::exists($viewPath)) {
                File::delete($viewPath);
            }
        } else {
            // Clear all
            $this->compiledComponentCache = [];
            File::deleteDirectory(\Livewire\invade($this->compiler)->cacheDirectory);
        }
    }

    /**
     * Get compilation statistics.
     */
    public function getStats(): array
    {
        $cacheDir = $this->compiler->getCacheDirectory();
        $compiledFiles = File::exists($cacheDir . '/classes')
            ? count(File::allFiles($cacheDir . '/classes'))
            : 0;

        return [
            'total_compiled' => $compiledFiles,
            'memory_cached' => count($this->compiledComponentCache),
            'cache_directory' => $cacheDir,
        ];
    }
}