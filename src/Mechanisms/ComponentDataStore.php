<?php

namespace Livewire\Mechanisms;

use WeakMap;

class ComponentDataStore
{
    protected static $dataLookup;

    public function __invoke()
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

    static function get($component, $key, $default = null)
    {
        if (! isset(static::$dataLookup[$component])) {
            return $default;
        }

        if (! isset(static::$dataLookup[$component][$key])) {
            return $default;
        }

        return static::$dataLookup[$component][$key];
    }

    static function push($component, $key, $value, $iKey = null)
    {
        if (! isset(static::$dataLookup[$component])) {
            static::$dataLookup[$component] = [];
        }

        if (! isset(static::$dataLookup[$component][$key])) {
            static::$dataLookup[$component][$key] = [];
        }

        // if ($iKey) {

        // }

        // static::$dataLookup[$component][$key]] = $value;
    }
}
