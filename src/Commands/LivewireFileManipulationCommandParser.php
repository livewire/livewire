<?php

namespace Livewire\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LivewireFileManipulationCommandParser
{
    public $component;
    public $newComponent;

    public function __construct($appPath, $viewPath, $rawCommand, $newRawCommand = null)
    {
        $this->component = new LivewireComponentParser($appPath, $viewPath, $rawCommand);
        $this->newComponent = new LivewireComponentParser($appPath, $viewPath, $newRawCommand);
    }

    public function classContents()
    {
        $template = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Component.stub');

        return preg_replace_array(
            ['/\[namespace\]/', '/\[class\]/', '/\[view\]/'],
            [$this->component->classNamespace(), $this->component->className(), $this->component->viewName()],
            $template
        );
    }

    public function newClassContents()
    {
        $originalFile = file_get_contents($this->component->classPath());

        $escapedClassNamespace = preg_replace('/\\\/', '\\\\\\', $this->component->classNamespace());

        return preg_replace_array(
            ["/namespace {$escapedClassNamespace}/", "/class {$this->component->className()}/", "/{$this->component->viewName()}/"],
            ["namespace {$this->newComponent->classNamespace()}", "class {$this->newComponent->className()}", $this->newComponent->viewName()],
            $originalFile
        );
    }
}
