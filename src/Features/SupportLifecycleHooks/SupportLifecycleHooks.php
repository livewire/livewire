<?php

namespace Livewire\Features\SupportLifecycleHooks;

use function Livewire\store;
use function Livewire\wrap;
use Livewire\ComponentHook;

class SupportLifecycleHooks extends ComponentHook
{
    public function mount($params)
    {
        if (store($this->component)->has('skipMount')) { return; }

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
        if (store($this->component)->has('skipHydrate')) { return; }

        $this->callHook('boot');
        $this->callTraitHook('boot');

        $this->callTraitHook('initialize');

        $this->callHook('hydrate');
        $this->callTraitHook('hydrate');

        // Call "hydrateXx" hooks for each property...
        foreach ($this->getProperties() as $property => $value) {
            $this->callHook('hydrate'.str($property)->studly(), [$value]);
        }

        $this->callHook('booted');
        $this->callTraitHook('booted');
    }

    public function update($propertyName, $fullPath, $newValue)
    {
        $name = str($fullPath);

        $propertyName = $name->studly()->before('.');
        $keyAfterFirstDot = $name->contains('.') ? $name->after('.')->__toString() : null;
        $keyAfterLastDot = $name->contains('.') ? $name->afterLast('.')->__toString() : null;

        $beforeMethod = 'updating'.$propertyName;
        $afterMethod = 'updated'.$propertyName;

        $beforeNestedMethod = $name->contains('.')
            ? 'updating'.$name->replace('.', '_')->studly()
            : false;

        $afterNestedMethod = $name->contains('.')
            ? 'updated'.$name->replace('.', '_')->studly()
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

    public function call($methodName)
    {
        $protectedMethods = [
            'mount',
            'hydrate*',
            'dehydrate*',
            'updating*',
            'updated*',
        ];

        throw_if(
            str($methodName)->is($protectedMethods),
            new DirectlyCallingLifecycleHooksNotAllowedException($methodName, $this->component->getName())
        );
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
            $this->callHook('dehydrate'.str($property)->studly(), [$value]);
        }
    }

    public function callHook($name, $params = [])
    {
        if (method_exists($this->component, $name)) {
            wrap($this->component)->__call($name, $params);
        }
    }

    function callTraitHook($name, $params = [])
    {
        foreach (class_uses_recursive($this->component) as $trait) {
            $method = $name.class_basename($trait);

            if (method_exists($this->component, $method)) {
                wrap($this->component)->$method(...$params);
            }
        }
    }
}
