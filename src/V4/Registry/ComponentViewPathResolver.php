<?php

namespace Livewire\V4\Registry;

use Livewire\Mechanisms\Mechanism;
use Livewire\V4\Registry\Exceptions\ViewNotFoundException;

class ComponentViewPathResolver extends Mechanism
{
    protected $aliases = [];
    protected $namespaces = [];
    protected $defaultViewPaths = [];
    protected $supportedExtensions = [];

    function __construct($defaultViewPaths = null, $supportedExtensions = null)
    {
        $this->defaultViewPaths = $defaultViewPaths ?: [
            resource_path('views/components'),
            resource_path('views/livewire'),
        ];

        $this->supportedExtensions = $supportedExtensions ?: ['.livewire.php', '.blade.php'];
    }

    function setSupportedExtensions(array $extensions)
    {
        $this->supportedExtensions = $extensions;
    }

    function component($componentName, $componentViewPath)
    {
        $this->aliases[$componentName] = $componentViewPath;
    }

    function namespace($namespaceName, $directoryPath)
    {
        $this->namespaces[$namespaceName] = rtrim($directoryPath, '/');
    }

    function resolve($name)
    {
        // Check if it's a direct alias registration first...
        if (isset($this->aliases[$name])) {
            $path = $this->aliases[$name];

            if (file_exists($path)) {
                return $path;
            }

            throw new ViewNotFoundException("Component view file not found: [{$path}]");
        }

        // Handle namespaced components (e.g., "foo::some-component")...
        if (str_contains($name, '::')) {
            return $this->resolveNamespacedComponent($name);
        }

        // Try to resolve from default view directories...
        return $this->resolveFromDefaultDirectories($name);
    }

    protected function resolveNamespacedComponent($name)
    {
        [$namespace, $componentName] = explode('::', $name, 2);

        if (! isset($this->namespaces[$namespace])) {
            throw new ViewNotFoundException("Namespace [{$namespace}] is not registered");
        }

        $basePath = $this->namespaces[$namespace];

        return $this->tryViewResolutionPaths($basePath, $componentName);
    }

    protected function resolveFromDefaultDirectories($name)
    {
        foreach ($this->defaultViewPaths as $basePath) {
            try {
                return $this->tryViewResolutionPaths($basePath, $name);
            } catch (ViewNotFoundException $e) {
                // Continue to next directory...
                continue;
            }
        }

        throw new ViewNotFoundException("Unable to find component view: [{$name}]");
    }

