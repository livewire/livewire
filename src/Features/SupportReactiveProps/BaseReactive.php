<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use function Livewire\store;

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
                // Queue the update to be processed after all hooks are hydrated
                // This ensures SupportLifecycleHooks is initialized before we trigger updates
                // Don't set the value yet - it will be set when processing the queue
                // so that updating* hooks see the old value
                SupportReactiveProps::queueUpdate(
                    $this->component->getId(),
                    $propertyName,
                    $updatedValue,
                    function ($value) {
                        $this->setValue($value);
                        // Update the hash so dehydrate doesn't think we mutated the prop
                        $this->originalValueHash = crc32(json_encode($value));
                    }
                );

                // Return early - hash will be updated when the queue is processed
                return;
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
