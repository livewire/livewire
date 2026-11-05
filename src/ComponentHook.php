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
        if (! method_exists($this, 'update')) return;

        $callback = $this->update($propertyName, $fullPath, $newValue);

        if (is_callable($callback)) return $callback;
    }

    function callCall($method, $params, $returnEarly, $metadata, $componentContext) {
        if (! method_exists($this, 'call')) return;

        $callback = $this->call($method, $params, $returnEarly, $metadata, $componentContext);

        if (is_callable($callback)) return $callback;
    }

    function callRender(...$params) {
        if (! method_exists($this, 'render')) return;

        $callback = $this->render(...$params);

        if (is_callable($callback)) return $callback;
    }

    function callRenderIsland(...$params) {
        if (! method_exists($this, 'renderIsland')) return;

        $callback = $this->renderIsland(...$params);

        if (is_callable($callback)) return $callback;
    }

    function callRenderPlaceholder(...$params) {
        if (! method_exists($this, 'renderPlaceholder')) return;

        $callback = $this->renderPlaceholder(...$params);

        if (is_callable($callback)) return $callback;
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
