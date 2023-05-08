<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils as SyntheticUtils;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\HandleComponents\Synthesizers\LivewireSynth;

use function Livewire\on;
use function Livewire\store;

class SupportReactiveProps extends ComponentHook
{
    public static $pendingChildParams = [];

    public static function provide()
    {
        on('flush-state', fn() => static::$pendingChildParams = []);

        on('mount.stub', function ($tag, $id, $params, $parent, $key) {
            static::storeChildParams($id, $params);
        });

        on('dehydrate', function ($target, $context) {
            $props = store($target)->get('reactiveProps', []);
            $propHashes = store($target)->get('reactivePropHashes', []);

            foreach ($propHashes as $key => $hash) {
                if (crc32(json_encode($target->{$key})) !== $hash) {
                    throw new \Exception('Cant mutate a prop: ['.$key.']');
                }
            }

            $props && $context->addMemo('props', $props);
        });

        on('hydrate', function ($target, $memo) {
            if (! isset($memo['props'])) return;

            $propKeys = $memo['props'];

            $props = static::getProps($memo['id'], $propKeys);

            $propHashes = [];

            foreach ($props as $key => $value) {
                $target->{$key} = $value;
            }

            foreach ($propKeys as $key) {
                $propHashes[$key] = crc32(json_encode($target->{$key}));
            }

            store($target)->set('reactiveProps', $propKeys);
            store($target)->set('reactivePropHashes', $propHashes);

            return $target;
        });
    }

    public static function storeChildParams($id, $params)
    {
        static::$pendingChildParams[$id] = $params;
    }

    public static function getProps($id, $propKeys)
    {
        $params = static::$pendingChildParams[$id] ?? [];

        $props = [];

        foreach ($params as $key => $value) {
            if (in_array($key, $propKeys)) {
                $props[$key] = $value;
            }
        }

        return $props;
    }

    public static function hasProp($id, $propKey)
    {
        $params = static::$pendingChildParams[$id] ?? [];

        foreach ($params as $key => $value) {
            if ($propKey === $key) return true;
        }

        return false;
    }
}
