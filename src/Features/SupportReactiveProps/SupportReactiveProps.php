<?php

namespace Livewire\Features\SupportReactiveProps;

use function Livewire\on;
use Livewire\ComponentHook;

class SupportReactiveProps extends ComponentHook
{
    public static $pendingChildParams = [];

    static function provide()
    {
        on('flush-state', fn() => static::$pendingChildParams = []);

        on('mount.stub', function ($tag, $id, $params, $parent, $key) {
            static::$pendingChildParams[$id] = $params;
        });
    }

    static function hasPassedInProps($id) {
        return isset(static::$pendingChildParams[$id]);
    }

    static function getPassedInProp($id, $name) {
        $params = static::$pendingChildParams[$id] ?? [];

        return $params[$name] ?? null;
    }
}
