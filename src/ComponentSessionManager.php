<?php

namespace Livewire;

class ComponentSessionManager
{
    protected $component;

    public function __construct($component)
    {
        $this->component = $component;
    }

    public function get($key)
    {
        return session()->get("{$this->component->id}:{$key}");
    }

    public function put($key, $value)
    {
        return session()->put("{$this->component->id}:{$key}", $value);
    }
}
