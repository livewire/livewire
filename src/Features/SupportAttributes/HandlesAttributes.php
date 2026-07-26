<?php

namespace Livewire\Features\SupportAttributes;

use function Livewire\store;

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

        // Attributes can arrive after a one-shot lifecycle window has
        // already run (a #[Virtual] form object materializing mid-request).
        // Replay the missed windows on just the late arrivals so arrival
        // time never changes behavior...
        $arrivals = $attributes->whereInstanceOf(Attribute::class);

        foreach (store($this)->get('attributeWindows', []) as [$window, $params]) {
            $arrivals->each(function ($attribute) use ($window, $params) {
                if (method_exists($attribute, $window)) {
                    $attribute->$window(...$params);
                }
            });
        }
    }

    function forgetAttributesWhere($callback)
    {
        $this->attributes = $this->getAttributes()->reject($callback)->values();
    }
}
