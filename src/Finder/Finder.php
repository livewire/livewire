<?php

namespace Livewire\Finder;

use Livewire\Component;

class Finder
{
    private const ZAP = "\u{26A1}";
    private const ZAP_VS15 = "\u{26A1}\u{FE0E}";
    private const ZAP_VS16 = "\u{26A1}\u{FE0F}";

    protected $classLocations = [];

    protected $viewLocations = [];

    protected $classNamespaces = [];

    protected $viewNamespaces = [];

    protected $classComponents = [];

    protected $viewComponents = [];

    public function addComponent($name = null, $viewPath = null, $class = null): void
    {
        // Support $name being used a single argument for class-based components...
        if ($name !== null && $class === null && $viewPath === null) {
            $class = $name;

            $name = null;
        }

        if (is_object($class)) {
            $class = get_class($class);
        }

        // Support $class being used a single named argument for class-based components...
        if ($name === null && $class !== null && $viewPath === null) {
            $name = $this->generateHashName($class);
        }

        if ($name == null && $class === null && $viewPath !== null) {
            throw new \Exception('You must provide a name when registering a single/multi-file component');
        }

        if ($name) {
            if ($class !== null) $this->classComponents[$name] = $this->normalizeClassName($class);
            elseif ($viewPath !== null) $this->viewComponents[$name] = $viewPath;
        }
    }

    public function addLocation($viewPath = null, $classNamespace = null): void
    {
        if ($classNamespace !== null) $this->classLocations[] = $this->normalizeClassName($classNamespace);
        if ($viewPath !== null) $this->viewLocations[] = $viewPath;
    }

    public function addNamespace($namespace, $viewPath = null, $classNamespace = null, $classPath = null, $classViewPath = null): void
    {
        if ($classNamespace !== null) {
            $this->classNamespaces[$namespace] = [
                'classNamespace' => $this->normalizeClassName($classNamespace),
                'classPath' => $classPath,
                'classViewPath' => $classViewPath,
            ];
        }
        if ($viewPath !== null) $this->viewNamespaces[$namespace] = $viewPath;
    }

    public function getClassNamespace(string $namespace): array
    {
        return $this->classNamespaces[$namespace];
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

            $result = $this->generateNameFromClass($class);

            return $result;
        }

