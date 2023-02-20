<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use function Livewire\on;
use function Livewire\store;


class SupportWireModelingNestedComponents extends ComponentHook
{
    protected static $outersByComponentId = [];

    public static function provide()
    {
        on('flush-state', fn() => static::$outersByComponentId = []);

        // On a subsequent request, a parent encounters a child component
        // with wire:model on it, and that child has already been mounted
        // in a previous request, capture the value being passed in so we
        // can later set the child's property if it exists in this request.
        on('mount.stub', function ($tag, $id, $params, $parent, $key) {
            if (! isset($params['wire:model'])) return;

            $outer = $params['wire:model'];

            static::$outersByComponentId[$id] = [$outer => $parent->$outer];
        });
    }

    public function hydrate($meta)
    {
        if (! isset($meta['bindings'])) return;

        $bindings = $meta['bindings'];

        // Store the bindings for later dehydration...
        store($this->component)->set('bindings', $bindings);

        // If this child's parent already rendered its stub, retrieve
        // the memo'd value and set it.
        if (! isset(static::$outersByComponentId[$meta['id']])) return;

        $outers = static::$outersByComponentId[$meta['id']];

        foreach ($bindings as $outer => $inner) {
            $this->component->$inner = $outers[$outer];
        }
    }

    public function dehydrate($context)
    {
        $bindings = store($this->component)->get('bindings', false);

        if (! $bindings) return;

        // Add the bindings metadata to the payload for later reference...
        $context->addMeta('bindings', $bindings);

        return function () use ($bindings, $context) {
            // Currently we can only support a single wire:model bound value,
            // so we'll just get the first one. But in the future we will
            // likely want to support named bindings, so we'll keep
            // this value as an array.
            $outer = array_keys($bindings)[0];
            $inner = array_values($bindings)[0];

            // Attach the necessary Alpine directives so that the child and
            // parent's JS, ephemeral, values are bound.
            $context->effects['html'] = Utils::insertAttributesIntoHtmlRoot($context->effects['html'], [
                'x-model' => '$wire.$parent.'.$outer,
                'x-modelable' => '$wire.'.$inner,
            ]);
        };
    }
}
