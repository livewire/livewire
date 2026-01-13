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

        $currentNestingLevel = 1;
        $maxNestingLevel = $currentNestingLevel;
        $startDirectiveCount = 1;

        $compiler->directive('island', function ($expression) use (&$currentNestingLevel, &$maxNestingLevel, &$startDirectiveCount) {
            $maxNestingLevel = max($maxNestingLevel, $currentNestingLevel);

            return '[STARTISLAND:' . $startDirectiveCount++ . ':' . $currentNestingLevel++ . ']('.$expression.')';
        });

        $compiler->directive('endisland', function () use (&$currentNestingLevel) {
            return '[ENDISLAND:' . --$currentNestingLevel . ']';
        });

        if ($currentNestingLevel !== 1) {
            throw new \Exception('Start @island directive found without a matching @endisland directive');
        }

        $result = $compiler->compileStatementsMadePublic($this->mutableContents);

        for ($i=$maxNestingLevel; $i >= $currentNestingLevel; $i--) {
            $result = preg_replace_callback('/(\[STARTISLAND:([0-9]+):' . $i . '\])\((.*?)\)(.*?)(\[ENDISLAND:' . $i . '\])/s', function ($matches) use ($i) {
                $occurrence = $matches[2];
                $innerContent = $matches[4];
                $expression = $matches[3];

                if (str_contains($innerContent, '@placeholder')) {
                    $innerContent = str_replace('@placeholder', '<'.'?php if (isset($__placeholder)) { ob_start(); } if (isset($__placeholder)): ?'.'>', $innerContent);
                    $innerContent = str_replace('@endplaceholder', '<'.'?php endif; if (isset($__placeholder)) { echo ob_get_clean(); return; } ?'.'>', $innerContent);

                } else {
                    $innerContent = '<'.'?php if (isset($__placeholder)) { echo $__placeholder; return; } ?'.'>' . "\n\n" . $innerContent;
                }

                return $this->compileIsland(
                    occurrence: $occurrence,
                    nestingLevel: $i,
                    expression: $expression,
                    innerContent: $innerContent,
                );
            }, $result);
        }

        return $result;
    }

    public function compileIsland(int $occurrence, int $nestingLevel, string $expression, string $innerContent): string
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

        // Inject scope provider code at the top of the island view...
        $scopeProviderCode = $this->generateScopeProviderCode($expression);
        $innerContent = $scopeProviderCode . $innerContent;

        // Ensure the cached directory exists...
        File::ensureDirectoryExists(dirname($cachedPath));

        // Write the cached island to the file system...
        file_put_contents($cachedPath, $innerContent);

        app('livewire.compiler')->cacheManager->mutateFileModificationTime($cachedPath);

        return $output;
    }

    protected function generateScopeProviderCode(string $expression): string
    {
        $directiveWithExtraction = '';

        // Only extract directive's "with" if there's an expression
        if (trim($expression) !== '') {
            $directiveWithExtraction = <<<PHP
// Extract directive's "with" parameter (overrides component properties)
\$__islandScope = (function(\$name = null, \$token = null, \$lazy = false, \$defer = false, \$always = false, \$skip = false, \$with = []) {
    return \$with;
})({$expression});
if (!empty(\$__islandScope)) {
    extract(\$__islandScope, EXTR_OVERWRITE);
}


PHP;
        }

        // Always include runtime "with" extraction (even if directive has no parameters)
        return <<<PHP
<?php
{$directiveWithExtraction}// Extract runtime "with" parameter if provided (overrides everything)
if (isset(\$__runtimeWith) && is_array(\$__runtimeWith) && !empty(\$__runtimeWith)) {
    extract(\$__runtimeWith, EXTR_OVERWRITE);
}
?>

PHP;
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
