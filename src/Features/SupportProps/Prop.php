<?php

namespace Livewire\Features\SupportProps;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[\Attribute]
class Prop extends LivewireAttribute
{
    function __construct(public $reactive = false) {}

    public function mount($params)
    {
        $property = $this->getName();

        if (! array_key_exists($property, $params)) return;

        $this->setValue($params[$property]);

        if ($this->reactive) {
            store($this->component)->push('reactiveProps', $property);
        }
    }

    public function dehydrate($context)
    {
        if (! $this->reactive) return;

        $context->pushMemo('props', $this->getName());
    }
}
