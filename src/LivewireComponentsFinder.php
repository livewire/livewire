<?php

namespace Livewire;

use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class LivewireComponentsFinder
{
    protected $path;

    public function __construct()
    {
        $this->setPath(app_path('Http/Livewire'));
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path): void
    {
        $this->path = $path;
    }

    public function getClassNames(): array
    {
        return collect((new Finder)->in($this->path)->files())
            ->values()
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

    public function getDeclaredAliases(): array
    {
        return collect($this->getClassNames())
            ->map(function (string $class) {
                return [
                    'class' => $class,
                    'alias' => isset($class::$alias) ? $class::$alias : null,
                ];
            })
            ->filter(function (array $data) {
                return $data['alias'];
            })
            ->mapWithKeys(function (array $data) {
                return [$data['alias'] => $data['class']];
            })
            ->toArray();
    }

}
