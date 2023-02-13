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
        if (! $queryString = invade($this)->component->getQueryString()) return;

        foreach ($queryString as $key => $value) {
            $key = is_string($key) ? $key : $value;
            $alias = $value['as'] ?? $key;
            $use = $value['use'] ?? 'push';
            $alwaysShow = $value['alwaysShow'] ?? ($value['except'] ? false : true);

            $this->setPropertyHook($key, new Url(as: $alias, use: $use, alwaysShow: $alwaysShow));
        }
    }
}
