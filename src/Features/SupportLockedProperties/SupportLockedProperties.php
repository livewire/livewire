<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\Drawer\Utils;

use function Livewire\on;

class SupportLockedProperties
{
    public function boot()
    {
        on('update', function ($root, $path, $value) {
            $prop = Utils::beforeFirstDot($path);

            if (Utils::hasAttribute($root, $prop, Locked::class)) {
               throw new \Exception('Cannot update locked property: ['.$prop.']');
            }
        });
    }
}
