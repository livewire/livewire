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

    public function addLocation($path = null, $class = null): void
    {
        if ($class !== null) $this->classNamespaces[] = $this->normalizeClassName($class);
        if ($path !== null) $this->viewLocations[] = $path;
    }

    public function addNamespace($namespace, $path = null, $class = null): void
    {
        if ($class !== null) $this->classNamespaces[$namespace] = $this->normalizeClassName($class);
        if ($path !== null) $this->viewNamespaces[$namespace] = $path;
    }

    public function addComponent($name = null, $path = null, $class = null): void
    {
        // Support $name being used a single argument for class-based components...
        if ($name !== null && $class === null && $path === null) {
            $class = $name;

            $name = null;
        }

        if (is_object($class)) {
            $class = get_class($class);
        }

        // Support $class being used a single named argument for class-based components...
        if ($name === null && $class !== null && $path === null) {
            $name = $this->generateHashName($class);
        }

        if ($name == null && $class === null && $path !== null) {
            throw new \Exception('You must provide a name when registering a single/multi-file component');
        }

        if ($name) {
            if ($class !== null) $this->classComponents[$name] = $this->normalizeClassName($class);
            elseif ($path !== null) $this->viewComponents[$name] = $path;
        }
    }

    public function normalizeName($nameComponentOrClass): ?string
    {
        if (is_object($nameComponentOrClass)) {
            $nameComponentOrClass = get_class($nameComponentOrClass);
        }

        $class = null;

        if (is_subclass_of($class = $nameComponentOrClass, Component::class)) {
            if (is_object($class)) {
                $class = get_class($class);
            }

            $name = array_search($class, $this->classComponents);

            if ($name !== false) {
                return $name;
            }

            $hashOfClass = $this->generateHashName($class);

            $name = $this->classComponents[$hashOfClass] ?? false;

            if ($name !== false) {
                return $name;
            }

            $result = $this->generateNameFromClass($class, $this->classNamespaces);

            return $result;
        }

        return $nameComponentOrClass;
    }

    protected function parseNamespaceAndName($name): array
    {
        if (str_contains($name, '::')) {
            [$namespace, $componentName] = explode('::', $name, 2);
            return [$namespace, $componentName];
        }

        return [null, $name];
    }

    public function resolveClassComponentClassName($name): ?string
    {
        [$namespace, $componentName] = $this->parseNamespaceAndName($name);

        if ($namespace !== null) {
            if (isset($this->classNamespaces[$namespace])) {
                $class = $this->generateClassFromName($componentName, [$this->classNamespaces[$namespace]]);

                if (class_exists($class)) {
                    return $class;
                }
            }

            return null;
        }

        if (isset($this->classComponents[$name])) {
            return $this->classComponents[$name];
        }

        $class = $this->generateClassFromName($name, $this->classNamespaces);

        if (! class_exists($class)) {
            return null;
        }

        return $class;
    }

    public function resolveSingleFileComponentPath($name): ?string
    {
        $path = null;

        [$namespace, $componentName] = $this->parseNamespaceAndName($name);

        if ($namespace !== null) {
            if (isset($this->viewNamespaces[$namespace])) {
                $locations = [$this->viewNamespaces[$namespace]];
            } else {
                return null;
            }
        } else {
            $componentName = $name;

            // Check if the component is explicitly registered...
            if (isset($this->viewComponents[$name])) {
                $path = $this->viewComponents[$name];

                if (! is_dir($path) && file_exists($path) && $this->hasValidSingleFileComponentSource($path)) {
                    return $path;
                }
            }

            $locations = $this->viewLocations;
        }

        // Check for a component inside locations...
        foreach ($locations as $location) {
            $location = $this->normalizeLocation($location);
            $segments = explode('.', $componentName);

            $lastSegment = last($segments);
            $leadingSegments = implode('.', array_slice($segments, 0, -1));

            $trailingPath = str_replace('.', '/', $lastSegment);
            $leadingPath = $leadingSegments ? str_replace('.', '/', $leadingSegments) . '/' : '';

            $paths = [
                'singleFileWithZap' => $location . '/' . $leadingPath . '⚡' . $trailingPath . '.blade.php',
                'singleFileWithZapVariation15' => $location . '/' . $leadingPath . '⚡︎' . $trailingPath . '.blade.php',
                'singleFileWithZapVariation16' => $location . '/' . $leadingPath . '⚡️' . $trailingPath . '.blade.php',
                'singleFileAsIndexWithZap' => $location . '/' . $leadingPath . $trailingPath . '/⚡︎index.blade.php',
                'singleFileAsIndexWithZapVariation15' => $location . '/' . $leadingPath . $trailingPath . '/⚡︎index.blade.php',
                'singleFileAsIndexWithZapVariation16' => $location . '/' . $leadingPath . $trailingPath . '/⚡️index.blade.php',
                'singleFileAsSelfNamedWithZap' => $location . '/' . $leadingPath . $trailingPath . '/' . '⚡︎' . $trailingPath . '.blade.php',
                'singleFileAsSelfNamedWithZapVariation15' => $location . '/' . $leadingPath . $trailingPath . '/' . '⚡︎' . $trailingPath . '.blade.php',
                'singleFileAsSelfNamedWithZapVariation16' => $location . '/' . $leadingPath . $trailingPath . '/' . '⚡️' . $trailingPath . '.blade.php',
                'singleFile' => $location . '/' . $leadingPath . $trailingPath . '.blade.php',
                'singleFileAsIndex' => $location . '/' . $leadingPath . $trailingPath . '/index.blade.php',
                'singleFileAsSelfNamed' => $location . '/' . $leadingPath . $trailingPath . '/' . $trailingPath . '.blade.php',
            ];

            foreach ($paths as $filePath) {
                if (! is_dir($filePath)
                    && file_exists($filePath)
                    && $this->hasValidSingleFileComponentSource($filePath)) {
                    return $filePath;
                }
            }
        }

        return $path;
    }

    public function resolveMultiFileComponentPath($name): ?string
    {
        $path = null;

        [$namespace, $componentName] = $this->parseNamespaceAndName($name);

        if ($namespace !== null) {
            if (isset($this->viewNamespaces[$namespace])) {
                $locations = [$this->viewNamespaces[$namespace]];
            } else {
                return null;
            }
        } else {
            $componentName = $name;

            // Check if the component is explicitly registered...
            if (isset($this->viewComponents[$name])) {
                $path = $this->viewComponents[$name];

                if (is_dir($path)) {
                    return $path;
                }
            }

            $locations = $this->viewLocations;
        }

        // Check for a multi-file component inside locations...
        foreach ($locations as $location) {
            $location = $this->normalizeLocation($location);

            $segments = explode('.', $componentName);

            $lastSegment = last($segments);
            $leadingSegments = implode('.', array_slice($segments, 0, -1));

            $trailingPath = str_replace('.', '/', $lastSegment);
            $leadingPath = $leadingSegments ? str_replace('.', '/', $leadingSegments) . '/' : '';


            $dirs = [
                'multiFileWithZap' => $location . '/' . $leadingPath . '⚡' . $trailingPath,
                'multiFileWithZapVariation15' => $location . '/' . $leadingPath . '⚡︎' . $trailingPath,
                'multiFileWithZapVariation16' => $location . '/' . $leadingPath . '⚡️' . $trailingPath,
                'multiFileAsIndexWithZap' => $location . '/' . $leadingPath . $trailingPath . '/⚡︎index',
                'multiFileAsIndexWithZapVariation15' => $location . '/' . $leadingPath . $trailingPath . '/⚡︎index',
                'multiFileAsIndexWithZapVariation16' => $location . '/' . $leadingPath . $trailingPath . '/⚡️index',
                'multiFileAsSelfNamedWithZap' => $location . '/' . $leadingPath . $trailingPath . '/' . '⚡' . $trailingPath,
                'multiFileAsSelfNamedWithZapVariation15' => $location . '/' . $leadingPath . $trailingPath . '/' . '⚡︎' . $trailingPath,
                'multiFileAsSelfNamedWithZapVariation16' => $location . '/' . $leadingPath . $trailingPath . '/' . '⚡️' . $trailingPath,
                'multiFile' => $location . '/' . $leadingPath . $trailingPath,
                'multiFileAsIndex' => $location . '/' . $leadingPath . $trailingPath . '/index',
                'multiFileAsSelfNamed' => $location . '/' . $leadingPath . $trailingPath . '/' . $trailingPath,
            ];

            foreach ($dirs as $dir) {
                $baseName = basename($dir);

                $fileBaseName = str_contains($baseName, 'index') ? 'index' : $baseName;

                if (
                    is_dir($dir)
                    && $this->hasValidMultiFileComponentSource($dir, $fileBaseName)
                ) {
                    return $dir;
                }
            }
        }

        return $path;
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

            if (class_exists($class)) return $this->normalizeClassName($class);
            if (class_exists($indexClass)) return $this->normalizeClassName($indexClass);
            if (class_exists($selfNamedClass)) return $this->normalizeClassName($selfNamedClass);
        }

        return $this->normalizeClassName($baseClass);
    }

    protected function generateNameFromClass($class, $classNamespaces = []): string
    {
        $class = str_replace(
            ['/', '\\'],
            '.',
            $this->normalizePath($class)
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
                $this->normalizePath($classNamespace)
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

    protected function normalizeClassName(string $className): string
    {
        return trim($className, '\\');
    }

    protected function generateHashName(string $className): string
    {
        return 'lw' . crc32($this->normalizeClassName($className));
    }

    protected function normalizePath(string $path): string
    {
        return trim(trim($path, '/'), '\\');
    }

    protected function normalizeLocation(string $location): string
    {
        return rtrim($location, '/');
    }

    protected function hasValidSingleFileComponentSource(string $filePath): bool
    {
        // Read the file contents
        $contents = file_get_contents($filePath);

        if ($contents === false) {
            return false;
        }

        // Light touch check: Look for the pattern that indicates an SFC
        // Pattern: <?php followed by 'new' and 'class' (with potential attributes/newlines between)
        // This distinguishes SFCs from regular Blade views
        return preg_match('/\<\?php.*new\s+.*class/s', $contents) === 1;
    }

    protected function hasValidMultiFileComponentSource(string $dir, string $fileBaseName): bool
    {
        return file_exists($dir . '/' . $fileBaseName . '.php')
            || file_exists($dir . '/' . $fileBaseName . '.blade.php');
    }

    public function resolveSingleFileComponentPathForCreation(string $name): string
    {
        [$namespace, $componentName] = $this->parseNamespaceAndName($name);

        // Get the appropriate location
        if ($namespace !== null && isset($this->viewNamespaces[$namespace])) {
            $location = $this->viewNamespaces[$namespace];
        } else {
            // Use the first configured component location or fallback
            $location = $this->viewLocations[0] ?? resource_path('views/components');
        }

        $location = $this->normalizeLocation($location);

        // Parse the component name into path segments
        $segments = explode('.', $componentName ?? $name);
        $lastSegment = array_pop($segments);
        $leadingPath = !empty($segments) ? implode('/', $segments) . '/' : '';

        // Determine if emoji should be used (get from config)
        $useEmoji = config('livewire.make_command.emoji', true);
        $prefix = $useEmoji ? '⚡' : '';

        // Build the file path
        return $location . '/' . $leadingPath . $prefix . $lastSegment . '.blade.php';
    }

    public function resolveMultiFileComponentPathForCreation(string $name): string
    {
        [$namespace, $componentName] = $this->parseNamespaceAndName($name);

        // Get the appropriate location
        if ($namespace !== null && isset($this->viewNamespaces[$namespace])) {
            $location = $this->viewNamespaces[$namespace];
        } else {
            // Use the first configured component location or fallback
            $location = $this->viewLocations[0] ?? resource_path('views/components');
        }

        $location = $this->normalizeLocation($location);

        // Parse the component name into path segments
        $segments = explode('.', $componentName ?? $name);
        $lastSegment = array_pop($segments);
        $leadingPath = !empty($segments) ? implode('/', $segments) . '/' : '';

        // Determine if emoji should be used (get from config)
        $useEmoji = config('livewire.make_command.emoji', true);
        $prefix = $useEmoji ? '⚡' : '';

        // Build the directory path
        return $location . '/' . $leadingPath . $prefix . $lastSegment;
    }

    public function resolveClassComponentFilePaths(string $name): array
    {
        [$namespace, $componentName] = $this->parseNamespaceAndName($name);

        // Parse the component name into segments
        $segments = explode('.', $componentName ?? $name);

        // Convert segments to StudlyCase for class name
        $classSegments = array_map(fn($segment) => str($segment)->studly()->toString(), $segments);
        $className = implode('\\', $classSegments);

        // Convert segments to kebab-case for view name
        $viewSegments = array_map(fn($segment) => str($segment)->kebab()->toString(), $segments);
        $viewName = implode('.', $viewSegments);

        // Build the class file path
        $classPath = app_path('Livewire/' . str_replace('\\', '/', $className) . '.php');

        // Build the view file path using the configured view path
        $configuredViewPath = config('livewire.view_path', resource_path('views/livewire'));
        $viewPath = $configuredViewPath . '/' . str_replace('.', '/', $viewName) . '.blade.php';

        return [
            'class' => $classPath,
            'view' => $viewPath,
        ];
    }
}