<?php

namespace Livewire\Compiler\Parser;

class SingleFileParser extends Parser
{
    public function __construct(
        public string $path,
        public string $contents,
        public ?string $scriptPortion,
        public string $classPortion,
        public ?string $placeholderPortion,
        public string $viewPortion,
    ) {}

    public static function parse(string $path): self
    {
        $contents = file_get_contents($path);

        $mutableContents = $contents;

        $scriptPortion = static::extractScriptPortion($mutableContents);
        $classPortion = static::extractClassPortion($mutableContents);
        $placeholderPortion = static::extractPlaceholderPortion($mutableContents);
        $viewPortion = trim($mutableContents);

        return new self(
            $path,
            $contents,
            $scriptPortion,
            $classPortion,
            $placeholderPortion,
            $viewPortion,
        );
    }

    public static function extractClassPortion(string &$contents): string
    {
        $pattern = '/<\?php\s*.*?\s*\?>/s';

        $classPortion = static::extractPattern($pattern, $contents);

        if ($classPortion === false) {
            throw new \Exception('Class contents not found');
        }

        return $classPortion;
    }

    public static function extractScriptPortion(string &$contents): ?string
    {
        // Get the view portion (everything after the PHP class)
        $viewContents = static::getViewPortion($contents);

        // Remove @script/@endscript blocks (let Livewire handle these normally)
        $viewContents = preg_replace('/@script\s*.*?@endscript/s', '', $viewContents);

        // Find script tags that are at the start of a line
        $pattern = '/(?:^|\n)\s*(<script\b[^>]*>.*?<\/script>)/s';
        preg_match_all($pattern, $viewContents, $matches);

        if (empty($matches[1])) {
            return null;
        }

        // Take the last script tag (most likely to be at root level)
        $scriptTag = end($matches[1]);

        // Remove it from the original contents
        $contents = str_replace($scriptTag, '', $contents);

        return $scriptTag;
    }

    private static function getViewPortion(string $contents): string
    {
        // Remove the PHP class portion to get just the view
        $classPattern = '/<\?php\s*.*?\s*\?>/s';
        $viewContents = preg_replace($classPattern, '', $contents);

        return trim($viewContents);
    }

    protected static function extractPattern(string $pattern, string &$contents): string | false
    {
        if (preg_match($pattern, $contents, $matches)) {
            $match = $matches[0];

            $contents = str_replace($match, '', $contents);

            return $match;
        }

        return false;
    }

    public function generateClassContents(string $viewFileName, ?string $placeholderFileName = null, ?string $scriptFileName = null): string
    {
        $classContents = trim($this->classPortion);

        $classContents = $this->stripTrailingPhpTag($classContents);
        $classContents = $this->ensureAnonymousClassHasReturn($classContents);
        $classContents = $this->ensureAnonymousClassHasTrailingSemicolon($classContents);
        $classContents = $this->injectViewMethod($classContents, $viewFileName);

        if ($placeholderFileName) {
            $classContents = $this->injectPlaceholderMethod($classContents, $placeholderFileName);
        }

        if ($scriptFileName) {
            $classContents = $this->injectScriptMethod($classContents, $scriptFileName);
        }

        return $classContents;
    }

    public function generateViewContents(): string
    {
        $viewContents = trim($this->viewPortion);

        $viewContents = $this->injectUseStatementsFromClassPortion($viewContents, $this->classPortion);

        return $viewContents;
    }

    public function generateScriptContents(): ?string
    {
        if ($this->scriptPortion === null) return null;

        $scriptContents = '';

        $pattern = '/<script\b([^>]*)>(.*?)<\/script>/s';

        if (preg_match($pattern, $this->scriptPortion, $matches)) {
            $scriptContents = trim($matches[2]);
        }

        // Hoist imports to the top
        $imports = [];
        $scriptContents = preg_replace_callback(
            '/^import\s+.+?;?\s*$/m',
            function ($match) use (&$imports) {
                $imports[] = trim($match[0]);
                return ''; // Remove from original position
            },
            $scriptContents
        );

        // Clean up any extra whitespace left by removed imports
        $scriptContents = trim($scriptContents);

        // Build the final script with hoisted imports and export function
        $hoistedImports = empty($imports) ? '' : implode("\n", $imports) . "\n";

        return <<<JS
{$hoistedImports}
export function run(\$wire, \$js, \$intercept) {
    {$scriptContents}
}
JS;
    }
}