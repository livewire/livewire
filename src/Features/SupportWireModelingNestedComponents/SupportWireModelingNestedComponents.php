<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils as SyntheticUtils;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\Drawer\Utils;

use function Livewire\after;
use function Livewire\on;
use function Livewire\store;

class SupportWireModelingNestedComponents extends ComponentHook
{
    protected static $outersByComponentId = [];

    public static function provide()
    {
        on('flush-state', fn() => static::$outersByComponentId = []);

        on('dummy-mount', function ($tag, $id, $params, $parent, $key) {
            if (! isset($params['wire:model'])) return;

            $outer = $params['wire:model'];

            static::$outersByComponentId[$id] = [$outer => $parent->$outer];
        });

        // When a Livewire component is rendered, we'll check to see if "wire:model" is set.
        on('mount', function ($name, $params, $parent, $key, $hijack) {
            return function ($target) use ($parent, $params) {

            };
        });
    }

    // We need to add a note that everytime we render this thing, we'll need to add
    // those extra Alpine attributes.
    public function dehydrate($context)
    {
        $wireModels = store($this->component)->get('wireModels', false);

        if (! $wireModels) return;

        $context->addMeta('wireModels', $wireModels);

        return function () use ($context, $wireModels) {
            if (! $context->effects['html']) return;

            foreach ($wireModels as $outer => $inner) {
            }
        };
    }

    // Now on subsequent renders, we can make a note...
    public function hydrate($meta)
    {
        if (! isset($meta['wireModels'])) return;

        $wireModels = $meta['wireModels'];

        store($this->component)->set('wireModels', $wireModels);

        if (! isset(static::$outersByComponentId[$meta['id']])) return;

        $outers = static::$outersByComponentId[$meta['id']];

        foreach ($wireModels as $outer => $inner) {
            $this->component->$inner = $outers[$outer];
        }
    }
}
