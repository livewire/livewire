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
                // Set value immediately so lifecycle hooks (boot/hydrate/booted) see updated data
                $this->setValue($updatedValue);
                $this->originalValueHash = crc32(json_encode($updatedValue));

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
