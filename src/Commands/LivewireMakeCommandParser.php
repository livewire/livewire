<?php

namespace Livewire\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LivewireMakeCommandParser
{
    protected $appPath;
    protected $viewPath;
    protected $component;
    protected $componentClass;
    protected $directories;

    public function __construct($appPath, $viewPath, $rawCommand)
    {
        $this->appPath = rtrim($appPath, DIRECTORY_SEPARATOR) . '/';
        $this->viewPath = rtrim($viewPath, DIRECTORY_SEPARATOR) . '/';

        $directories = preg_split('/[.]+/', $rawCommand);

        $this->component = array_pop($directories);
        $this->componentClass = Str::studly($this->component);

        $this->directories = array_map([Str::class, 'studly'], $directories);
    }

    public function component()
    {
        return $this->component;
    }

    public function classPath()
    {
        return $this->appPath . collect()
            ->concat(['Http', 'Livewire'])
            ->concat($this->directories)
            ->push($this->classFile())
            ->implode(DIRECTORY_SEPARATOR);
    }

    public function classFile()
    {
        return $this->componentClass . '.php';
    }

    public function classNamespace()
    {
        return collect()
            ->concat(['App', 'Http', 'Livewire'])
            ->concat($this->directories)
            ->implode('\\');
    }

    public function className()
    {
        return $this->componentClass;
    }

    public function classContents()
    {
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Component.stub');

        return preg_replace_array(
            ['/\[namespace\]/', '/\[class\]/', '/\[view\]/'],
            [$this->classNamespace(), $this->className(), $this->viewName()],
            $template
        );
    }

    public function viewPath()
    {
        return $this->viewPath . collect()
            ->push('livewire')
            ->concat($this->directories)
            ->map([Str::class, 'kebab'])
            ->push($this->viewFile())
            ->implode(DIRECTORY_SEPARATOR);
    }

    public function viewFile()
    {
        return $this->component . '.blade.php';
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

    public function viewContents()
    {
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'view.stub');

        return preg_replace(
            '/\[quote\]/',
            $this->wisdomOfTheTao(),
            $template
        );
    }

    public function wisdomOfTheTao()
    {
        $wisdom = require(__DIR__ . DIRECTORY_SEPARATOR . 'the-tao.php');

        return Arr::random($wisdom);
    }
}
