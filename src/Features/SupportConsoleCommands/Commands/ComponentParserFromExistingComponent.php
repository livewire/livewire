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

    public function testContents($testType = 'phpunit')
    {
        $file_content = file_get_contents($this->existingParser->testPath());

        $escapedTestNamespace = preg_replace('/\\\/', '\\\\\\', $this->existingParser->testNamespace());
        $escapedClassWithNamespace = preg_replace('/\\\/', '\\\\\\', $this->existingParser->classNamespace() . '\\' . $this->existingParser->className());

        $replaces = [
            "/namespace {$escapedTestNamespace}/"              => 'namespace ' . $this->testNamespace(),
            "/use {$escapedClassWithNamespace}/"               => 'use ' . $this->classNamespace() . '\\' . $this->className(),
            "/class {$this->existingParser->testClassName()}/" => 'class ' . $this->testClassName(),
            "/{$this->existingParser->className()}::class/"    => $this->className() . '::class',
        ];

        return preg_replace(array_keys($replaces), array_values($replaces), $file_content);
    }
}
