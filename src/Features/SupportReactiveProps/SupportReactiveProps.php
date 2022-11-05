<?php

namespace Livewire\Features\SupportReactiveProps;

use Synthetic\Utils as SyntheticUtils;
use Livewire\Mechanisms\DataStore;
use Livewire\LivewireSynth;

use function Livewire\store;

class SupportReactiveProps
{
    public static $pendingChildParams = [];

    public function boot()
    {
        app('synthetic')->on('flush-state', fn() => static::$pendingChildParams = []);

        app('synthetic')->on('mount', function ($name, $params, $parent, $key, $hijack) {
            return function ($target) use ($params) {
                $props = [];

                foreach (SyntheticUtils::getAnnotations($target) as $key => $value) {
                    if (isset($value['prop']) && isset($params[$key])) {
                        $props[] = $key;
                    }
                }

                store($target)->set('props', $props);
            };
        });

        app('synthetic')->on('dummy-mount', function ($tag, $id, $params, $parent, $key) {
            $this->storeChildParams($id, $params);
        });

        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $props = store($target)->get('props', []);
            $propHashes = store($target)->get('propHashes', []);

            foreach ($propHashes as $key => $hash) {
                if (crc32(json_encode($target->{$key})) !== $hash) {
                    throw new \Exception('Cant mutate a prop: ['.$key.']');
                }
            }

            $props && $context->addMeta('props', $props);
        });

        app('synthetic')->on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;
            if (! isset($meta['props'])) return;

            $propKeys = $meta['props'];

            $props = static::getProps($meta['id'], $propKeys);

            return function ($target) use ($props, $propKeys) {
                $propHashes = [];

                foreach ($props as $key => $value) {
                    $target->{$key} = $value;
                }

                foreach ($propKeys as $key) {
                    $propHashes[$key] = crc32(json_encode($target->{$key}));
                }

                store($target)->set('props', $propKeys);
                store($target)->set('propHashes', $propHashes);

                return $target;
            };
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
