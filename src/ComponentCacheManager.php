<?php

namespace Livewire;

use Illuminate\Support\Arr;

class ComponentCacheManager
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
        return Arr::get($this->getFullComponentCache(), $key, $default);
    }

    public function put($key, $value)
    {
        $componentCache = $this->getFullComponentCache();

        Arr::set($componentCache, $key, $value);

        return cache()->put("{$this->component->id}", $componentCache);
    }

    protected function getFullComponentCache() {
        return cache()->get("{$this->component->id}", []);
    }

    public static function garbageCollect(array $componentIds)
    {
        foreach ($componentIds as $componentId) {
            cache()->forget($componentId);
        }

        return $componentIds;
    }
}
