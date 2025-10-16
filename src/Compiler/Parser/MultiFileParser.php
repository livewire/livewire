<?php

namespace Livewire\Compiler\Parser;

class MultiFileParser extends Parser
{
    public function __construct(
        public string $path,
        public ?string $scriptPortion,
        public string $classPortion,
        public string $viewPortion,
        public ?string $placeholderPortion,
    ) {}

    public static function parse(string $path): self
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
        $viewPortion = file_get_contents($viewPath);
        $placeholderPortion = static::extractPlaceholderPortion($viewPortion);

        return new self(
            $path,
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

    public function generateViewContents(): string
    {
        return trim($this->viewPortion);
    }

    public function generateScriptContents(): ?string
    {
        return $this->scriptPortion;
    }
}