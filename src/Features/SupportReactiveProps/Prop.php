<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\PropertyHook;

use function Livewire\store;

#[\Attribute]
class Prop extends PropertyHook
{
    public function mount($params)
    {
        $property = $this->getName();

        if (! array_key_exists($property, $params)) return;

        $this->setValue($params[$property]);

        store($this->component)->push('props', $property);
    }

    public function dehydrate($context)
    {
        $context->pushMeta('props', $this->getName());
    }
}
