<?php

namespace Livewire\Mechanisms;

use Livewire\Drawer\Utils;
use WeakMap;

class ComponentDataStore
{
    protected static $dataLookup;

    public function boot()
    {
        static::$dataLookup = new WeakMap;
    }

    static function set($component, $key, $value)
    {
        if (! isset(static::$dataLookup[$component])) {
            static::$dataLookup[$component] = [];
        }

        static::$dataLookup[$component][$key] = $value;
    }

    static function has($component, $key, $iKey = null) {
        if (! isset(static::$dataLookup[$component])) {
            return false;
        }

        if (! isset(static::$dataLookup[$component][$key])) {
            return false;
        }

        if ($iKey !== null) {
            return !! static::$dataLookup[$component][$key][$iKey] ?? false;
        }

        return true;
    }

    static function get($component, $key, $default = null)
    {
        if (! isset(static::$dataLookup[$component])) {
            return value($default);
        }

        if (! isset(static::$dataLookup[$component][$key])) {
            return value($default);
        }

        return static::$dataLookup[$component][$key];
    }

    static function find($component, $key, $iKey = null, $default = null)
    {
        if (! isset(static::$dataLookup[$component])) {
            return value($default);
        }

        if (! isset(static::$dataLookup[$component][$key])) {
            return value($default);
        }

        if ($iKey !== null && ! isset(static::$dataLookup[$component][$key][$iKey])) {
            return value($default);
        }

        return $iKey !== null
            ? static::$dataLookup[$component][$key][$iKey]
            : static::$dataLookup[$component][$key];
    }

    static function push($component, $key, $value, $iKey = null)
    {
        if (! isset(static::$dataLookup[$component])) {
            static::$dataLookup[$component] = [];
        }

        if (! isset(static::$dataLookup[$component][$key])) {
            static::$dataLookup[$component][$key] = [];
        }

        if ($iKey) {
            static::$dataLookup[$component][$key][$iKey] = $value;
        } else {
            static::$dataLookup[$component][$key][] = $value;
        }
    }
}
