<?php

namespace Livewire\Finder;

use Livewire\Component;

class Finder
{
    protected $viewLocations = [];

    protected $classNamespaces = [];

    protected $viewNamespaces = [];

    protected $classComponents = [];

    protected $viewComponents = [];

    // Memoization caches
    protected $classNameCache = [];

    protected $singleFileComponentPathCache = [];

    protected $multiFileComponentPathCache = [];

    protected $normalizedNameCache = [];

    public function __construct()
    {
        $this->classNamespaces[] = config('livewire.class_namespace');
    }

    public function addLocation($classNamespace = null, $viewPath = null): void
    {
        if ($classNamespace !== null) $this->classNamespaces[] = trim($classNamespace, '\\');
        if ($viewPath !== null) $this->viewLocations[] = $viewPath;
    }


    public function addNamespace($namespace, $classNamespace = null, $viewPath = null): void
    {
        if ($classNamespace !== null) $this->classNamespaces[$namespace] = trim($classNamespace, '\\');
        if ($viewPath !== null) $this->viewNamespaces[$namespace] = $viewPath;
    }

    public function addComponent($name = null, $className = null, $viewPath = null): void
    {
        if ($name == null && $className === null && $viewPath !== null) {
            throw new \Exception('You must provide a name when registering a single/multi-file component');
        }

        if ($name) {
            if ($className !== null) $this->classComponents[$name] = trim($className, '\\');
            elseif ($viewPath !== null) $this->viewComponents[$name] = $viewPath;
        } else {
            if ($className !== null) {
                $this->classComponents[crc32(trim($className, '\\'))] = trim($className, '\\');
            }
        }
    }

    public function resolveClassName($name): ?string
    {
        // Check memoization cache first
        if (isset($this->classNameCache[$name])) {
            return $this->classNameCache[$name];
        }

        if (isset($this->classComponents[$name])) {
            $this->classNameCache[$name] = $this->classComponents[$name];
            return $this->classComponents[$name];
        }

        $class = $this->generateClassFromName($name, $this->classNamespaces);

        if (! class_exists($class)) {
            $this->classNameCache[$name] = null;
            return null;
        }

        $this->classNameCache[$name] = $class;
        return $class;
    }

    public function resolveSingleFileComponentPath($name): ?string
    {
        // Check memoization cache first
        if (isset($this->singleFileComponentPathCache[$name])) {
            return $this->singleFileComponentPathCache[$name];
        }

        $path = null;

        // Check if the component is explicitly registered...
        if (isset($this->viewComponents[$name])) {
            $path = $this->viewComponents[$name];

            if (! is_dir($path) && file_exists($path)) {
                $this->singleFileComponentPathCache[$name] = $path;
                return $path;
            }
        }

        // Check for a component inside locations...
        foreach ($this->viewLocations as $location) {
            $location = rtrim($location, '/');


            $segments = explode('.', $name);

            $lastSegment = last($segments);
            $leadingSegments = implode('.', array_slice($segments, 0, -1));

            $trailingPath = str_replace('.', '/', $lastSegment);
            $leadingPath = $leadingSegments ? str_replace('.', '/', $leadingSegments) . '/' : '';

            $paths = [
                'singleFile' => $location . '/' . $leadingPath . $trailingPath . '.blade.php',
                'singleFileWithZap' => $location . '/' . $leadingPath . '⚡︎' . $trailingPath . '.blade.php',
                'singleFileAsIndex' => $location . '/' . $leadingPath . $trailingPath . '/index.blade.php',
                'singleFileAsIndexWithZap' => $location . '/' . $leadingPath . $trailingPath . '/⚡︎index.blade.php',
                'singleFileAsSelfNamed' => $location . '/' . $leadingPath . $trailingPath . '/' . $trailingPath . '.blade.php',
                'singleFileAsSelfNamedWithZap' => $location . '/' . $leadingPath . $trailingPath . '/' . '⚡︎' . $trailingPath . '.blade.php'
            ];

            foreach ($paths as $path) {
                if (! is_dir($path) && file_exists($path)) {
                    $this->singleFileComponentPathCache[$name] = $path;
                    return $path;
                }
            }
        }

        $this->singleFileComponentPathCache[$name] = $path;
        return $path;
    }

