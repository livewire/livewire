<?php

namespace Livewire\Compiler\Parser;

class SingleFileParser extends Parser
{
    public function __construct(
        public string $path,
        public string $contents,
        public ?string $scriptPortion,
        public string $classPortion,
        public string $viewPortion,
    ) {}

    public static function parse(string $path): self
    {
        $contents = file_get_contents($path);

        $mutableContents = $contents;

        $scriptPortion = static::extractScriptPortion($mutableContents);
        $classPortion = static::extractClassPortion($mutableContents);
        $viewPortion = trim($mutableContents);

        return new self(
            $path,
            $contents,
            $scriptPortion,
            $classPortion,
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
        $pattern = '/(<script\b[^>]*>.*?<\/script>)/s';

        $scriptPortion = static::extractPattern($pattern, $contents);

        return $scriptPortion ?? null;
    }

    public static function extractPattern(string $pattern, string &$contents): string | false
    {
        if (preg_match($pattern, $contents, $matches)) {
            $match = $matches[0];

            $contents = str_replace($match, '', $contents);

            return $match;
        }

        return false;
    }

    public function generateClassContents(string $viewFileName): string
    {
        $classContents = trim($this->classPortion);

        $classContents = $this->stripTrailingPhpTag($classContents);
        $classContents = $this->ensureAnonymousClassHasReturn($classContents);
        $classContents = $this->injectViewMethod($classContents, $viewFileName);

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

        return $scriptContents;
    }
}