<?php

namespace Livewire\Compiler;

use Illuminate\Support\Facades\File;

class CacheManager
{
    public function __construct(
        public string $cacheDirectory,
    ) {}

    public function hasBeenCompiled(string $sourcePath): bool
    {
        $classPath = $this->getClassPath($sourcePath);

        return file_exists($classPath);
    }

    public function isExpired(string $sourcePath): bool
    {
        $sourceModificationTime = $this->getLatestFileModificationTimeOfSource($sourcePath);

        $compiledModificationTime = $this->getLatestFileModificationTimeOfCompiledFiles($sourcePath);

        return $sourceModificationTime > $compiledModificationTime;
    }

    public function getLatestFileModificationTimeOfSource(string $sourcePath): int
    {
        if (is_dir($sourcePath)) {
            return max(array_map('filemtime', glob($sourcePath . '/*')));
        }

        return filemtime($sourcePath);
    }

    public function getLatestFileModificationTimeOfCompiledFiles(string $sourcePath): int
    {
        $classPath = $this->getClassPath($sourcePath);
        $viewPath = $this->getViewPath($sourcePath);
        $scriptPath = $this->getScriptPath($sourcePath);

        $times = [];
        foreach ([$classPath, $viewPath, $scriptPath] as $path) {
            if (file_exists($path)) {
                $times[] = filemtime($path);
            }
        }

        return empty($times) ? 0 : min($times);
    }

    public function getClassName(string $sourcePath): string
    {
        $instance = require $this->getClassPath($sourcePath);

        return $instance::class;
    }

    public function getHash(string $sourcePath): string
    {
        return substr(md5($sourcePath), 0, 8);
    }

    public function getClassPath(string $sourcePath): string
    {
        $hash = $this->getHash($sourcePath);

        return $this->cacheDirectory . '/classes/' . $hash . '.php';
    }

    public function getViewPath(string $sourcePath): string
    {
        $hash = $this->getHash($sourcePath);

        return $this->cacheDirectory . '/views/' . $hash . '.blade.php';
    }

    public function getScriptPath(string $sourcePath): string
    {
        $hash = $this->getHash($sourcePath);

        return $this->cacheDirectory . '/scripts/' . $hash . '.js';
    }

    public function getPlaceholderPath(string $sourcePath): string
    {
        $hash = $this->getHash($sourcePath);

        return $this->cacheDirectory . '/placeholders/' . $hash . '.blade.php';
    }

    public function writeClassFile(string $sourcePath, string $contents): void
    {
        $this->invalidateOpCache($sourcePath);

        $classPath = $this->getClassPath($sourcePath);

        File::ensureDirectoryExists($this->cacheDirectory . '/classes');

        File::put($classPath, $contents);
    }

    public function writeViewFile(string $sourcePath, string $contents): void
    {
        $viewPath = $this->getViewPath($sourcePath);

        File::ensureDirectoryExists($this->cacheDirectory . '/views');

        File::put($viewPath, $contents);
    }

    public function writeScriptFile(string $sourcePath, string $contents): void
    {
        $scriptPath = $this->getScriptPath($sourcePath);

        File::ensureDirectoryExists($this->cacheDirectory . '/scripts');

        File::put($scriptPath, $contents);
    }

    public function writePlaceholderFile(string $sourcePath, string $contents): void
    {
        $placeholderPath = $this->getPlaceholderPath($sourcePath);

        File::ensureDirectoryExists($this->cacheDirectory . '/placeholders');

        File::put($placeholderPath, $contents);
    }

    public function invalidateOpCache(string $sourcePath): void
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($sourcePath, true);
        }
    }
}
