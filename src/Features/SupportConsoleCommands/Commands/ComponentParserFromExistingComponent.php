<?php

namespace Livewire\Features\SupportConsoleCommands\Commands;

class ComponentParserFromExistingComponent extends ComponentParser
{
    protected $existingParser;

    public function __construct($classNamespace, $viewPath, $rawCommand, $existingParser)
    {
        $this->existingParser = $existingParser;

        parent::__construct($classNamespace, $viewPath, $rawCommand);
    }

    public function classContents($inline = false)
    {
        $originalFile = file_get_contents($this->existingParser->classPath());

        $escapedClassNamespace = preg_replace('/\\\/', '\\\\\\', $this->existingParser->classNamespace());

        return preg_replace_array(
            ["/namespace {$escapedClassNamespace}/", "/class {$this->existingParser->className()}/", "/{$this->existingParser->viewName()}/"],
            ["namespace {$this->classNamespace()}", "class {$this->className()}", $this->viewName()],
            $originalFile
        );
    }
}
