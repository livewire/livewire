<?php

namespace Livewire\Features\SupportAttributes;

trait HandlesAttributes
{
    protected AttributeCollection $attributes;

    public function getAttributes()
    {
        return $this->attributes ??= AttributeCollection::fromComponent($this);
    }

    public function setPropertyAttribute($property, $attribute)
    {
        $attribute->__boot($this, AttributeLevel::PROPERTY, $property);

        $this->mergeOutsideAttributes(new AttributeCollection([$attribute]));
    }

    public function mergeOutsideAttributes(AttributeCollection $attributes)
    {
        $this->attributes = $this->getAttributes()->concat($attributes);
    }
}
