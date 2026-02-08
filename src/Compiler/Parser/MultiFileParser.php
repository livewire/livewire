<?php

namespace Livewire\Compiler\Parser;

use Livewire\Compiler\Compiler;

class MultiFileParser extends Parser
{
    public function __construct(
        public string $path,
        public string $classPath,
        public string $viewPath,
        public ?string $scriptPortion,
        public string $classPortion,
        public string $viewPortion,
        public ?string $placeholderPortion,
    ) {}

    public static function parse(Compiler $compiler, string $path): self
    {
        $name = basename($path);

        // Strip out the emoji if it exists...
        $name = preg_replace('/⚡[\x{FE0E}\x{FE0F}]?/u', '', $name);

        $classPath = $path . '/' . $name . '.php';
        $viewPath = $path . '/' . $name . '.blade.php';
        $scriptPath = $path . '/' . $name . '.js';

        if (! file_exists($classPath)) {
            throw new \Exception('Class file not found: ' . $classPath);
        }

        if (! file_exists($viewPath)) {
            throw new \Exception('View file not found: ' . $viewPath);
        }

        $scriptPortion = file_exists($scriptPath) ? file_get_contents($scriptPath) : null;
        $classPortion = file_get_contents($classPath);
        $viewPortion = $compiler->prepareViewForCompilation(file_get_contents($viewPath), $viewPath);

        $placeholderPortion = static::extractPlaceholderPortion($viewPortion);

        return new self(
            $path,
            $classPath,
            $viewPath,
            $scriptPortion,
            $classPortion,
            $viewPortion,
            $placeholderPortion,
        );
    }

    public function generateClassContents(?string $viewFileName = null, ?string $placeholderFileName = null, ?string $scriptFileName = null): string
    {
        $classContents = trim($this->classPortion);

        $classContents = $this->stripTrailingPhpTag($classContents);
        $classContents = $this->ensureAnonymousClassHasReturn($classContents);
        $classContents = $this->ensureAnonymousClassHasTrailingSemicolon($classContents);

        // Inject #line directive after opening PHP tag to map errors back to source file
        $classContents = $this->injectSourceCommentAfterPhpTag($classContents);

        if ($viewFileName) {
            $classContents = $this->injectViewMethod($classContents, $viewFileName);
        }

        if ($placeholderFileName) {
            $classContents = $this->injectPlaceholderMethod($classContents, $placeholderFileName);
        }

        if ($scriptFileName) {
            $classContents = $this->injectScriptMethod($classContents, $scriptFileName);
        }

        return $classContents;
    }

    protected function injectSourceCommentAfterPhpTag(string $classContents): string
    {
        // Find the position after opening PHP tag and any following whitespace/newline
        if (preg_match('/^<\?php\s*\n?/', $classContents, $match)) {
            $phpTagWithWhitespace = $match[0];
            $restOfContents = substr($classContents, strlen($phpTagWithWhitespace));

            // Calculate start line (line after opening tag in the original file)
            $newlinesInTag = substr_count($phpTagWithWhitespace, "\n");
            $startLine = 1 + $newlinesInTag;

            // The #line directive tells PHP: "the next line is line N of file X"
            $lineDirective = $this->injectSourceComment('', $this->classPath, $startLine);

            return $phpTagWithWhitespace . $lineDirective . $restOfContents;
        }

        return $classContents;
    }

    public function generateViewContents(): string
    {
        $viewContents = trim($this->viewPortion);

        // Add source annotation for error handlers to reference
        $sourceAnnotation = sprintf("{{-- @livewireSource %s:1 --}}\n", $this->viewPath);

        return $sourceAnnotation . $viewContents;
    }

    public function generateScriptContents(): ?string
    {
        // Return null if there's no script content
        if ($this->scriptPortion === null || trim($this->scriptPortion) === '') {
            return null;
        }

        $scriptContents = trim($this->scriptPortion);

        return <<<JS
        export function run(\$wire, \$js) {
            {$scriptContents}
        }
        JS;
    }

    /**
     * Generate the complete single-file component contents (this is used for the convert command).
     */
    public function generateContentsForSingleFile(): string
    {
        // Clean up the class contents
        $classContents = trim($this->classPortion);

        // Remove the return statement if present
        $classContents = preg_replace('/return\s+new\s+class\s*\(/s', 'new class(', $classContents);

        // Ensure trailing semicolon is present
        if (! str_ends_with($classContents, ';')) {
            $classContents .= ';';
        }

        // Ensure it starts with opening PHP tag
        $phpOpen = '<' . '?php';
        if (! str_starts_with($classContents, $phpOpen)) {
            $classContents = $phpOpen . "\n\n" . $classContents;
        }

        // Ensure it ends with closing PHP tag
        $phpClose = '?' . '>';
        if (! str_ends_with($classContents, $phpClose)) {
            $classContents .= "\n" . $phpClose;
        }

        $sfcContents = $classContents . "\n\n" . trim($this->viewPortion);

        // Add script section if present
        if ($this->scriptPortion !== null && trim($this->scriptPortion) !== '') {
            $indentedScript = $this->addJavaScriptIndentation(trim($this->scriptPortion));
            $sfcContents .= "\n\n<script>\n" . $indentedScript . "\n</script>";
        }

        return $sfcContents;
    }

    protected function addJavaScriptIndentation(string $source): string
    {
        $lines = explode("\n", $source);

        if (empty($lines)) {
            return $source;
        }

        // Add 4 spaces to each non-empty line
        $indentedLines = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                $indentedLines[] = $line; // Keep empty lines as-is
            } else {
                $indentedLines[] = '    ' . $line;
            }
        }

        return implode("\n", $indentedLines);
    }
}