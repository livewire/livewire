<?php

namespace Livewire\Features\SupportLazyLoading;

use function Livewire\on;

class SupportLazyLoading
{
    public function boot()
    {
        on('mount', function ($name, $params, $parent, $key, $hijack) {
            if ($name === 'lazy') return;
            if (! array_key_exists('lazy', $params)) return;
            unset($params['lazy']);

            [$html] = app('livewire')->mount('lazy', ['componentName' => $name, 'forwards' => $params], $key);

            $hijack($html);
        });

        app('livewire')->component('lazy', Lazy::class);
    }
}
