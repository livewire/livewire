<?php

namespace Livewire;

abstract class ComponentHook
{
    protected $component;

    public function setComponent($component)
    {
        $this->component = $component;
    }

    public function callBoot(...$params)
    {
        if (method_exists($this, 'boot')) {
            $this->boot(...$params);
        }
    }

    public function callMount(...$params)
    {
        if (method_exists($this, 'mount')) {
            $this->mount(...$params);
        }
    }

    public function callHydrate(...$params)
    {
        if (method_exists($this, 'hydrate')) {
            $this->hydrate(...$params);
        }
    }

    public function callUpdate($propertyName, $fullPath, $newValue)
    {
        $callbacks = [];

        if (method_exists($this, 'update')) {
            $callbacks[] = $this->update($propertyName, $fullPath, $newValue);
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $callback(...$params);
                }
            }
        };
    }

    public function callCall($method, $params, $returnEarly)
    {
        $callbacks = [];

        if (method_exists($this, 'call')) {
            $callbacks[] = $this->call($method, $params, $returnEarly);
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $callback(...$params);
                }
            }
        };
    }

    public function callRender(...$params)
    {
        $callbacks = [];

        if (method_exists($this, 'render')) {
            $callbacks[] = $this->render(...$params);
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) {
                    $callback(...$params);
                }
            }
        };
    }

    public function callDehydrate(...$params)
    {
        if (method_exists($this, 'dehydrate')) {
            $this->dehydrate(...$params);
        }
    }

    public function callDestroy(...$params)
    {
        if (method_exists($this, 'destroy')) {
            $this->destroy(...$params);
        }
    }

    public function callException(...$params)
    {
        if (method_exists($this, 'exception')) {
            $this->exception(...$params);
        }
    }

    public function getProperties()
    {
        return $this->component->all();
    }

    public function getProperty($name)
    {
        return data_get($this->getProperties(), $name);
    }

    public function storeSet($key, $value)
    {
        store($this->component)->set($key, $value);
    }

    public function storePush($key, $value, $iKey = null)
    {
        store($this->component)->push($key, $value, $iKey);
    }

    public function storeGet($key, $default = null)
    {
        return store($this->component)->get($key, $default);
    }

    public function storeHas($key)
    {
        return store($this->component)->has($key);
    }
}
