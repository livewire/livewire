<?php

namespace Livewire\Features\SupportLifecycleHooks;

use function Livewire\wrap;
use function Livewire\on;
use Livewire\ComponentHook;
use Illuminate\Support\Str;

class SupportLifecycleHooks extends ComponentHook
{
    // Performance optimization: Cache trait lookups per component class...
    protected static $traitCache = [];

    // Performance optimization: Cache method existence checks per component class...
    protected static $methodCache = [];

    // Performance optimization: Cache trait hook methods (e.g. "mountWithFileUploads") per component class and hook...
    protected static $traitHookCache = [];

    // Performance optimization: Cache protected lifecycle method names per component class...
    protected static $protectedMethodCache = [];

    public static function provide()
    {
        on('flush-state', function () {
            static::$traitCache = [];
            static::$methodCache = [];
            static::$traitHookCache = [];
            static::$protectedMethodCache = [];
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
            $this->callHook('hydrate'.Str::studly($property), [$value]);
        }

        $this->callHook('booted');
        $this->callTraitHook('booted');
    }

    public function update($propertyName, $fullPath, $newValue)
    {
        $isNested = str_contains($fullPath, '.');

        $propertyName = Str::studly(Str::before($fullPath, '.'));
        $keyAfterFirstDot = $isNested ? Str::after($fullPath, '.') : null;
        $keyAfterLastDot = $isNested ? Str::afterLast($fullPath, '.') : null;

        $beforeMethod = 'updating'.$propertyName;
        $afterMethod = 'updated'.$propertyName;

        $beforeNestedMethod = $isNested
            ? 'updating'.Str::studly(str_replace('.', '_', $fullPath))
            : false;

        $afterNestedMethod = $isNested
            ? 'updated'.Str::studly(str_replace('.', '_', $fullPath))
            : false;

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
        throw_if(
            Str::is($this->protectedMethods(), $methodName),
            new DirectlyCallingLifecycleHooksNotAllowedException($methodName, $this->component->getName())
        );

        $this->callTraitHook('call', ['methodName' => $methodName, 'params' => $params, 'returnEarly' => $returnEarly, 'metadata' => $metadata]);
    }

    protected function protectedMethods()
    {
        $class = get_class($this->component);

        if (isset(static::$protectedMethodCache[$class])) {
            return static::$protectedMethodCache[$class];
        }

        $protectedMethods = [
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

        // Also block trait-suffixed lifecycle hooks (e.g. mountWithFileUploads, bootMyTrait)
        foreach ($this->traits() as $trait) {
            $traitBasename = class_basename($trait);
            $protectedMethods[] = 'mount'.$traitBasename;
            $protectedMethods[] = 'boot'.$traitBasename;
            $protectedMethods[] = 'booted'.$traitBasename;
        }

        return static::$protectedMethodCache[$class] = $protectedMethods;
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
            $this->callHook('dehydrate'.Str::studly($property), [$value]);
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
        $methods = $this->traitHookMethods($name);

        if (! $methods) return;

        // resolveMethodDependencies() can produce arrays with both
        // string and integer keys (e.g. ['postId' => '123', 0 => null]).
        // PHP forbids positional args after named args when spreading,
        // so strip the integer-keyed entries in that case. When all keys
        // are the same type (e.g. updating/updated hooks pass only
        // integer-keyed [$name, $value]), leave them as-is.
        $keys = array_keys($params);
        $hasStringKeys = array_filter($keys, 'is_string');
        $hasIntKeys = array_filter($keys, 'is_int');

        $paramsToSpread = ($hasStringKeys && $hasIntKeys)
            ? array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY)
            : $params;

        foreach ($methods as $method) {
            wrap($this->component)->$method(...$paramsToSpread);
        }
    }

    protected function traitHookMethods($name)
    {
        $class = get_class($this->component);

        if (isset(static::$traitHookCache[$class][$name])) {
            return static::$traitHookCache[$class][$name];
        }

        $methods = [];

        foreach ($this->traits() as $trait) {
            $method = $name.class_basename($trait);

            if (method_exists($this->component, $method)) {
                $methods[] = $method;
            }
        }

        return static::$traitHookCache[$class][$name] = $methods;
    }

    protected function traits()
    {
        return static::$traitCache[get_class($this->component)] ??= class_uses_recursive($this->component);
    }
}