        return $nameComponentOrClass;
    }

    public function parseNamespaceAndName($name): array
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

        // Check if the component is in a namespace...
        if ($namespace !== null) {
            if (isset($this->classNamespaces[$namespace]['classNamespace'])) {
                $class = $this->generateClassFromName($componentName, [$this->classNamespaces[$namespace]['classNamespace']]);

                if (class_exists($class)) {
                    return $class;
                }
            }

            return null;
        }

        // Check if the component is explicitly registered...
        if (isset($this->classComponents[$name])) {
            return $this->classComponents[$name];
        }

        // Check if the component is in a class location...
        $class = $this->generateClassFromName($name, $this->classLocations);

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
                'singleFileWithZap' => $location . '/' . $leadingPath . self::ZAP . $trailingPath . '.blade.php',
                'singleFileWithZapVariation15' => $location . '/' . $leadingPath . self::ZAP_VS15 . $trailingPath . '.blade.php',
                'singleFileWithZapVariation16' => $location . '/' . $leadingPath . self::ZAP_VS16 . $trailingPath . '.blade.php',
                'singleFileAsIndexWithZap' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP . 'index.blade.php',
                'singleFileAsIndexWithZapVariation15' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP_VS15 . 'index.blade.php',
                'singleFileAsIndexWithZapVariation16' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP_VS16 . 'index.blade.php',
                'singleFileAsSelfNamedWithZap' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP . $trailingPath . '.blade.php',
                'singleFileAsSelfNamedWithZapVariation15' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP_VS15 . $trailingPath . '.blade.php',
                'singleFileAsSelfNamedWithZapVariation16' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP_VS16 . $trailingPath . '.blade.php',
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
                'multiFileWithZap' => $location . '/' . $leadingPath . self::ZAP . $trailingPath,
                'multiFileWithZapVariation15' => $location . '/' . $leadingPath . self::ZAP_VS15 . $trailingPath,
                'multiFileWithZapVariation16' => $location . '/' . $leadingPath . self::ZAP_VS16 . $trailingPath,
                'multiFileAsIndexWithZap' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP . 'index',
                'multiFileAsIndexWithZapVariation15' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP_VS15 . 'index',
                'multiFileAsIndexWithZapVariation16' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP_VS16 . 'index',
                'multiFileAsSelfNamedWithZap' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP . $trailingPath,
                'multiFileAsSelfNamedWithZapVariation15' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP_VS15 . $trailingPath,
                'multiFileAsSelfNamedWithZapVariation16' => $location . '/' . $leadingPath . $trailingPath . '/' . self::ZAP_VS16 . $trailingPath,
                'multiFile' => $location . '/' . $leadingPath . $trailingPath,
                'multiFileAsIndex' => $location . '/' . $leadingPath . $trailingPath . '/index',
                'multiFileAsSelfNamed' => $location . '/' . $leadingPath . $trailingPath . '/' . $trailingPath,
            ];

            foreach ($dirs as $dir) {
                $baseName = basename($dir);

                $fileBaseName = str_contains($baseName, 'index') ? 'index' : $baseName;

                // Strip out the emoji from folder name to derive the file name...
                $fileBaseName = preg_replace('/' . self::ZAP . '[\x{FE0E}\x{FE0F}]?/u', '', $fileBaseName);

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

    protected function generateClassFromName($name, $classNamespaces = []): string
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

    protected function generateNameFromClass($class): string
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

        if (count($segments) >= 2) {
            $lastSegment = end($segments);
            $secondToLastSegment = $segments[count($segments) - 2];

            if ($secondToLastSegment && $lastSegment === $secondToLastSegment) {
                $fullName = $fullName->replaceLast('.' . $lastSegment, '');
            }
        }

        $classNamespaces = collect($this->classNamespaces)
            ->map(fn ($classNamespace) => $classNamespace['classNamespace'])
            ->merge($this->classLocations)
            ->toArray();

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
            && file_exists($dir . '/' . $fileBaseName . '.blade.php');
    }

    public function resolveSingleFileComponentPathForCreation(string $name): ?string
    {
        [$namespace, $componentName] = $this->parseNamespaceAndName($name);

        // Get the appropriate location
        if ($namespace !== null) {
            if (isset($this->viewNamespaces[$namespace])) {
                $location = $this->viewNamespaces[$namespace];
            } else {
                // Namespace specified but not registered
                return null;
            }
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
        $prefix = $useEmoji ? self::ZAP : '';

        // Build the file path
        return $location . '/' . $leadingPath . $prefix . $lastSegment . '.blade.php';
    }

    public function resolveMultiFileComponentPathForCreation(string $name): ?string
    {
        [$namespace, $componentName] = $this->parseNamespaceAndName($name);

        // Get the appropriate location
        if ($namespace !== null) {
            if (isset($this->viewNamespaces[$namespace])) {
                $location = $this->viewNamespaces[$namespace];
            } else {
                // Namespace specified but not registered
                return null;
            }
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
        $prefix = $useEmoji ? self::ZAP : '';

        // Build the directory path
        return $location . '/' . $leadingPath . $prefix . $lastSegment;
    }

    public function resolveClassComponentFilePaths(string $name): ?array
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

        if ($namespace !== null) {
            if (! isset($this->classNamespaces[$namespace])) {
                return null;
            }

            $classNamespaceDetails  = $this->classNamespaces[$namespace];

            $configuredClassPath = $classNamespaceDetails['classPath'];
            $configuredViewPath = $classNamespaceDetails['classViewPath'];
        } else {
            $configuredClassPath = config('livewire.class_path', app_path('Livewire'));
            $configuredViewPath = config('livewire.view_path', resource_path('views/livewire'));
        }

        // Build the class file path
        $classPath = $configuredClassPath . '/' . str_replace('\\', '/', $className) . '.php';

        // Build the view file path
        $viewPath = $configuredViewPath . '/' . str_replace('.', '/', $viewName) . '.blade.php';

        return [
            'class' => $classPath,
            'view' => $viewPath,
        ];
    }
}