<?php

namespace Livewire\Features\SupportIslands\Compiler;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class IslandCompiler
{
    protected string $mutableContents;

    public function __construct(
        public string $pathSignature,
        public string $contents,
    ) {
        $this->mutableContents = $contents;
    }

    public static function compile(string $pathSignature, string $contents): string
    {
        $compiler = new self($pathSignature, $contents);

        return $compiler->process();
    }

    public function process(): string
    {
        $compiler = $this->getHackedBladeCompiler();

        $islandStatementCounter = 1;
        $maxIslandStatementCounter = 1;

        $compiler->directive('island', function ($expression) use (&$islandStatementCounter, &$maxIslandStatementCounter) {
            if (str_contains($expression, 'view:')) {
                return $expression;
            }

            $maxIslandStatementCounter = max($maxIslandStatementCounter, $islandStatementCounter);

            return '[STARTISLAND:' . $islandStatementCounter++ . ']('.$expression.')';
        });

        $compiler->directive('endisland', function () use (&$islandStatementCounter) {
            return '[ENDISLAND:' . --$islandStatementCounter . ']';
        });

        if ($islandStatementCounter !== 1) {
            throw new \Exception('Start @island directive found without a matching @endisland directive');
        }

        $result = $compiler->compileStatementsMadePublic($this->mutableContents);

        for ($i=$maxIslandStatementCounter; $i >= $islandStatementCounter; $i--) {
            $result = preg_replace_callback('/(\[STARTISLAND:' . $i . '\])\((.*?)\)(.*?)(\[ENDISLAND:' . $i . '\])/s', function ($matches) use ($i) {
                $innerContent = $matches[3];

                if (str_contains($innerContent, '@placeholder')) {
                    $innerContent = str_replace('@placeholder', '<'.'?php if (isset($__placeholder)) { ob_start(); } if (isset($__placeholder)): ?'.'>', $innerContent);
                    $innerContent = str_replace('@endplaceholder', '<'.'?php endif; if (isset($__placeholder)) { echo ob_get_clean(); return; } ?'.'>', $innerContent);

                } else {
                    $innerContent = '<'.'?php if (isset($__placeholder)) { echo $__placeholder; return; } ?'.'>' . "\n\n" . $innerContent;
                }

                return $this->compileIsland(
                    occurrence: $i,
                    expression: $matches[2],
                    innerContent: $innerContent,
                );
            }, $result);
        }

        return $result;
    }

    public function compileIsland(int $occurrence, string $expression, string $innerContent): string
    {
        // Get the cached path for the extracted island...
        $hash = $this->getPathBasedHash($this->pathSignature);
        $token = $hash . '-' . $occurrence;
        $cachedPath = self::getCachedPathFromToken($token);

        // Build the output directive for the island...
        $output = '@island';

        if (trim($expression) === '') {
            $output .= '(token: \'' . $token . '\')';
        } else {
            $output .= '(' . $expression . ', token: \'' . $token . '\')';
        }

        // Ensure the cached directory exists...
        File::ensureDirectoryExists(dirname($cachedPath));

        // Write the cached island to the file system...
        file_put_contents($cachedPath, $innerContent);

        return $output;
    }

    public static function getCachedPathFromToken(string $token): string
    {
        $cachedDirectory = app('livewire.compiler')->cacheManager->cacheDirectory;

        return $cachedDirectory . '/islands/' . $token . '.blade.php';
    }

    public function getPathBasedHash(string $path): string
    {
        return app('livewire.compiler')->cacheManager->getHash(
            $this->pathSignature,
        );
    }

    public function getHackedBladeCompiler()
    {
        $instance = new class (
            app('files'),
            storage_path('framework/views/livewire'),
        ) extends \Illuminate\View\Compilers\BladeCompiler {
            /**
             * Make this method public...
             */
            public function compileStatementsMadePublic($template)
            {
                return $this->compileStatements($template);
            }

            /**
             * Tweak this method to only process custom directives so we
             * can restrict rendering solely to @island related directives...
             */
            protected function compileStatement($match)
            {
                if (str_contains($match[1], '@')) {
                    $match[0] = isset($match[3]) ? $match[1].$match[3] : $match[1];
                } elseif (isset($this->customDirectives[$match[1]])) {
                    $match[0] = $this->callCustomDirective($match[1], Arr::get($match, 3));
                } elseif (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
                    // Don't process through built-in directive methods...
                    // $match[0] = $this->$method(Arr::get($match, 3));

                    // Just return the original match...
                    return $match[0];
                } else {
                    return $match[0];
                }

                return isset($match[3]) ? $match[0] : $match[0].$match[2];
            }
        };

        return $instance;
    }
}
