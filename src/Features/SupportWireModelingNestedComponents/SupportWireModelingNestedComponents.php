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
        on('mount.stub', function ($tag, $id, $params, $parent, $key, $slots, $attributes) {
            $outer = collect($attributes)->first(function ($value, $key) {
                return str($key)->startsWith('wire:model');
            });

            if (! $outer) return;

            static::$outersByComponentId[$id] = [$outer => data_get($parent, $outer)];
        });
    }

    public function hydrate($memo)
    {
        if (! isset($memo['bindings'])) return;

        $bindings = $memo['bindings'];
        $directives = $memo['bindingsDirectives'];

        // Store the bindings for later dehydration...
        store($this->component)->set('bindings', $bindings);
        store($this->component)->set('bindings-directives', $directives);

        // If this child's parent already rendered its stub, retrieve
        // the memo'd value and set it.
        if (! isset(static::$outersByComponentId[$memo['id']])) return;

        $outers = static::$outersByComponentId[$memo['id']];

        foreach ($bindings as $outer => $inner) {
            store($this->component)->set('hasBeenSeeded', true);

            $this->component->$inner = $outers[$outer];
        }
    }

    public function render($view, $data)
    {
        return function ($html, $replaceHtml) {
            $bindings = store($this->component)->get('bindings', false);
            $directives = store($this->component)->get('bindings-directives', false);

            if (! $bindings) return;

            // Currently we can only support a single wire:model bound value,
            // so we'll just get the first one. But in the future we will
            // likely want to support named bindings, so we'll keep
            // this value as an array.
            $outer = array_keys($bindings)[0];
            $inner = array_values($bindings)[0];
            $directive = array_values($directives)[0];

            // Attach the necessary Alpine directives so that the child and
            // parent's JS, ephemeral, values are bound.
            $replaceHtml(Utils::insertAttributesIntoHtmlRoot($html, [
                $directive =>  '$parent.'.$outer,
                'x-modelable' => '$wire.'.$inner,
            ]));
        };
    }

    public function dehydrate($context)
    {
        $bindings = store($this->component)->get('bindings', false);

        if (! $bindings) return;

        $directives = store($this->component)->get('bindings-directives');

        // Add the bindings metadata to the paylad for later reference...
        $context->addMemo('bindings', $bindings);
        $context->addMemo('bindingsDirectives', $directives);
    }
}
