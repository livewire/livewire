<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use Livewire\PropertyHook;
use function Livewire\store;

#[\Attribute]
class Modelable extends PropertyHook
{
    public function mount($params, $parent)
    {
        if ($parent && isset($params['wire:model'])) {
            $outer = $params['wire:model'];
            $inner = $this->getName();

            store($this->component)->push('bindings', $inner, $outer);

            $this->setValue($parent->{$outer});
        }
    }
}
