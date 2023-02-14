<?php

namespace Livewire\Features\SupportQueryString;

use function Livewire\on;
use function Livewire\before;
use function Livewire\invade;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Illuminate\Support\Arr;
use Livewire\ComponentHook;

class SupportQueryString extends ComponentHook
{
    /**
     * Note: this is support for the legacy syntax...
     */
    function mount()
    {
        if (! $queryString = $this->getQueryString()) return;

        foreach ($queryString as $key => $value) {
            $key = is_string($key) ? $key : $value;
            $alias = $value['as'] ?? $key;
            $use = $value['use'] ?? 'push';
            $alwaysShow = $value['alwaysShow'] ?? ($value['except'] ? false : true);

            $this->setPropertyHook($key, new Url(as: $alias, use: $use, alwaysShow: $alwaysShow));
        }
    }

    public function getQueryString()
    {
        $component = $this->component;

        $componentQueryString = [];

        if (method_exists($component, 'queryString')) $componentQueryString = invade($component)->queryString();
        elseif (property_exists($component, 'queryString')) $componentQueryString = invade($component)->queryString;

        return collect(class_uses_recursive($class = static::class))
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
