<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\Drawer\Utils;
use Livewire\Drawer\Utils as SyntheticUtils;

use function Livewire\on;

class SupportLockedProperties
{
    public function boot()
    {
        on('diff', function ($root, $path, $value) {
            $prop = Utils::beforeFirstDot($path);

            if (SyntheticUtils::propertyHasAnnotation($root, $prop, 'locked')) {
                throw new \Exception('Cannot update locked property: ['.$prop.']');
            }
        });
    }
}
