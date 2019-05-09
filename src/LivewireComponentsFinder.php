<?php

namespace Livewire;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Livewire\LivewireComponent;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

class LivewireComponentsFinder
{
    protected $files;
    protected $path;
    protected $manifestPath;
    protected $manifest;

    public function __construct(Filesystem $files, $manifestPath, $path)
    {
        $this->files = $files;
        $this->path = $path;
        $this->manifestPath = $manifestPath;
    }

    public function find($alias)
    {
        return $this->getManifest()[$alias] ?? null;
    }

    public function getManifest()
    {
        if (! is_null($this->manifest)) {
            return $this->manifest;
        }

        if (! file_exists($this->manifestPath)) {
            $this->build();
        }

        return $this->manifest =  $this->files->getRequire($this->manifestPath);
    }

    public function build()
    {
        return dd($this->getClassNames());
    }

    public function getClassNames()
    {
        return collect($this->files->allFiles($this->path))
            ->map(function (SplFileInfo $file) {
                return app()->getNamespace().str_replace(
                        ['/', '.php'],
                        ['\\', ''],
                        Str::after($file->getPathname(), app_path().DIRECTORY_SEPARATOR)
                    );
            })
            ->filter(function (string $class) {
                return is_subclass_of($class, LivewireComponent::class) &&
                    ! (new ReflectionClass($class))->isAbstract();
            })
            ->toArray();
    }
}
