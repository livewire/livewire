<?php

namespace Livewire\v4\Compiler;

class ParsedComponent
{
    public string $frontmatter;
    public string $viewContent;
    public bool $isExternal;
    public ?string $externalClass;

    public function __construct(
        string $frontmatter,
        string $viewContent,
        bool $isExternal = false,
        ?string $externalClass = null
    ) {
        $this->frontmatter = $frontmatter;
        $this->viewContent = $viewContent;
        $this->isExternal = $isExternal;
        $this->externalClass = $externalClass;
    }

    public function hasInlineClass(): bool
    {
        return !$this->isExternal && !empty(trim($this->frontmatter));
    }

    public function hasExternalClass(): bool
    {
        return $this->isExternal && !empty($this->externalClass);
    }

    public function getClassDefinition(): string
    {
        if ($this->isExternal) {
            return $this->externalClass;
        }

        return $this->frontmatter;
    }
}