<?php

namespace Livewire\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LivewireFileManipulationCommandParser
{
    protected $appPath;
    protected $viewPath;
    protected $component;
    protected $newComponent;
    protected $componentClass;
    protected $newComponentClass;
    protected $directories;
    protected $newDirectories;

    public function __construct($appPath, $viewPath, $rawCommand, $newRawCommand = null)
    {
        $this->appPath = rtrim($appPath, DIRECTORY_SEPARATOR).'/';
        $this->viewPath = rtrim($viewPath, DIRECTORY_SEPARATOR).'/';

        $directories = preg_split('/[.]+/', $rawCommand);
        $newDirectories = preg_split('/[.]+/', $newRawCommand);

        $this->component = Str::kebab(array_pop($directories));
        $this->componentClass = Str::studly($this->component);

        $this->newComponent = Str::kebab(array_pop($newDirectories));
        $this->newComponentClass = Str::studly($this->newComponent);

        $this->directories = array_map([Str::class, 'studly'], $directories);
        $this->newDirectories = array_map([Str::class, 'studly'], $newDirectories);
    }

    public function component()
    {
        return $this->component;
    }

    public function newComponent()
    {
        return $this->newComponent;
    }

    public function classPath()
    {
        return $this->appPath.collect()
            ->concat(['Http', 'Livewire'])
            ->concat($this->directories)
            ->push($this->classFile())
            ->implode(DIRECTORY_SEPARATOR);
    }

    public function newClassPath()
    {
        return $this->appPath.collect()
            ->concat(['Http', 'Livewire'])
            ->concat($this->newDirectories)
            ->push($this->newClassFile())
            ->implode(DIRECTORY_SEPARATOR);
    }

    public function relativeClassPath()
    {
        return Str::replaceFirst(base_path().'/', '', $this->classPath());
    }

    public function relativeNewClassPath()
    {
        return Str::replaceFirst(base_path().'/', '', $this->newClassPath());
    }

    public function classFile()
    {
        return $this->componentClass.'.php';
    }

    public function newClassFile()
    {
        return $this->newComponentClass.'.php';
    }

    public function classNamespace()
    {
        return collect()
            ->concat(['App', 'Http', 'Livewire'])
            ->concat($this->directories)
            ->implode('\\');
    }

    public function newClassNamespace()
    {
        return collect()
            ->concat(['App', 'Http', 'Livewire'])
            ->concat($this->newDirectories)
            ->implode('\\');
    }

    public function className()
    {
        return $this->componentClass;
    }

    public function newClassName()
    {
        return $this->newComponentClass;
    }

    public function classContents()
    {
        $template = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Component.stub');

        return preg_replace_array(
            ['/\[namespace\]/', '/\[class\]/', '/\[view\]/'],
            [$this->classNamespace(), $this->className(), $this->viewName()],
            $template
        );
    }

    public function newClassContents()
    {
        $originalFile = file_get_contents($this->classPath());

        $escapedClassNamespace = preg_replace('/\\\/', '\\\\\\', $this->classNamespace());

        return preg_replace_array(
            ["/namespace {$escapedClassNamespace}/", "/class {$this->className()}/", "/{$this->viewName()}/"],
            ["namespace {$this->newClassNamespace()}", "class {$this->newClassName()}", $this->newViewName()],
            $originalFile
        );
    }

    public function viewPath()
    {
        return $this->viewPath.collect()
            ->push('livewire')
            ->concat($this->directories)
            ->map([Str::class, 'kebab'])
            ->push($this->viewFile())
            ->implode(DIRECTORY_SEPARATOR);
    }

    public function newViewPath()
    {
        return $this->viewPath.collect()
            ->push('livewire')
            ->concat($this->newDirectories)
            ->map([Str::class, 'kebab'])
            ->push($this->newViewFile())
            ->implode(DIRECTORY_SEPARATOR);
    }

    public function relativeViewPath()
    {
        return Str::replaceFirst(base_path().'/', '', $this->viewPath());
    }

    public function relativeNewViewPath()
    {
        return Str::replaceFirst(base_path().'/', '', $this->newViewPath());
    }

    public function viewFile()
    {
        return $this->component.'.blade.php';
    }

    public function newViewFile()
    {
        return $this->newComponent.'.blade.php';
    }

    public function viewName()
    {
        return collect()
            ->push('livewire')
            ->concat($this->directories)
            ->map([Str::class, 'kebab'])
            ->push($this->component)
            ->implode('.');
    }

    public function newViewName()
    {
        return collect()
            ->push('livewire')
            ->concat($this->newDirectories)
            ->map([Str::class, 'kebab'])
            ->push($this->newComponent)
            ->implode('.');
    }

    public function viewContents()
    {
        $template = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'view.stub');

        return preg_replace(
            '/\[quote\]/',
            $this->wisdomOfTheTao(),
            $template
        );
    }

    public function wisdomOfTheTao()
    {
        $wisdom = require __DIR__.DIRECTORY_SEPARATOR.'the-tao.php';

        return Arr::random($wisdom);
    }
}
