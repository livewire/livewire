<?php

namespace Livewire\Features\SupportAttributes;

use Attribute;
use ReflectionObject;
use ReflectionAttribute;

trait HandlesAttributes
{
    protected AttributeCollection $attributes;

    function getAttributes()
    {
        return $this->attributes ??= AttributeCollection::fromComponent($this);
    }
}
