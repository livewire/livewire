<?php

namespace Livewire\Mechanisms;

use WeakMap;

class DataStore
{
    public function register()
    {
        app()->singleton($this::class);
    }

    public function boot()
    {
        //
    }

    protected $lookup;

    public function __construct()
    {
        $this->lookup = new WeakMap;
    }

    public function set($instance, $key, $value)
    {
        if (! isset($this->lookup[$instance])) {
            $this->lookup[$instance] = [];
        }

        $this->lookup[$instance][$key] = $value;
    }

    public function has($instance, $key, $iKey = null)
    {
        if (! isset($this->lookup[$instance])) {
            return false;
        }

        if (! isset($this->lookup[$instance][$key])) {
            return false;
        }

        if ($iKey !== null) {
            return (bool) $this->lookup[$instance][$key][$iKey] ?? false;
        }

        return true;
    }

    public function get($instance, $key, $default = null)
    {
        if (! isset($this->lookup[$instance])) {
            return value($default);
        }

        if (! isset($this->lookup[$instance][$key])) {
            return value($default);
        }

        return $this->lookup[$instance][$key];
    }

    public function find($instance, $key, $iKey = null, $default = null)
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

    public function push($instance, $key, $value, $iKey = null)
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
}
