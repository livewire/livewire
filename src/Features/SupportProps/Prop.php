<?php

namespace Livewire\Features\SupportProps;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[\Attribute]
class Prop extends LivewireAttribute
{
    function __construct(public $reactive = false) {}

    protected $originalValueHash;

    public function mount($params)
    {
        $property = $this->getName();

        if (! array_key_exists($property, $params)) return;

        $this->setValue($params[$property]);

        if ($this->reactive) {
            store($this->component)->push('reactiveProps', $property);
        }

        $this->originalValueHash = crc32(json_encode($this->getValue()));
    }

    public function hydrate()
    {
        $updatedValue = SupportProps::getPassedInProp(
            $this->component->getId(), $this->getName()
        );

        $this->setValue($updatedValue);

        $this->originalValueHash = crc32(json_encode($this->getValue()));
    }

    public function dehydrate($context)
    {
        if (! $this->reactive) return;

        if ($this->originalValueHash !== crc32(json_encode($this->getValue()))) {
            throw new CannotMutateReactivePropException($this->component->getName(), $this->getName());
        }

        $context->pushMemo('props', $this->getName());
    }
}
