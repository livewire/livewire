<?php

namespace Livewire\Features\SupportMagicActions;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportMagicActions extends ComponentHook
{
    public static $magicActions = [
        '$refresh',
        '$set',
        '$sync',
        '$commit',
    ];

    public function boot()
    {
        on('call', function ($component, $method, $params, $componentContext, $returnEarly, $context) {
            if (! in_array($method, self::$magicActions)) {
                return;
            }

            $returnEarly();
        });
    }
}