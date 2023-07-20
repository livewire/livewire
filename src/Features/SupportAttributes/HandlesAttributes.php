<?php

namespace Livewire\Features\SupportAttributes;

trait HandlesAttributes
{
    protected AttributeCollection $attributes;

    function getAttributes()
    {
        return $this->attributes ??= AttributeCollection::fromComponent($this);
    }

    function setPropertyAttribute($property, $attribute)
    {
        $attribute->__boot($this, AttributeLevel::PROPERTY, $property);

        $this->mergeOutsideAttributes(new AttributeCollection([$attribute]));
    }

    function mergeOutsideAttributes(AttributeCollection $attributes)
    {
        $this->attributes = $this->getAttributes()->concat($attributes);
    }
}
