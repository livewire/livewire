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

    protected function wrapCallable($callback)
    {
        if (is_callable($callback)) {
            return $callback;
        }

        if (is_array($callback)) {
            $callables = array_filter($callback, 'is_callable');
            if (empty($callables)) return null;

            return function (...$params) use ($callables) {
                foreach ($callables as $cb) $cb(...$params);
            };
        }

        return null;
    }

    function callUpdate($propertyName, $fullPath, $newValue) {
        if (! method_exists($this, 'update')) return null;

        return $this->wrapCallable($this->update($propertyName, $fullPath, $newValue));
    }

    function callCall($method, $params, $returnEarly, $metadata, $componentContext) {
        if (! method_exists($this, 'call')) return null;

        return $this->wrapCallable($this->call($method, $params, $returnEarly, $metadata, $componentContext));
    }

    function callRender(...$params) {
        if (! method_exists($this, 'render')) return null;

        return $this->wrapCallable($this->render(...$params));
    }

    function callRenderIsland(...$params) {
        if (! method_exists($this, 'renderIsland')) return null;

        return $this->wrapCallable($this->renderIsland(...$params));
    }

    function callRenderPlaceholder(...$params) {
        if (! method_exists($this, 'renderPlaceholder')) return null;

        return $this->wrapCallable($this->renderPlaceholder(...$params));
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

    function storeFind($key, $iKey = null, $default = null)
    {
        return store($this->component)->find($key, $iKey, $default);
    }

    function storeHas($key)
    {
        return store($this->component)->has($key);
    }
}
