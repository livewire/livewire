<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use function Livewire\store;

#[\Attribute]
class Reactive extends LivewireAttribute
{
    function __construct() {}

    protected $originalValueHash;

    public function mount($params)
    {
        $property = $this->getName();

        store($this->component)->push('reactiveProps', $property);

        $this->originalValueHash = crc32(json_encode($this->getValue()));
    }

    public function hydrate()
    {
        if (SupportReactiveProps::hasPassedInProps($this->component->getId())) {
            $updatedValue = SupportReactiveProps::getPassedInProp(
                $this->component->getId(), $this->getName()
            );

            $this->setValue($updatedValue);
        }

        $this->originalValueHash = crc32(json_encode($this->getValue()));
    }

    public function dehydrate($context)
    {
        if ($this->originalValueHash !== crc32(json_encode($this->getValue()))) {
            throw new CannotMutateReactivePropException($this->component->getName(), $this->getName());
        }

        $context->pushMemo('props', $this->getName());
    }
}
