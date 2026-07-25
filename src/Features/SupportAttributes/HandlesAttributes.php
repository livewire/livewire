<?php

namespace Livewire\Features\SupportAttributes;

trait HandlesAttributes
{
    protected AttributeCollection $attributes;

    // Lifecycle phases that have already run for this component, so attributes
    // registered later can catch up on the ones they missed...
    protected array $elapsedAttributePhases = [];

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

        // Attributes can arrive after the lifecycle has moved on — a virtual
        // property is built by its own method, so a form object's attributes
        // only exist once that method has run. Replay the phases they missed
        // instead of letting them silently do nothing...
        foreach ($this->elapsedAttributePhases as [$phase, $params]) {
            foreach ($attributes as $attribute) {
                if (! $attribute instanceof Attribute) continue;

                if (method_exists($attribute, $phase)) $attribute->{$phase}(...$params);
            }
        }
    }

    // Drop every attribute registered on behalf of a sub-target (a form object
    // being replaced, say) so its rules and effects don't pile up...
    function forgetOutsideAttributes($subTarget)
    {
        $this->attributes = $this->getAttributes()->reject(
            fn ($attribute) => $attribute instanceof Attribute && $attribute->getSubTarget() === $subTarget
        )->values();
    }

    function markAttributePhaseAsElapsed($phase, $params = [])
    {
        $this->elapsedAttributePhases[] = [$phase, $params];
    }
}
