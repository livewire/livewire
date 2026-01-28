<?php

namespace Livewire\Compiler\Parser;

use Livewire\Compiler\Compiler;

class SingleFileParser extends Parser
{
    public function __construct(
        public string $path,
        public string $contents,
        public ?string $scriptPortion,
        public ?string $stylePortion,
        public ?string $globalStylePortion,
        public string $classPortion,
        public ?string $placeholderPortion,
        public string $viewPortion,
    ) {}

    public static function parse(Compiler $compiler, string $path): self
    {
        $contents = file_get_contents($path);

        $mutableContents = $compiler->prepareViewForCompilation($contents, $path);

        $scriptPortion = static::extractScriptPortion($mutableContents);
        $stylePortion = static::extractStylePortion($mutableContents);
        $globalStylePortion = static::extractGlobalStylePortion($mutableContents);
        $classPortion = static::extractClassPortion($mutableContents);
        $placeholderPortion = static::extractPlaceholderPortion($mutableContents);

        $viewPortion = trim($mutableContents);

        return new self(
            $path,
            $contents,
            $scriptPortion,
            $stylePortion,
            $globalStylePortion,
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

        // Remove @assets/@endassets blocks (let Livewire handle these normally)
        $viewContents = preg_replace('/@assets\s*.*?@endassets/s', '', $viewContents);

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

    public static function extractStylePortion(string &$contents): ?string
    {
        // Get the view portion (everything after the PHP class)
        $viewContents = static::getViewPortion($contents);

        // Find style tags that are at the start of a line, but NOT <style global>
        $pattern = '/(?:^|\n)\s*(<style\b(?![^>]*\bglobal\b)[^>]*>.*?<\/style>)/s';
        preg_match_all($pattern, $viewContents, $matches);

        if (empty($matches[1])) {
            return null;
        }

        // Take the last style tag (most likely to be at root level)
        $styleTag = end($matches[1]);

        // Remove it from the original contents
        $contents = str_replace($styleTag, '', $contents);

        return $styleTag;
    }

    public static function extractGlobalStylePortion(string &$contents): ?string
    {
        // Get the view portion (everything after the PHP class)
        $viewContents = static::getViewPortion($contents);

        // Find style tags with "global" attribute
        $pattern = '/(?:^|\n)\s*(<style\b[^>]*\bglobal\b[^>]*>.*?<\/style>)/s';
        preg_match_all($pattern, $viewContents, $matches);

        if (empty($matches[1])) {
            return null;
        }

        // Take the last global style tag
        $styleTag = end($matches[1]);

        // Remove it from the original contents
        $contents = str_replace($styleTag, '', $contents);

        return $styleTag;
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

    public function generateClassContents(?string $viewFileName = null, ?string $placeholderFileName = null, ?string $scriptFileName = null, ?string $styleFileName = null, ?string $globalStyleFileName = null): string
    {
        $classContents = trim($this->classPortion);

        $classContents = $this->stripTrailingPhpTag($classContents);
        $classContents = $this->ensureAnonymousClassHasReturn($classContents);
        $classContents = $this->ensureAnonymousClassHasTrailingSemicolon($classContents);

        if ($viewFileName) {
            $classContents = $this->injectViewMethod($classContents, $viewFileName);
        }

        if ($placeholderFileName) {
            $classContents = $this->injectPlaceholderMethod($classContents, $placeholderFileName);
        }

        if ($scriptFileName) {
            $classContents = $this->injectScriptMethod($classContents, $scriptFileName);
        }

        if ($styleFileName) {
            $classContents = $this->injectStyleMethod($classContents, $styleFileName);
        }

        if ($globalStyleFileName) {
            $classContents = $this->injectGlobalStyleMethod($classContents, $globalStyleFileName);
        }

        return $classContents;
    }

    /**
     * Generate the class contents for a multi-file component (this is used for the upgrade command).
     */
    public function generateClassContentsForMultiFile(): string
    {
        $classContents = trim($this->classPortion);

        $classContents = $this->stripTrailingPhpTag($classContents);
        $classContents = $this->ensureAnonymousClassHasTrailingSemicolon($classContents);

        return $classContents;
    }

    public function generateViewContents(): string
    {
        $viewContents = trim($this->viewPortion);

        $viewContents = $this->injectUseStatementsFromClassPortion($viewContents, $this->classPortion);

        return $viewContents;
    }

    public function generateViewContentsForMultiFile(): string
    {
        $viewContents = trim($this->viewPortion);

        if ($this->placeholderPortion) {
            $viewContents = $this->injectPlaceholderDirective($viewContents, $this->placeholderPortion);
        }

        return $viewContents;
    }

    protected function injectPlaceholderDirective(string $viewContents, string $placeholderPortion): string
    {
        return '@placeholder' . $placeholderPortion . '@endplaceholder' . "\n\n" . $viewContents;
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
        export function run(\$wire, \$js) {
            {$scriptContents}
        }
        JS;
    }

    public function generateScriptContentsForMultiFile(): ?string
    {
        if ($this->scriptPortion === null) return null;

        $scriptContents = '';

        $pattern = '/<script\b([^>]*)>(.*?)<\/script>/s';

        if (preg_match($pattern, $this->scriptPortion, $matches)) {
            $scriptContents = $matches[2];
        }

        return $this->cleanupJavaScriptIndentation($scriptContents);
    }

    public function generateStyleContents(): ?string
    {
        if ($this->stylePortion === null) return null;

        $pattern = '/<style\b[^>]*>(.*?)<\/style>/s';

        if (preg_match($pattern, $this->stylePortion, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public function generateGlobalStyleContents(): ?string
    {
        if ($this->globalStylePortion === null) return null;

        $pattern = '/<style\b[^>]*>(.*?)<\/style>/s';

        if (preg_match($pattern, $this->globalStylePortion, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public function generateStyleContentsForMultiFile(): ?string
    {
        if ($this->stylePortion === null) return null;

        $styleContents = '';

        $pattern = '/<style\b[^>]*>(.*?)<\/style>/s';

        if (preg_match($pattern, $this->stylePortion, $matches)) {
            $styleContents = $matches[1];
        }

        return $this->cleanupCssIndentation($styleContents);
    }

    public function generateGlobalStyleContentsForMultiFile(): ?string
    {
        if ($this->globalStylePortion === null) return null;

        $styleContents = '';

        $pattern = '/<style\b[^>]*>(.*?)<\/style>/s';

        if (preg_match($pattern, $this->globalStylePortion, $matches)) {
            $styleContents = $matches[1];
        }

        return $this->cleanupCssIndentation($styleContents);
    }

    protected function cleanupCssIndentation(string $source): string
    {
        $lines = explode("\n", $source);

        if (empty($lines)) {
            return $source;
        }

        // Find the minimum indentation level (excluding empty lines)
        $minIndentation = null;
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue; // Skip empty lines
            }

            // Count leading whitespace
            $indentation = strlen($line) - strlen(ltrim($line));

            if ($minIndentation === null || $indentation < $minIndentation) {
                $minIndentation = $indentation;
            }
        }

        // If no indentation found, return as-is
        if ($minIndentation === null || $minIndentation === 0) {
            return $source;
        }

        // Remove the minimum indentation from all lines
        $cleanedLines = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                $cleanedLines[] = $line; // Keep empty lines as-is
            } else {
                $cleanedLines[] = substr($line, $minIndentation);
            }
        }

        return implode("\n", $cleanedLines);
    }

    protected function cleanupJavaScriptIndentation(string $source): string
    {
        $lines = explode("\n", $source);

        if (empty($lines)) {
            return $source;
        }

        // Find the minimum indentation level (excluding empty lines)
        $minIndentation = null;
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue; // Skip empty lines
            }

            // Count leading whitespace
            $indentation = strlen($line) - strlen(ltrim($line));

            if ($minIndentation === null || $indentation < $minIndentation) {
                $minIndentation = $indentation;
            }
        }

        // If no indentation found, return as-is
        if ($minIndentation === null || $minIndentation === 0) {
            return $source;
        }

        // Remove the minimum indentation from all lines
        $cleanedLines = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                $cleanedLines[] = $line; // Keep empty lines as-is
            } else {
                $cleanedLines[] = substr($line, $minIndentation);
            }
        }

        return implode("\n", $cleanedLines);
    }
}