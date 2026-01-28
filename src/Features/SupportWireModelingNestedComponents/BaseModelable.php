<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use function Livewire\store;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseModelable extends LivewireAttribute
{
    public function mount($params, $parent, $attributes)
    {
        if (! $parent) return;

        $outer = null;

        foreach ($attributes as $key => $value) {
            if (str($key)->startsWith('wire:model')) {
                $outer = $value;
                store($this->component)->push('bindings-directives', $key, $value);
                break;
            }
        }

        if ($outer === null) return;

        $inner = $this->getName();

        store($this->component)->push('bindings', $inner, $outer);

        $this->setValue(data_get($parent, $outer));
    }

    // This update hook is for the following scenario:
    // An modelable value has changed in the browser.
    // A network request is triggered from the parent.
    // The request contains both parent and child component updates.
    // The parent finishes it's request and the "updated" value is
    // overridden in the parent's lifecycle (ex. a form field being reset).
    // Without this hook, the child's value will not honor that change
    // and will instead still be updated to the old value, even though
    // the parent changed the bound value. This hook detects if the parent
    // has provided a value during this request and ensures that it is the
    // final value for the child's request...
    function update($fullPath, $newValue)
    {
        if (store($this->component)->get('hasBeenSeeded', false)) {
            $oldValue = $this->getValue();

            return function () use ($oldValue) {
                $this->setValue($oldValue);
            };
        }
    }
}