    protected function tryViewResolutionPaths($basePath, $name)
    {
        // Convert dots to directory separators (e.g., "foo.bar" becomes "foo/bar")...
        $path = str_replace('.', '/', $name);

        // Try each supported extension with each convention
        foreach ($this->supportedExtensions as $extension) {
            // Convention 1: foo.blade.php or foo.livewire.php
            $candidate = $basePath . '/' . $path . $extension;
            if (file_exists($candidate)) {
                // For .blade.php files, only treat as Livewire component if it contains ⚡
                if ($extension === '.blade.php' && !str_contains($candidate, '⚡')) {
                    continue; // Skip non-⚡ blade files
                }
                return $candidate;
            }
            
            // For both .livewire.php and .blade.php files, check for versions with ⚡ in the filename
            if ($extension === '.livewire.php' || $extension === '.blade.php') {
                // Try various positions of ⚡ in the filename
                $parentDir = dirname($basePath . '/' . $path);
                $componentBaseName = basename($path);
                
                // If we're at the root level (no subdirectory), use basePath as the parent
                if ($parentDir === $basePath) {
                    $parentDir = $basePath;
                }
                
                if (is_dir($parentDir)) {
                    // Look for any file that contains both the component name and ⚡
                    $patterns = [
                        $parentDir . '/*⚡*' . $extension,  // ⚡ anywhere
                        $parentDir . '/⚡*' . $extension,   // ⚡ at beginning
                        $parentDir . '/*⚡' . $extension,   // ⚡ at end (before extension)
                    ];
                    
                    foreach ($patterns as $pattern) {
                        $files = glob($pattern);
                        
                        foreach ($files as $file) {
                            $filename = basename($file, $extension);
                            // Check if this file matches our component (ignoring ⚡)
                            if (str_replace('⚡', '', $filename) === $componentBaseName) {
                                // For .blade.php files, we already know it has ⚡ so it's valid
                                return $file;
                            }
                        }
                    }
                }
            }
        }

        // PRIORITY: Check for multi-file component directory BEFORE checking subdirectory files
        // Convention 2: Check all directories that might be multi-file components
        // First check exact match
        $directoryCandidate = $basePath . '/' . $path;
        if (is_dir($directoryCandidate)) {
            // For directories with ⚡ in the name, check for multi-file component structure
            if (str_contains($directoryCandidate, '⚡')) {
                // Use the base component name (without ⚡) for the files inside
                $componentName = str_replace('⚡', '', basename($path));
                $livewireFile = $directoryCandidate . '/' . $componentName . '.php';
                $bladeFile = $directoryCandidate . '/' . $componentName . '.blade.php';

                // Check if both required multi-file component files exist
                if (file_exists($livewireFile) && file_exists($bladeFile)) {
                    return $directoryCandidate; // Return directory path for multi-file components
                }
            } else {
                // For regular directories without ⚡, still check the old convention
                $componentName = basename($path);
                $livewireFile = $directoryCandidate . '/' . $componentName . '.php';
                $bladeFile = $directoryCandidate . '/' . $componentName . '.blade.php';

                // Check if both required multi-file component files exist
                if (file_exists($livewireFile) && file_exists($bladeFile)) {
                    return $directoryCandidate; // Return directory path for multi-file components
                }
            }
        }

        // Also check for directories in parent path that contain ⚡
        $parentPath = dirname($basePath . '/' . $path);
        $componentBaseName = basename($path);
        if (is_dir($parentPath)) {
            $dirs = glob($parentPath . '/*⚡*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                $dirBaseName = basename($dir);
                // Check if this directory name matches our component (ignoring ⚡ position)
                if (str_replace('⚡', '', $dirBaseName) === $componentBaseName) {
                    $livewireFile = $dir . '/' . $componentBaseName . '.php';
                    $bladeFile = $dir . '/' . $componentBaseName . '.blade.php';
                    
                    if (file_exists($livewireFile) && file_exists($bladeFile)) {
                        return $dir; // Return directory path for multi-file components
                    }
                }
            }
        }

        // Continue with remaining single-file conventions
        foreach ($this->supportedExtensions as $extension) {
            // Convention 3: foo/foo.blade.php or foo/foo.livewire.php
            $candidate = $basePath . '/' . $path . '/' . basename($path) . $extension;
            if (file_exists($candidate)) {
                // For .blade.php files, only treat as Livewire component if it contains ⚡
                if ($extension === '.blade.php' && !str_contains($candidate, '⚡')) {
                    continue; // Skip non-⚡ blade files
                }
                return $candidate;
            }
            
            // For .livewire.php files in subdirectories, also check for versions with ⚡
            if ($extension === '.livewire.php') {
                $subDir = $basePath . '/' . $path;
                $componentBaseName = basename($path);
                
                if (is_dir($subDir)) {
                    // Check for files with ⚡ that match the component name
                    $pattern = $subDir . '/*⚡*' . $extension;
                    $files = glob($pattern);
                    
                    foreach ($files as $file) {
                        $filename = basename($file, $extension);
                        // Check if this file matches our component (ignoring ⚡)
                        if (str_replace('⚡', '', $filename) === $componentBaseName) {
                            return $file;
                        }
                    }
                }
            }

            // Convention 4: foo/index.blade.php or foo/index.livewire.php
            $candidate = $basePath . '/' . $path . '/index' . $extension;
            if (file_exists($candidate)) {
                // For .blade.php files, only treat as Livewire component if it contains ⚡
                if ($extension === '.blade.php' && !str_contains($candidate, '⚡')) {
                    continue; // Skip non-⚡ blade files
                }
                return $candidate;
            }
            
            // For .livewire.php files, also check for index files with ⚡
            if ($extension === '.livewire.php') {
                $subDir = $basePath . '/' . $path;
                
                if (is_dir($subDir)) {
                    // Check for index files with ⚡
                    $pattern = $subDir . '/*index*⚡*' . $extension;
                    $files = glob($pattern);
                    
                    foreach ($files as $file) {
                        $filename = basename($file, $extension);
                        // Check if this is an index file (ignoring ⚡)
                        if (str_replace('⚡', '', $filename) === 'index') {
                            return $file;
                        }
                    }
                    
                    // Also check ⚡index patterns
                    $pattern = $subDir . '/⚡*index*' . $extension;
                    $files = glob($pattern);
                    
                    foreach ($files as $file) {
                        $filename = basename($file, $extension);
                        // Check if this is an index file (ignoring ⚡)
                        if (str_replace('⚡', '', $filename) === 'index') {
                            return $file;
                        }
                    }
                }
            }
        }

        throw new ViewNotFoundException("No view file found for component: [{$name}] in [{$basePath}]");
    }
}