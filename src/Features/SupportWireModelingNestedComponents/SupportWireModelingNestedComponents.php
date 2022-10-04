<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use Synthetic\Utils as SyntheticUtils;
use Livewire\Synthesizers\LivewireSynth;
use Livewire\Mechanisms\ComponentDataStore;
use Livewire\Drawer\Utils;

class SupportWireModelingNestedComponents
{
    protected $outersByComponentId = [];

    public function boot()
    {
        app('synthetic')->on('flush-state', fn() => static::$outersByComponentId = []);

        // When a Livewire component is rendered, we'll check to see if "wire:model" is set.
        app('synthetic')->on('mount', function ($name, $params, $parent, $key, $slots, $hijack) {
            return function ($target) use ($parent, $params) {
                if ($parent && isset($params['wire:model'])) {
                    $outer = $params['wire:model'];

                    foreach (SyntheticUtils::getAnnotations($target) as $propertyName => $annotations) {
                        if (array_key_exists('modelable', $annotations)) {
                            $inner = $propertyName;
                        }
                    }

                    // We couldn't find a "modelable" property in the child.
                    if (! isset($inner)) return $target;

                    $wireModels = ComponentDataStore::get($target, 'wireModels', []);
                    $wireModels[$outer] = $inner;
                    ComponentDataStore::set($target, 'wireModels', $wireModels);

                    $target->$inner = $parent->$outer;
                }
            };
        });

        app('synthetic')->on('dummy-mount', function ($tag, $id, $params, $parent, $key) {
            if (! isset($params['wire:model'])) return;

            $outer = $params['wire:model'];

            $this->outersByComponentId[$id] = [$outer => $parent->$outer];
        });

        // We need to add a note that everytime we render this thing, we'll need to add
        // those extra Alpine attributes.
        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;
            $wireModels = ComponentDataStore::get($target, 'wireModels', false);
            if (! $wireModels) return;

            $context->addMeta('wireModels', $wireModels);

            return function ($thing) use ($target, $context, $wireModels) {
                foreach ($wireModels as $outer => $inner) {
                    $context->effects['html'] = Utils::insertAttributesIntoHtmlRoot($context->effects['html'], [
                        'x-model' => '$wire.$parent.'.$outer,
                        'x-modelable' => '$wire.'.$inner,
                    ]);
                }

                return $thing;
            };
        });

        // Now on subsequent renders, we can make a note
        app('synthetic')->on('hydrate', function ($synth, $value, $meta) {
            if (! $synth instanceof LivewireSynth) return;
            if (! isset($meta['wireModels'])) return;

            return function ($target) use ($meta) {
                $wireModels = $meta['wireModels'];

                ComponentDataStore::set($target, 'wireModels', $wireModels);

                if (! isset($this->outersByComponentId[$meta['id']])) return $target;

                $outers = $this->outersByComponentId[$meta['id']];

                foreach ($wireModels as $outer => $inner) {
                    $target->$inner = $outers[$outer];
                }

                return $target;
            };
        });
    }
}
