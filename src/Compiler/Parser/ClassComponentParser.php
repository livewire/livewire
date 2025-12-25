<?php

namespace Livewire\Compiler\Parser;

use Livewire\Compiler\Compiler;

class ClassComponentParser extends Parser
{
    public function __construct(
        public string $classPath,
        public string $viewPath,
        public string $classContents,
        public string $viewContents,
        public array $useStatements,
        public array $traitStatements,
        public string $classBody,
        public ?string $customViewPath,
        public ?string $placeholderPortion,
    ) {}

    public static function parse(Compiler $compiler, string $classPath, string $viewPath): self
    {
        if (! file_exists($classPath)) {
            throw new \Exception('Class file not found: ' . $classPath);
        }

        if (! file_exists($viewPath)) {
            throw new \Exception('View file not found: ' . $viewPath);
        }

        $classContents = file_get_contents($classPath);
        $viewContents = $compiler->prepareViewForCompilation(file_get_contents($viewPath), $viewPath);

        $useStatements = static::extractUseStatements($classContents);
        $traitStatements = static::extractTraitStatements($classContents);
        $classBody = static::extractClassBody($classContents);
        $customViewPath = static::extractCustomViewPath($classContents);
        $placeholderPortion = static::extractPlaceholderPortion($viewContents);

        return new self(
            $classPath,
            $viewPath,
            $classContents,
            $viewContents,
            $useStatements,
            $traitStatements,
            $classBody,
            $customViewPath,
            $placeholderPortion,
        );
    }

    public function canConvert(): array
    {
        $errors = [];

        if ($this->hasInlineView()) {
            $errors[] = 'Component has an inline view (render() returns a string instead of view()). Cannot convert.';
        }

        return $errors;
    }

    protected function hasInlineView(): bool
    {
        // Look for render method in the class body
        if (! preg_match('/function\s+render\s*\([^)]*\)\s*(?::\s*[^\{]+)?\s*\{/s', $this->classBody)) {
            return false; // No render method found, view is auto-resolved
        }

        // Extract the render method body
        $renderBody = $this->extractRenderMethodBody();

        if ($renderBody === null) {
            return false;
        }

        // Check for heredoc/nowdoc returns
        if (preg_match('/return\s+<<</', $renderBody)) {
            return true;
        }

        // Check for string literal returns that look like HTML
        if (preg_match('/return\s+[\'"]</', $renderBody)) {
            return true;
        }

        // Check for Blade::render() calls
        if (preg_match('/return\s+.*Blade\s*::\s*render\s*\(/', $renderBody)) {
            return true;
        }

        return false;
    }

