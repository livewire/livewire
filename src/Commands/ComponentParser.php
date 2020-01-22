<?php

namespace Livewire\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ComponentParser
{
    protected $appPath;
    protected $viewPath;
    protected $component;
    protected $componentClass;
    protected $directories;
    protected $stubClassPath = null;
    protected $stubViewPath = null;

    public function __construct($classNamespace, $viewPath, $rawCommand, $stub = null)
    {
        $this->baseClassNamespace = $classNamespace;

        $classPath = static::generatePathFromNamespace($classNamespace);

        $this->baseClassPath = rtrim($classPath, DIRECTORY_SEPARATOR).'/';
        $this->baseViewPath = rtrim($viewPath, DIRECTORY_SEPARATOR).'/';

        $directories = preg_split('/[.]+/', $rawCommand);

        $this->component = Str::kebab(array_pop($directories));
        $this->componentClass = Str::studly($this->component);

        $this->stubViewPath = $this->baseViewPath.'stubs/'.Str::kebab($stub).'.stub';
        $this->stubClassPath = $this->baseClassPath.'Stubs/'.Str::studly(Str::kebab($stub)).'.stub';

        $this->directories = array_map([Str::class, 'studly'], $directories);
    }

    public function component()
    {
        return $this->component;
    }

    public function classPath()
    {
        return $this->baseClassPath.collect()
            ->concat($this->directories)
            ->push($this->classFile())
            ->implode(DIRECTORY_SEPARATOR);
    }

    public function relativeClassPath()
    {
        return Str::replaceFirst(base_path().'/', '', $this->classPath());
    }

    public function classFile()
    {
        return $this->componentClass.'.php';
    }

    public function classNamespace()
    {
        return empty($this->directories)
            ? $this->baseClassNamespace
            : $this->baseClassNamespace.'\\'.collect()
                ->concat($this->directories)
                ->map([Str::class, 'studly'])
                ->implode('\\');
    }

    public function className()
    {
        return $this->componentClass;
    }

    public function classContents()
    {
        if(File::exists($this->stubClassPath)) {
            $template = file_get_contents($this->stubClassPath);
        } else {
            $template = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'Component.stub');
        }

        return preg_replace_array(
            ['/\[namespace\]/', '/\[class\]/', '/\[view\]/'],
            [$this->classNamespace(), $this->className(), $this->viewName()],
            $template
        );
    }

    public function viewPath()
    {
        return $this->baseViewPath.collect()
            ->concat($this->directories)
            ->map([Str::class, 'kebab'])
            ->push($this->viewFile())
            ->implode(DIRECTORY_SEPARATOR);
    }

    public function relativeViewPath()
    {
        return Str::replaceFirst(base_path().'/', '', $this->viewPath());
    }

    public function viewFile()
    {
        return $this->component.'.blade.php';
    }

    public function viewName()
    {
        return collect()
            ->concat(explode('/',Str::after($this->baseViewPath, resource_path('views'))))
            ->filter()
            ->concat($this->directories)
            ->map([Str::class, 'kebab'])
            ->push($this->component)
            ->implode('.');
    }

    public function viewContents()
    {
        if(File::exists($this->stubViewPath)) {
            $template = file_get_contents($this->stubViewPath);
        } else {
            $template = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'view.stub');
        }

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

    public static function generatePathFromNamespace($namespace)
    {
        $name = Str::replaceFirst(app()->getNamespace(), '', $namespace);

        return app('path').'/'.str_replace('\\', '/', $name);
    }
}
