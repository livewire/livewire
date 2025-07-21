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
    public array $scripts;

    public function __construct(
        string $frontmatter,
        string $viewContent,
        bool $isExternal = false,
        ?string $externalClass = null,
        ?string $layoutTemplate = null,
        ?array $layoutData = null,
        array $scripts = [],
    ) {
        $this->frontmatter = $frontmatter;
        $this->viewContent = $viewContent;
        $this->isExternal = $isExternal;
        $this->externalClass = $externalClass;
        $this->layoutTemplate = $layoutTemplate;
        $this->layoutData = $layoutData;
        $this->scripts = $scripts;
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

    public function hasScripts(): bool
    {
        return !empty($this->scripts);
    }

    public function getClassSource(): string
    {
        return <<<PHP
        <?php

        {$this->frontmatter}
        PHP;
    }

    public function getViewSource(): string
    {
        return <<<HTML
        {$this->viewContent}
        HTML;
    }

    public function getScriptSource(): string
    {
        if (empty($this->scripts)) throw new \Exception('No scripts found');

        $script = $this->scripts[0];

        $fullTag = $script['fullTag'];

        return str($fullTag)->between('<script>', '</script>')->toString();
    }
}