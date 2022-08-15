<?php

namespace Livewire\Features;

use Livewire\Synthesizers\LivewireSynth;
use Synthetic\Synthesizers\ObjectSynth;
use Synthetic\Utils;

class SupportReactiveProps
{
    public static $pendingChildParams = [];

    public function __invoke()
    {
        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $props = [];

            foreach (Utils::getAnnotations($target) as $key => $value) {
                if (isset($value['prop'])) $props[] = $key;
            }

            $props && $context->addMeta('props', $props);
        });

        app('synthetic')->on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;
            if (! isset($meta['props'])) return;

            $propKeys = $meta['props'];

            $props = static::getProps($meta['id'], $propKeys);

            return function ($target) use ($props) {
                foreach ($props as $key => $value) {
                    $target->{$key} = $value;
                }

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
}
