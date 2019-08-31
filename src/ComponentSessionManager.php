<?php

namespace Livewire;

class ComponentSessionManager
{
    protected $component;

    public function __construct($component)
    {
        $this->component = $component;
    }

    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        return ! collect($keys)->contains(function ($key) {
            return is_null($this->get($key));
        });
    }

    public function get($key, $default = null)
    {
        return session()->get("{$this->component->id}.{$key}", $default);
    }

    public function put($key, $value)
    {
        return session()->put("{$this->component->id}.{$key}", $value);
    }

    public static function garbageCollect($componentId)
    {
        session()->forget((array) $componentId);
    }
}
