<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use function Livewire\store;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class Modelable extends LivewireAttribute
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
