<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportReactiveProps extends ComponentHook
{
    public static $pendingChildParams = [];

    public static function provide()
    {
        on('flush-state', fn () => static::$pendingChildParams = []);

        on('mount.stub', function ($tag, $id, $params, $parent, $key) {
            static::$pendingChildParams[$id] = $params;
        });
    }

    public static function hasPassedInProps($id)
    {
        return isset(static::$pendingChildParams[$id]);
    }

    public static function getPassedInProp($id, $name)
    {
        $params = static::$pendingChildParams[$id] ?? [];

        return $params[$name] ?? null;
    }
}
