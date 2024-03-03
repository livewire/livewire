<?php

namespace Livewire\Mechanisms;

use WeakMap;

class DataStore extends Mechanism
{
    protected $lookup;

    function __construct()
    {
        $this->lookup = new WeakMap;
    }

    function set($instance, $key, $value)
    {
        if (! isset($this->lookup[$instance])) {
            $this->lookup[$instance] = [];
        }

        $this->lookup[$instance][$key] = $value;
    }

    function has($instance, $key, $iKey = null) {
        if (! isset($this->lookup[$instance])) {
            return false;
        }

        if (! isset($this->lookup[$instance][$key])) {
            return false;
        }

        if ($iKey !== null) {
            return !! ($this->lookup[$instance][$key][$iKey] ?? false);
        }

        return true;
    }

    function get($instance, $key, $default = null)
    {
        if (! isset($this->lookup[$instance])) {
            return value($default);
        }

        if (! isset($this->lookup[$instance][$key])) {
            return value($default);
        }

        return $this->lookup[$instance][$key];
    }

    function find($instance, $key, $iKey = null, $default = null)
    {
        if (! isset($this->lookup[$instance])) {
            return value($default);
        }

        if (! isset($this->lookup[$instance][$key])) {
            return value($default);
        }

        if ($iKey !== null && ! isset($this->lookup[$instance][$key][$iKey])) {
            return value($default);
        }

        return $iKey !== null
            ? $this->lookup[$instance][$key][$iKey]
            : $this->lookup[$instance][$key];
    }

    function push($instance, $key, $value, $iKey = null)
    {
        if (! isset($this->lookup[$instance])) {
            $this->lookup[$instance] = [];
        }

        if (! isset($this->lookup[$instance][$key])) {
            $this->lookup[$instance][$key] = [];
        }

        if ($iKey) {
            $this->lookup[$instance][$key][$iKey] = $value;
        } else {
            $this->lookup[$instance][$key][] = $value;
        }
    }

    function unset($instance, $key, $iKey = null)
    {
        if (! isset($this->lookup[$instance])) {
            return;
        }

        if ($iKey !== null) {
            // Set a local variable to avoid the "indirect modification" error.
            $keyValue = $this->lookup[$instance][$key];

            unset($keyValue[$iKey]);

            $this->lookup[$instance][$key] = $keyValue;
        } else {
            // Set a local variable to avoid the "indirect modification" error.
            $instanceValue = $this->lookup[$instance];

            unset($instanceValue[$key]);

            $this->lookup[$instance] = $instanceValue;
        }
    }
}
