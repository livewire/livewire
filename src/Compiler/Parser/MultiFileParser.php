<?php

namespace Livewire\Compiler\Parser;

class MultiFileParser extends Parser
{
    public function __construct(
        public string $path,
        public ?string $scriptPortion,
        public string $classPortion,
        public string $viewPortion,
    ) {}

    public static function parse(string $path): self
    {
        $name = basename($path);

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

        return new self(
            $path,
            $scriptPortion,
            $classPortion,
            $viewPortion,
        );
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
        return trim($this->viewPortion);
    }

    public function generateScriptContents(): ?string
    {
        return $this->scriptPortion;
    }
}