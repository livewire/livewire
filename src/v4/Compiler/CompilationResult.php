<?php

namespace Livewire\V4\Compiler;

class CompilationResult
{
    public string $className;
    public string $classPath;
    public string $viewName;
    public string $viewPath;
    public bool $isExternal;
    public ?string $externalClass;
    public string $hash;

    public function __construct(
        string $className,
        string $classPath,
        string $viewName,
        string $viewPath,
        bool $isExternal = false,
        ?string $externalClass = null,
        string $hash = ''
    ) {
        $this->className = $className;
        $this->classPath = $classPath;
        $this->viewName = $viewName;
        $this->viewPath = $viewPath;
        $this->isExternal = $isExternal;
        $this->externalClass = $externalClass;
        $this->hash = $hash;
    }

    public function shouldGenerateClass(): bool
    {
        return !$this->isExternal;
    }

    public function getClassNamespace(): string
    {
        $parts = explode('\\', $this->className);
        array_pop($parts); // Remove class name
        return implode('\\', $parts);
    }

    public function getShortClassName(): string
    {
        $parts = explode('\\', $this->className);
        return array_pop($parts);
    }
}