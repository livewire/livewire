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
        $stylePath = $this->getStylePath($sourcePath);
        $globalStylePath = $this->getGlobalStylePath($sourcePath);

        $times = [];
        foreach ([$classPath, $viewPath, $scriptPath, $stylePath, $globalStylePath] as $path) {
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

    public function getStylePath(string $sourcePath): string
    {
        $hash = $this->getHash($sourcePath);

        return $this->cacheDirectory . '/styles/' . $hash . '.css';
    }

    public function getGlobalStylePath(string $sourcePath): string
    {
        $hash = $this->getHash($sourcePath);

        return $this->cacheDirectory . '/styles/' . $hash . '.global.css';
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

        $this->ensureCacheDirectoryExists();
        File::makeDirectory($this->cacheDirectory . '/classes', 0777, true, true);

        File::put($classPath, $contents);
    }

    public function writeViewFile(string $sourcePath, string $contents): void
    {
        $viewPath = $this->getViewPath($sourcePath);

        $this->ensureCacheDirectoryExists();
        File::makeDirectory($this->cacheDirectory . '/views', 0777, true, true);

        File::put($viewPath, $contents);

        $this->mutateFileModificationTime($viewPath);
    }

    public function writeScriptFile(string $sourcePath, string $contents): void
    {
        $scriptPath = $this->getScriptPath($sourcePath);

        $this->ensureCacheDirectoryExists();
        File::makeDirectory($this->cacheDirectory . '/scripts', 0777, true, true);

        File::put($scriptPath, $contents);
    }

    public function writeStyleFile(string $sourcePath, string $contents): void
    {
        $stylePath = $this->getStylePath($sourcePath);

        $this->ensureCacheDirectoryExists();
        File::makeDirectory($this->cacheDirectory . '/styles', 0777, true, true);

        File::put($stylePath, $contents);
    }

    public function writeGlobalStyleFile(string $sourcePath, string $contents): void
    {
        $stylePath = $this->getGlobalStylePath($sourcePath);

        $this->ensureCacheDirectoryExists();
        File::makeDirectory($this->cacheDirectory . '/styles', 0777, true, true);

        File::put($stylePath, $contents);
    }

    public function writePlaceholderFile(string $sourcePath, string $contents): void
    {
        $placeholderPath = $this->getPlaceholderPath($sourcePath);

        $this->ensureCacheDirectoryExists();
        File::makeDirectory($this->cacheDirectory . '/placeholders', 0777, true, true);

        File::put($placeholderPath, $contents);

        $this->mutateFileModificationTime($placeholderPath);
    }

    public function writeIslandFile(string $sourcePath, string $contents): void
    {
        $viewPath = $this->getViewPath($sourcePath);

        $this->ensureCacheDirectoryExists();
        File::makeDirectory($this->cacheDirectory . '/views', 0777, true, true);

        File::put($viewPath, $contents);

        $this->mutateFileModificationTime($viewPath);
    }

    public function invalidateOpCache(string $sourcePath): void
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($sourcePath, true);
        }
    }

    protected function ensureCacheDirectoryExists(): void
    {
        File::makeDirectory($this->cacheDirectory, 0777, true, true);

        $gitignorePath = $this->cacheDirectory . '/.gitignore';

        if (! file_exists($gitignorePath)) {
            try {
                File::put($gitignorePath, "*\n!.gitignore");
            } catch (\Throwable) {
                // Non-critical, ignore if another process created it
            }
        }
    }

    public function mutateFileModificationTime(string $path): void
    {
        // This is a fix for a gnarly issue: blade's compiler uses filemtimes to determine if a compiled view has become expired.
        // AND it's comparison includes equals like this: $path >= $cachedPath
        // AND file_put_contents won't update the filemtime if the contents are the same
        // THEREFORE because we are creating a blade file at the same "second" that it is compiled
        // both the source file and the cached file's filemtime's match, therefore it become's in a perpetual state
        // of always being expired. So we mutate the source file to be one second behind so that the cached
        // view file is one second ahead. Phew. this one took a minute to find lol.
        $original = filemtime($path);
        touch($path, $original - 1);
    }

    public function clearCompiledFiles($output = null): void
    {
        try {
            if (is_dir($this->cacheDirectory)) {
                File::deleteDirectory($this->cacheDirectory);
            }
        } catch (\Exception $e) {
            // Silently fail to avoid breaking view:clear if there's an issue
            // But we can log it if output is available
            if ($output && method_exists($output, 'writeln')) {
                $output->writeln("<comment>Note: Could not clear Livewire compiled files.</comment>");
            }
        }
    }
}
