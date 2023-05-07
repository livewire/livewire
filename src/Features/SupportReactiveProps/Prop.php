<?php

namespace Livewire\Features\SupportReactiveProps;

use function Livewire\store;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class Prop extends LivewireAttribute
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
        $context->pushMemo('props', $this->getName());
    }
}