    protected function extractRenderMethodBody(): ?string
    {
        // Find the render method
        if (! preg_match('/function\s+render\s*\([^)]*\)\s*(?::\s*[^\{]+)?\s*\{/s', $this->classBody, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $startPos = $match[0][1] + strlen($match[0][0]) - 1; // Position of opening brace

        return $this->extractBracedContent($this->classBody, $startPos);
    }

    protected function extractBracedContent(string $contents, int $startPos): ?string
    {
        $braceCount = 0;
        $started = false;
        $endPos = $startPos;

        for ($i = $startPos; $i < strlen($contents); $i++) {
            $char = $contents[$i];

            if ($char === '{') {
                $braceCount++;
                $started = true;
            } elseif ($char === '}') {
                $braceCount--;

                if ($started && $braceCount === 0) {
                    $endPos = $i + 1;
                    break;
                }
            }
        }

        return substr($contents, $startPos, $endPos - $startPos);
    }

    protected static function extractUseStatements(string $contents): array
    {
        $useStatements = [];

        // Match use statements before the class declaration
        // We need to find content between <?php and the class declaration
        if (preg_match('/^<\?php\s*(.*?)(?:(?:abstract\s+)?class\s+)/s', $contents, $preambleMatch)) {
            $preamble = $preambleMatch[1];

            if (preg_match_all('/^use\s+[^;]+;/m', $preamble, $matches)) {
                foreach ($matches[0] as $statement) {
                    $useStatements[] = trim($statement);
                }
            }
        }

        return $useStatements;
    }

    protected static function extractTraitStatements(string $contents): array
    {
        $traits = [];

        // Extract the class body first
        $classBody = static::extractClassBody($contents);

        // Match trait use statements (use TraitName; or use TraitA, TraitB;)
        // These appear at the beginning of the class body
        if (preg_match_all('/^\s*use\s+([^;{]+)(?:;|\s*\{)/m', $classBody, $matches)) {
            foreach ($matches[0] as $statement) {
                $traits[] = trim($statement);
            }
        }

        return $traits;
    }

    protected static function extractClassBody(string $contents): string
    {
        // Find the class declaration and opening brace
        if (! preg_match('/(?:abstract\s+)?class\s+\w+\s*(?:extends\s+[^\{]+)?\s*(?:implements\s+[^\{]+)?\s*\{/s', $contents, $match, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $classStartPos = $match[0][1];
        $openBracePos = $classStartPos + strlen($match[0][0]) - 1;

        // Find the matching closing brace
        $braceCount = 0;
        $started = false;
        $bodyStart = $openBracePos + 1;
        $bodyEnd = $openBracePos;

        for ($i = $openBracePos; $i < strlen($contents); $i++) {
            $char = $contents[$i];

            if ($char === '{') {
                $braceCount++;
                $started = true;
            } elseif ($char === '}') {
                $braceCount--;

                if ($started && $braceCount === 0) {
                    $bodyEnd = $i;
                    break;
                }
            }
        }

        return substr($contents, $bodyStart, $bodyEnd - $bodyStart);
    }

    protected static function extractCustomViewPath(string $contents): ?string
    {
        // Look for view('custom.path') or $this->view('custom.path') in render method
        if (preg_match('/return\s+(?:\$this->)?view\s*\(\s*[\'"]([^\'"]+)[\'"]/s', $contents, $match)) {
            return $match[1];
        }

        return null;
    }

    public function getResolvedViewContents(): string
    {
        if ($this->customViewPath !== null) {
            try {
                $viewFinder = app('view')->getFinder();
                $resolvedPath = $viewFinder->find($this->customViewPath);

                if (file_exists($resolvedPath)) {
                    return file_get_contents($resolvedPath);
                }
            } catch (\Exception $e) {
                // Fall back to default view contents
            }
        }

        return $this->viewContents;
    }

    public function generateContentsForSingleFile(): string
    {
        $useStatements = $this->filterUseStatementsForConversion($this->useStatements);
        $useBlock = implode("\n", $useStatements);

        $classBody = $this->generateAnonymousClassBody();

        $php = "<?php\n\n";
        if ($useBlock) {
            $php .= $useBlock . "\n\n";
        }
        $php .= "new class extends Component\n{\n";
        $php .= $classBody;
        $php .= "};\n?>\n\n";

        $viewContents = $this->getResolvedViewContents();

        return $php . trim($viewContents);
    }

    public function generateClassContentsForMultiFile(): string
    {
        $useStatements = $this->filterUseStatementsForConversion($this->useStatements);
        $useBlock = implode("\n", $useStatements);

        $classBody = $this->generateAnonymousClassBody();

        $php = "<?php\n\n";
        if ($useBlock) {
            $php .= $useBlock . "\n\n";
        }
        $php .= "new class extends Component\n{\n";
        $php .= $classBody;
        $php .= "};";

        return $php;
    }

    public function generateViewContentsForMultiFile(): string
    {
        $viewContents = trim($this->getResolvedViewContents());

        if ($this->placeholderPortion) {
            $viewContents = '@placeholder' . $this->placeholderPortion . '@endplaceholder' . "\n\n" . $viewContents;
        }

        return $viewContents;
    }

    protected function filterUseStatementsForConversion(array $useStatements): array
    {
        return array_filter($useStatements, function ($statement) {
            // Keep use statements (they're valid in SFC/MFC)
            // Filter out namespace declarations if any slipped through
            return ! preg_match('/^namespace\s/', $statement);
        });
    }

    protected function generateAnonymousClassBody(): string
    {
        $body = $this->classBody;

        // Remove the render method if it's a standard view() call
        $body = $this->removeStandardRenderMethod($body);

        // Keep trait statements as-is (they work in anonymous classes)
        // Keep properties and methods

        // Normalize indentation
        $body = $this->normalizeIndentation($body);

        return $body;
    }

    protected function removeStandardRenderMethod(string $body): string
    {
        // Pattern to match a render method that just returns a view
        $pattern = '/\s*(public\s+)?function\s+render\s*\([^)]*\)\s*(?::\s*[^\{]+)?\s*\{\s*return\s+(?:\$this->)?view\s*\([^)]*\)\s*;\s*\}/s';

        return preg_replace($pattern, '', $body);
    }

    protected function normalizeIndentation(string $body): string
    {
        $lines = explode("\n", $body);
        $result = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $result[] = '';
                continue;
            }

            // Keep the line as-is, it should already have proper indentation
            $result[] = $line;
        }

        // Trim leading and trailing empty lines
        while (! empty($result) && trim($result[0]) === '') {
            array_shift($result);
        }
        while (! empty($result) && trim(end($result)) === '') {
            array_pop($result);
        }

        return implode("\n", $result) . "\n";
    }
}
