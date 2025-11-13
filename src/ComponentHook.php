<?php

namespace Livewire;

abstract class ComponentHook
{
    protected $component;

    function setComponent($component)
    {
        $this->component = $component;
    }

    function callBoot(...$params) {
        if (method_exists($this, 'boot')) $this->boot(...$params);
    }

    function callMount(...$params) {
        if (method_exists($this, 'mount')) $this->mount(...$params);
    }

    function callHydrate(...$params) {
        if (method_exists($this, 'hydrate')) $this->hydrate(...$params);
    }

    function callUpdate($propertyName, $fullPath, $newValue) {
        $callbacks = [];

        if (method_exists($this, 'update')) $callbacks[] = $this->update($propertyName, $fullPath, $newValue);

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callCall($method, $params, $returnEarly, $metadata, $componentContext) {
        $callbacks = [];

        if (method_exists($this, 'call')) $callbacks[] = $this->call($method, $params, $returnEarly, $metadata, $componentContext);

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callRender(...$params) {
        $callbacks = [];

        if (method_exists($this, 'render')) $callbacks[] = $this->render(...$params);

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callRenderIsland(...$params) {
        $callbacks = [];

        if (method_exists($this, 'renderIsland')) $callbacks[] = $this->renderIsland(...$params);

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callDehydrate(...$params) {
        if (method_exists($this, 'dehydrate')) $this->dehydrate(...$params);
    }

    function callDestroy(...$params) {
        if (method_exists($this, 'destroy')) $this->destroy(...$params);
    }

    function callException(...$params) {
        if (method_exists($this, 'exception')) $this->exception(...$params);
    }

    function getProperties()
    {
        return $this->component->all();
    }

    function getProperty($name)
    {
        return data_get($this->getProperties(), $name);
    }

    function storeSet($key, $value)
    {
        store($this->component)->set($key, $value);
    }

    function storePush($key, $value, $iKey = null)
    {
        store($this->component)->push($key, $value, $iKey);
    }

    function storeGet($key, $default = null)
    {
        return store($this->component)->get($key, $default);
    }

    function storeHas($key)
    {
        return store($this->component)->has($key);
    }
}
