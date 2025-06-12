<?php

namespace Livewire\V4\Compiler;

class ParsedComponent
{
    public string $frontmatter;
    public string $viewContent;
    public bool $isExternal;
    public ?string $externalClass;
    public ?string $layoutTemplate;
    public ?array $layoutData;
    public array $inlinePartials;

    public function __construct(
        string $frontmatter,
        string $viewContent,
        bool $isExternal = false,
        ?string $externalClass = null,
        ?string $layoutTemplate = null,
        ?array $layoutData = null,
        array $inlinePartials = []
    ) {
        $this->frontmatter = $frontmatter;
        $this->viewContent = $viewContent;
        $this->isExternal = $isExternal;
        $this->externalClass = $externalClass;
        $this->layoutTemplate = $layoutTemplate;
        $this->layoutData = $layoutData;
        $this->inlinePartials = $inlinePartials;
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

    public function hasLayout(): bool
    {
        return !empty($this->layoutTemplate);
    }

    public function hasInlinePartials(): bool
    {
        return !empty($this->inlinePartials);
    }
}