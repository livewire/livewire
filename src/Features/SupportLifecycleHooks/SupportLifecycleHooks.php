<?php

namespace Livewire\Features\SupportLifecycleHooks;

use function Livewire\wrap;
use function Livewire\on;
use Livewire\ComponentHook;

class SupportLifecycleHooks extends ComponentHook
{
    // Performance optimization: Cache trait lookups per component class...
    protected static $traitCache = [];

    // Performance optimization: Cache method existence checks per component class...
    protected static $methodCache = [];

    // Performance optimization: Cache existing trait hook method names per component class and hook...
    protected static $traitHooksCache = [];

    // Performance optimization: Cache protected methods per component class...
    protected static $protectedMethodsCache = [];

    // Performance optimization: Cache studly-cased property names...
    protected static $studlyCache = [];

    public static function provide()
    {
        on('flush-state', function () {
            static::$traitCache = [];
            static::$methodCache = [];
            static::$traitHooksCache = [];
            static::$protectedMethodsCache = [];
            static::$studlyCache = [];
        });
    }

    public function mount($params)
    {
        if ($this->storeHas('skipMount')) { return; }

        $this->callHook('boot');
        $this->callTraitHook('boot');

        $this->callTraitHook('initialize');

        $this->callHook('mount', $params);
        $this->callTraitHook('mount', $params);

        $this->callHook('booted');
        $this->callTraitHook('booted');
    }

    public function hydrate()
    {
        if ($this->storeHas('skipHydrate')) { return; }

        $this->callHook('boot');
        $this->callTraitHook('boot');

        $this->callTraitHook('initialize');

        $this->callHook('hydrate');
        $this->callTraitHook('hydrate');

        // Call "hydrateXx" hooks for each property...
        foreach ($this->getProperties() as $property => $value) {
            $studly = static::$studlyCache[$property] ??= \Illuminate\Support\Str::studly($property);
            $this->callHook('hydrate'.$studly, [$value]);
        }

        $this->callHook('booted');
        $this->callTraitHook('booted');
    }

    public function update($propertyName, $fullPath, $newValue)
    {
        $containsDot = str_contains($fullPath, '.');

        if ($containsDot) {
            $dotPos = strpos($fullPath, '.');
            $rawProperty = substr($fullPath, 0, $dotPos);
            $propertyName = static::$studlyCache[$rawProperty] ??= \Illuminate\Support\Str::studly($rawProperty);
            $keyAfterFirstDot = substr($fullPath, $dotPos + 1);
            $lastDotPos = strrpos($fullPath, '.');
            $keyAfterLastDot = substr($fullPath, $lastDotPos + 1);
            $studlyFullPath = \Illuminate\Support\Str::studly(str_replace('.', '_', $fullPath));
            $beforeNestedMethod = 'updating'.$studlyFullPath;
            $afterNestedMethod = 'updated'.$studlyFullPath;
        } else {
            $propertyName = static::$studlyCache[$fullPath] ??= \Illuminate\Support\Str::studly($fullPath);
            $keyAfterFirstDot = null;
            $keyAfterLastDot = null;
            $beforeNestedMethod = false;
            $afterNestedMethod = false;
        }

        $beforeMethod = 'updating'.$propertyName;
        $afterMethod = 'updated'.$propertyName;

        $this->callHook('updating', [$fullPath, $newValue]);
        $this->callTraitHook('updating', [$fullPath, $newValue]);

        $this->callHook($beforeMethod, [$newValue, $keyAfterFirstDot]);

        $this->callHook($beforeNestedMethod, [$newValue, $keyAfterLastDot]);

        return function () use ($fullPath, $afterMethod, $afterNestedMethod, $keyAfterFirstDot, $keyAfterLastDot, $newValue) {
            $this->callHook('updated', [$fullPath, $newValue]);
            $this->callTraitHook('updated', [$fullPath, $newValue]);

            $this->callHook($afterMethod, [$newValue, $keyAfterFirstDot]);

            $this->callHook($afterNestedMethod, [$newValue, $keyAfterLastDot]);
        };
    }

