<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseReactive extends LivewireAttribute
{
    function __construct() {}

    protected $originalValueHash;

    public function mount($params)
    {
        $property = $this->getName();

        $this->storePush('reactiveProps', $property);

        $this->originalValueHash = SupportReactiveProps::hashValue($this->getValue());
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
            if (! SupportReactiveProps::hashesMatch($currentValue, $updatedValue)) {
                // Set value immediately so lifecycle hooks (boot/hydrate/booted) see updated data
                $this->setValue($updatedValue);
                $this->originalValueHash = SupportReactiveProps::hashValue($updatedValue);

                // Queue the update trigger for after hydrate so updating*/updated* hooks fire
                // Pass both old and new values plus a setValue callback so that
                // updating* hooks can temporarily see the old value
                SupportReactiveProps::queueUpdate(
                    $this->component->getId(),
                    $propertyName,
                    $currentValue,
                    $updatedValue,
                    fn ($value) => $this->setValue($value),
                );

                return;
            }
        }

        $this->originalValueHash = SupportReactiveProps::hashValue($this->getValue());
    }

    public function dehydrate($context)
    {
        if ($this->originalValueHash !== SupportReactiveProps::hashValue($this->getValue())) {
            throw new CannotMutateReactivePropException($this->component->getName(), $this->getName());
        }

        $context->pushMemo('props', $this->getName());
    }
}
