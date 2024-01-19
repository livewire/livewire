<?php

namespace Livewire\Features\SupportQueryString;

use function Livewire\invade;
use Livewire\ComponentHook;

class SupportQueryString extends ComponentHook
{
    public $queryString;

    /**
     * Note: this is support for the legacy syntax...
     */
    function mount()
    {
        if (! $queryString = $this->getQueryString()) return;

        foreach ($queryString as $key => $value) {
            $key = is_string($key) ? $key : $value;
            $alias = $value['as'] ?? $key;
            $history = $value['history'] ?? true;
            $keep = $value['alwaysShow'] ?? $value['keep'] ?? false;
            $except = $value['except'] ?? null;

            $this->component->setPropertyAttribute($key, new BaseUrl(as: $alias, history: $history, keep: $keep, except: $except));
        }
    }

    public function getQueryString()
    {
        if (isset($this->queryString)) return $this->queryString;

        $component = $this->component;

        $componentQueryString = [];

        if (method_exists($component, 'queryString')) $componentQueryString = invade($component)->queryString();
        elseif (property_exists($component, 'queryString')) $componentQueryString = invade($component)->queryString;

        return $this->queryString = collect(class_uses_recursive($class = $component::class))
            ->map(function ($trait) use ($class, $component) {
                $member = 'queryString' . class_basename($trait);

                if (method_exists($class, $member)) {
                    return invade($component)->{$member}();
                }

                if (property_exists($class, $member)) {
                    return invade($component)->{$member};
                }

                return [];
            })
            ->values()
            ->mapWithKeys(function ($value) {
                return $value;
            })
            ->merge($componentQueryString)
            ->toArray();
    }
}