    public function call($methodName, $params, $returnEarly, $metadata)
    {
        $class = get_class($this->component);

        $protectedMethods = static::$protectedMethodsCache[$class] ??= (function () use ($class) {
            $protected = [
                'mount',
                'boot',
                'booted',
                'exception',
                'hydrate*',
                'dehydrate*',
                'updating*',
                'updated*',
                'rendering',
                'rendered',
                'scriptSrc',
            ];

            if (! isset(static::$traitCache[$class])) {
                static::$traitCache[$class] = class_uses_recursive($this->component);
            }

            foreach (static::$traitCache[$class] as $trait) {
                $traitBasename = class_basename($trait);
                $protected[] = 'mount'.$traitBasename;
                $protected[] = 'boot'.$traitBasename;
                $protected[] = 'booted'.$traitBasename;
            }

            return $protected;
        })();

        throw_if(
            str($methodName)->is($protectedMethods),
            new DirectlyCallingLifecycleHooksNotAllowedException($methodName, $this->component->getName())
        );

        $this->callTraitHook('call', ['methodName' => $methodName, 'params' => $params, 'returnEarly' => $returnEarly, 'metadata' => $metadata]);
    }

    public function exception($e, $stopPropagation)
    {
        $this->callHook('exception', ['e' => $e, 'stopPropagation' => $stopPropagation]);
        $this->callTraitHook('exception', ['e' => $e, 'stopPropagation' => $stopPropagation]);
    }

    public function render($view, $data)
    {
        $this->callHook('rendering', ['view' => $view, 'data' => $data]);
        $this->callTraitHook('rendering', ['view' => $view, 'data' => $data]);

        return function ($html) use ($view) {
            $this->callHook('rendered', ['view' => $view, 'html' => $html]);
            $this->callTraitHook('rendered', ['view' => $view, 'html' => $html]);
        };
    }

    public function dehydrate()
    {
        $this->callHook('dehydrate');
        $this->callTraitHook('dehydrate');

        // Call "dehydrateXx" hooks for each property...
        foreach ($this->getProperties() as $property => $value) {
            $studly = static::$studlyCache[$property] ??= \Illuminate\Support\Str::studly($property);
            $this->callHook('dehydrate'.$studly, [$value]);
        }
    }

    public function callHook($name, $params = [])
    {
        // Performance optimization: Cache method existence checks
        $class = get_class($this->component);
        $cacheKey = "{$class}::{$name}";

        if (!isset(static::$methodCache[$cacheKey])) {
            static::$methodCache[$cacheKey] = method_exists($this->component, $name);
        }

        if (static::$methodCache[$cacheKey]) {
            wrap($this->component)->__call($name, $params);
        }
    }

    function callTraitHook($name, $params = [])
    {
        $class = get_class($this->component);

        if (! isset(static::$traitHooksCache[$class][$name])) {
            if (! isset(static::$traitCache[$class])) {
                static::$traitCache[$class] = class_uses_recursive($this->component);
            }

            $methods = [];
            foreach (static::$traitCache[$class] as $trait) {
                $method = $name.class_basename($trait);
                if (method_exists($this->component, $method)) {
                    $methods[] = $method;
                }
            }
            static::$traitHooksCache[$class][$name] = $methods;
        }

        $methods = static::$traitHooksCache[$class][$name];
        if (empty($methods)) return;

        $paramsToSpread = $params;
        if (! empty($params)) {
            $keys = array_keys($params);
            $hasStringKeys = array_filter($keys, 'is_string');
            $hasIntKeys = array_filter($keys, 'is_int');

            if ($hasStringKeys && $hasIntKeys) {
                $paramsToSpread = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);
            }
        }

        foreach ($methods as $method) {
            wrap($this->component)->$method(...$paramsToSpread);
        }
    }
}
