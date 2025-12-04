<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use function Livewire\store;
use function Livewire\trigger;

#[\Attribute]
class BaseReactive extends LivewireAttribute
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

            $currentValue = $this->getValue();
            $propertyName = $this->getName();

            // Only trigger lifecycle hooks if value actually changed
            // Use the same comparison method as dehydrate() for consistency (handles arrays/objects)
            $currentHash = crc32(json_encode($currentValue));
            $updatedHash = crc32(json_encode($updatedValue));

            if ($currentHash !== $updatedHash) {
                // Trigger 'updating' and 'updated' lifecycle hooks (same as wire:model updates)
                // This allows components to react to reactive prop changes via updated*() methods
                // Note: trigger('update') expects the full path, which for simple properties is just the property name
                $finish = trigger('update', $this->component, $propertyName, $updatedValue);

                // Set the value after updating hooks but before updated hooks (same as HandleComponents::updateProperty)
                // This ensures updating* hooks see the old value, and updated* hooks see the new value
                $this->setValue($updatedValue);

                // Call the 'updated*' lifecycle hooks
                $finish();
            } else {
                $this->setValue($updatedValue);
            }
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