    public function resolveMultiFileComponentPath($name): ?string
    {
        // Check memoization cache first
        if (isset($this->multiFileComponentPathCache[$name])) {
            return $this->multiFileComponentPathCache[$name];
        }

        $path = null;

        // Check if the component is explicitly registered...
        if (isset($this->viewComponents[$name])) {
            $path = $this->viewComponents[$name];

            if (is_dir($path)) {
                $this->multiFileComponentPathCache[$name] = $path;
                return $path;
            }
        }

        // Check for a multi-file component inside locations...
        foreach ($this->viewLocations as $location) {
            $location = rtrim($location, '/');

            $segments = explode('.', $name);

            $lastSegment = last($segments);
            $leadingSegments = implode('.', array_slice($segments, 0, -1));

            $trailingPath = str_replace('.', '/', $lastSegment);
            $leadingPath = $leadingSegments ? str_replace('.', '/', $leadingSegments) . '/' : '';

            $paths = [
                'multiFile' => $location . '/' . $leadingPath . $trailingPath,
                'multiFileWithZap' => $location . '/' . $leadingPath . '⚡︎' . $trailingPath,
                'multiFileAsIndex' => $location . '/' . $leadingPath . $trailingPath . '/index',
                'multiFileAsIndexWithZap' => $location . '/' . $leadingPath . $trailingPath . '/⚡︎index',
                'multiFileAsSelfNamed' => $location . '/' . $leadingPath . $trailingPath . '/' . $trailingPath,
                'multiFileAsSelfNamedWithZap' => $location . '/' . $leadingPath . $trailingPath . '/' . '⚡︎' . $trailingPath,
            ];

            foreach ($paths as $path) {
                if (is_dir($path)) {
                    $this->multiFileComponentPathCache[$name] = $path;
                    return $path;
                }
            }
        }

        $this->multiFileComponentPathCache[$name] = $path;
        return $path;
    }

    public function normalizeName($nameComponentOrClass): string
    {
        // Create a cache key that works for both strings and objects
        $cacheKey = is_object($nameComponentOrClass) ? get_class($nameComponentOrClass) : $nameComponentOrClass;

        // Check memoization cache first
        if (isset($this->normalizedNameCache[$cacheKey])) {
            return $this->normalizedNameCache[$cacheKey];
        }

        $class = null;

        if (is_subclass_of($nameComponentOrClass, Component::class) && is_object($nameComponentOrClass)) {
            $name = $nameComponentOrClass->getName();

            if (! $name) {
                throw new \Exception('Component must have a name to be normalized');
            }

            $this->normalizedNameCache[$cacheKey] = $name;
            return $name;
        }

        if (is_subclass_of($class = $nameComponentOrClass, Component::class)) {
            $name = array_search($class, $this->classComponents);

            if ($name !== false) {
                $this->normalizedNameCache[$cacheKey] = $name;
                return $name;
            }

            $result = $this->generateNameFromClass($class, $this->classNamespaces);
            $this->normalizedNameCache[$cacheKey] = $result;
            return $result;
        }

        $this->normalizedNameCache[$cacheKey] = $nameComponentOrClass;
        return $nameComponentOrClass;
    }

    protected function generateClassFromName($name, $classNamespaces = [])
    {
        $baseClass = collect(str($name)->explode('.'))
            ->map(fn ($segment) => (string) str($segment)->studly())
            ->join('\\');

        foreach ($classNamespaces as $classNamespace) {
            $class = '\\' . $classNamespace . '\\' . $baseClass;
            $indexClass = '\\' . $classNamespace . '\\' . $baseClass . '\\Index';
            $lastSegment = last(explode('.', $name));
            $selfNamedClass = '\\' . $classNamespace . '\\' . $baseClass . '\\' . str($lastSegment)->studly();

            if (class_exists($class)) return $class;
            if (class_exists($indexClass)) return $indexClass;
            if (class_exists($selfNamedClass)) return $selfNamedClass;
        }

        return $baseClass;
    }

    protected function generateNameFromClass($class, $classNamespaces = []): string
    {
        $class = str_replace(
            ['/', '\\'],
            '.',
            trim(trim($class, '/'), '\\')
        );

        $fullName = str(collect(explode('.', $class))
            ->map(fn ($i) => \Illuminate\Support\Str::kebab($i))
            ->implode('.'));

        if ($fullName->startsWith('.')) {
            $fullName = $fullName->substr(1);
        }

        // If using an index component in a sub folder, remove the '.index' so the name is the subfolder name...
        if ($fullName->endsWith('.index')) {
            $fullName = $fullName->replaceLast('.index', '');
        }

        // If using a self-named component in a sub folder, remove the '.[last_segment]' so the name is the subfolder name...
        $segments = explode('.', $fullName);
        $lastSegment = end($segments);
        $secondToLastSegment = $segments[count($segments) - 2];

        if ($secondToLastSegment && $lastSegment === $secondToLastSegment) {
            $fullName = $fullName->replaceLast('.' . $lastSegment, '');
        }

        foreach ($classNamespaces as $classNamespace) {
            $namespace = str_replace(
                ['/', '\\'],
                '.',
                trim(trim($classNamespace, '/'), '\\')
            );

            $namespace = collect(explode('.', $namespace))
                ->map(fn ($i) => \Illuminate\Support\Str::kebab($i))
                ->implode('.');

            if ($fullName->startsWith($namespace)) {
                return (string) $fullName->substr(strlen($namespace) + 1);
            }
        }

        return (string) $fullName;
    }
}