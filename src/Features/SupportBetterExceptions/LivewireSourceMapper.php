<?php

namespace Livewire\Features\SupportBetterExceptions;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class LivewireSourceMapper
{
    /**
     * The path to the Livewire cache directory.
     */
    protected string $cacheDirectory;

    /**
     * Cache of parsed source mappings.
     */
    protected static array $sourceMapCache = [];

    public function __construct(?string $cacheDirectory = null)
    {
        $this->cacheDirectory = $cacheDirectory ?? storage_path('framework/views/livewire');
    }

    /**
     * Map compiled Livewire file paths to their original source paths.
     */
    public function map(FlattenException $exception): FlattenException
    {
        $trace = (new Collection($exception->getTrace()))
            ->map(function ($frame) {
                $file = (string) Arr::get($frame, 'file', '');

                if ($sourceInfo = $this->resolveSourceFromCompiledPath($file)) {
                    $frame['file'] = $sourceInfo['file'];
                    $frame['line'] = $this->calculateSourceLine(
                        $sourceInfo['file'],
                        $sourceInfo['line'],
                        $frame['line'] ?? 1,
                        $file
                    );
                }

                return $frame;
            })->toArray();

        return tap($exception, fn () => (fn () => $this->trace = $trace)->call($exception));
    }

    /**
     * Check if a file path is a compiled Livewire file and resolve its source.
     */
    protected function resolveSourceFromCompiledPath(string $compiledPath): ?array
    {
        // Check if this is a Livewire compiled file
        if (! $this->isLivewireCompiledFile($compiledPath)) {
            return null;
        }

        // Try to get cached result
        if (isset(static::$sourceMapCache[$compiledPath])) {
            return static::$sourceMapCache[$compiledPath];
        }

        // Read the compiled file and look for the @livewireSource comment
        $sourceInfo = $this->parseSourceComment($compiledPath);

        // Cache the result
        static::$sourceMapCache[$compiledPath] = $sourceInfo;

        return $sourceInfo;
    }

    /**
     * Check if the given path is a Livewire compiled file.
     */
    protected function isLivewireCompiledFile(string $path): bool
    {
        // Check if path contains the Livewire cache directory
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedCacheDir = str_replace('\\', '/', $this->cacheDirectory);

        return str_contains($normalizedPath, $normalizedCacheDir) ||
               str_contains($normalizedPath, 'livewire/classes/') ||
               str_contains($normalizedPath, 'livewire/views/');
    }

    /**
     * Parse the @livewireSource comment from a compiled file.
     */
    protected function parseSourceComment(string $compiledPath): ?array
    {
        if (! file_exists($compiledPath)) {
            return null;
        }

        $content = file_get_contents($compiledPath);

        // Look for PHP docblock comment: /** @livewireSource /path/to/file.php:123 */
        if (preg_match('/\/\*\*\s*@livewireSource\s+(.+?):(\d+)\s*\*\//', $content, $matches)) {
            return [
                'file' => $matches[1],
                'line' => (int) $matches[2],
            ];
        }

        // Look for Blade comment: {{-- @livewireSource /path/to/file.blade.php:123 --}}
        if (preg_match('/\{\{--\s*@livewireSource\s+(.+?):(\d+)\s*--\}\}/', $content, $matches)) {
            return [
                'file' => $matches[1],
                'line' => (int) $matches[2],
            ];
        }

        return null;
    }

    /**
     * Calculate the actual source line number based on the compiled line number.
     *
     * The source comment tells us where the original code starts. We need to
     * adjust the compiled line number to get the actual source line.
     */
    protected function calculateSourceLine(
        string $sourceFile,
        int $sourceStartLine,
        int $compiledLine,
        string $compiledPath
    ): int {
        // Find the line number of the @livewireSource comment in the compiled file
        $compiledContent = file_get_contents($compiledPath);
        $lines = explode("\n", $compiledContent);

        $commentLine = 1;
        foreach ($lines as $index => $line) {
            if (str_contains($line, '@livewireSource')) {
                $commentLine = $index + 1;
                break;
            }
        }

        // The offset from the comment to the error line in the compiled file
        $offsetFromComment = $compiledLine - $commentLine;

        // The actual source line is the start line plus the offset
        // (minus 1 because the line after the comment is the start line)
        return max(1, $sourceStartLine + $offsetFromComment - 1);
    }

    /**
     * Clear the source map cache.
     */
    public static function clearCache(): void
    {
        static::$sourceMapCache = [];
    }
}
