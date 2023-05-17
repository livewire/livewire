<?php

namespace Livewire\Features\SupportAttributes;

use ReflectionObject;
use ReflectionAttribute;
use Attribute;

trait HandlesAttributes
{
    protected AttributeCollection $attributes;

    function getAttributes()
    {
        return $this->attributes ??= AttributeCollection::fromComponent($this);
    }

    function mergeOutsideAttributes(AttributeCollection $attributes)
    {
        $this->attributes = $this->getAttributes()->concat($attributes);
    }
}
