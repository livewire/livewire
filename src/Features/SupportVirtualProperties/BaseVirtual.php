<?php

namespace Livewire\Features\SupportVirtualProperties;

use Livewire\Features\SupportAttributes\Attribute;

// The marker that turns a method into a virtual property. Discovery,
// initialization, and the value lookup all live on the component itself
// (see HandlesVirtualProperties) — the attribute's only job at runtime is
// making sure the method isn't callable as an action...
#[\Attribute]
class BaseVirtual extends Attribute
{
    function call()
    {
        throw new CannotCallVirtualPropertyDirectlyException(
            $this->component->getName(), $this->getName(),
        );
    }

    function getName()
    {
        return (string) str(parent::getName())->camel();
    }
}
