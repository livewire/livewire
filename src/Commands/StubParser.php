<?php

namespace Livewire\Commands;

use Illuminate\Support\Str;

class StubParser extends ComponentParser
{

    public function __construct($classNamespace, $viewPath, $rawCommand)
    {
        $this->baseClassNamespace = $classNamespace;

        $classPath = static::generatePathFromNamespace($classNamespace);

        $this->baseClassPath = rtrim($classPath, DIRECTORY_SEPARATOR).'/Stubs/';
        $this->baseViewPath = rtrim($viewPath, DIRECTORY_SEPARATOR).'/stubs/';

        $directories = preg_split('/[.]+/', $rawCommand);

        $this->component = Str::kebab(array_pop($directories));
        $this->componentClass = Str::studly($this->component);

        $this->directories = array_map([Str::class, 'studly'], $directories);
    }

    public function classFile()
    {
        return $this->componentClass.'.stub';
    }

    public function viewFile()
    {
        return $this->component.'.stub';
    }

    public function classContents()
    {
        return file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Component.stub');
    }
}
