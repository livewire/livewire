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

        $this->supportedExtensions = $supportedExtensions ?: [
            '.blade.php',
            '.wire.php',
        ];
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
            // Convention 1: foo.blade.php or foo.wire.php
            $candidate = $basePath . '/' . $path . $extension;
            if (file_exists($candidate)) {
                return $candidate;
            }

            // Convention 2: foo/foo.blade.php or foo/foo.wire.php
            $candidate = $basePath . '/' . $path . '/' . basename($path) . $extension;
            if (file_exists($candidate)) {
                return $candidate;
            }

            // Convention 3: foo/index.blade.php or foo/index.wire.php
            $candidate = $basePath . '/' . $path . '/index' . $extension;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        throw new ViewNotFoundException("No view file found for component: [{$name}] in [{$basePath}]");
    }
}