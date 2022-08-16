<?php

namespace Livewire\Features;

use Synthetic\Utils as SyntheticUtils;
use Livewire\Utils;
use Livewire\Synthesizers\LivewireSynth;

class SupportReactiveProps
{
    public static $pendingChildParams = [];

    public function __invoke()
    {
        app('synthetic')->on('dummy-mount', function ($tag, $id, $params, $parent, $key) {
            $this->storeChildParams($id, $params);
        });

        app('synthetic')->on('mount', function ($target, $id, $params, $parent, $key) {
            return function ($html) use ($parent, $key, $id) {


                return $html;
            };
        });

        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $props = [];

            foreach (SyntheticUtils::getAnnotations($target) as $key => $value) {
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
