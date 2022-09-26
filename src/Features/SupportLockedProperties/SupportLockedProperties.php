<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\Drawer\Utils;
use Synthetic\Utils as SyntheticUtils;

class SupportLockedProperties
{
    public function boot()
    {
        app('synthetic')->on('diff', function ($root, $path, $value) {
            $prop = Utils::beforeFirstDot($path);

            if (SyntheticUtils::propertyHasAnnotation($root, $prop, 'locked')) {
                throw new \Exception('Cannot update locked property: ['.$prop.']');
            }
        });
    }
}
