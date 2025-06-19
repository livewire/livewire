<?php

namespace Livewire\V4\MagicActions;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportMagicActions extends ComponentHook
{
    public static $magicActions = [
        '$refresh',
        '$set',
        '$sync',
    ];

    public function boot()
    {
        on('call', function ($root, $method, $params, $context, $returnEarly) {
            if (! in_array($method, self::$magicActions)) {
                return;
            }

            $returnEarly();
        });
    }
}