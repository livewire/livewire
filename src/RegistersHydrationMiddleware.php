<?php

namespace Livewire;

trait RegistersHydrationMiddleware
{
    protected $hydrationMiddleware = [];
    protected $initialHydrationMiddleware = [];
    protected $initialDehydrationMiddleware = [];
    protected $propertyHydrationMiddleware = [];
    protected $propertyDehydrationMiddleware = [];

    public function registerHydrationMiddleware(array $classes)
    {
        $this->hydrationMiddleware += $classes;
    }

    public function registerInitialHydrationMiddleware(array $callables)
    {
        $this->initialHydrationMiddleware += $callables;
    }

    public function registerInitialDehydrationMiddleware(array $callables)
    {
        $this->initialDehydrationMiddleware += $callables;
    }

    public function hydrate($instance, $request)
    {
        foreach ($this->hydrationMiddleware as $class) {
            $class::hydrate($instance, $request);
        }

        Livewire::dispatch('component.hydrate', $instance, $request);
        Livewire::dispatch('component.hydrate.subsequent', $instance, $request);
    }

    public function initialHydrate($instance, $request)
    {
        foreach ($this->initialHydrationMiddleware as $callable) {
            $callable($instance, $request);
        }

        Livewire::dispatch('component.hydrate', $instance, $request);
        Livewire::dispatch('component.hydrate.initial', $instance, $request);
    }

    public function initialDehydrate($instance, $response)
    {
        Livewire::dispatch('component.dehydrate', $instance, $response);
        Livewire::dispatch('component.dehydrate.initial', $instance, $response);

        foreach (array_reverse($this->initialDehydrationMiddleware) as $callable) {
            $callable($instance, $response);
        }
    }

    public function dehydrate($instance, $response)
    {

        Livewire::dispatch('component.dehydrate', $instance, $response);
        Livewire::dispatch('component.dehydrate.subsequent', $instance, $response);

        // The array is being reversed here, so the middleware dehydrate phase order of execution is
        // the inverse of hydrate. This makes the middlewares behave like layers in a shell.
        foreach (array_reverse($this->hydrationMiddleware) as $class) {
            $class::dehydrate($instance, $response);
        }
    }

    public function hydrateProperty($callback)
    {
        $this->propertyHydrationMiddleware[] = $callback;

        return $this;
    }

    public function dehydrateProperty($callback)
    {
        $this->propertyDehydrationMiddleware[] = $callback;

        return $this;
    }

    public function performHydrateProperty($value, $property, $instance)
    {
        $valueMemo = $value;
        foreach ($this->propertyHydrationMiddleware as $callable) {
            $valueMemo  = $callable($valueMemo, $property, $instance);
        }
        return $valueMemo;
    }

    public function performDehydrateProperty($value, $property, $instance)
    {
        $valueMemo = $value;
        foreach ($this->propertyDehydrationMiddleware as $callable) {
            $valueMemo  = $callable($valueMemo, $property, $instance);
        }
        return $valueMemo;
    }
}
