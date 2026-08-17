<?php

namespace Livewire\Features\SupportReactiveProps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
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

    static function hashValue(mixed $value): ?string
    {
        if ($value instanceof Model) {
            $class = $value::class;
            $morphMap = Relation::morphMap();
            $alias = in_array($class, $morphMap)
                ? array_search($class, $morphMap, true)
                : $class;

            $payload = json_encode([
                'class' => $alias,
                'key' => $value->getKey(),
                'attributes' => $value->getAttributes(),
            ]);

            return $payload === false ? null : (string) crc32($payload);
        }

        $json = json_encode($value);

        return $json === false ? null : (string) crc32($json);
    }

    static function hasPassedInProps($id) {
        return isset(static::$pendingChildParams[$id]);
    }

    static function getPassedInProp($id, $name) {
        $params = static::$pendingChildParams[$id] ?? [];

        return $params[$name] ?? null;
    }
}
