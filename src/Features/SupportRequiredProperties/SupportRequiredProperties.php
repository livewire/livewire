<?php

namespace Livewire\Features\SupportRequiredProperties;

use Livewire\ComponentHook;

class SupportRequiredProperties extends ComponentHook
{
    public function mount($params)
    {
        $requiredProperties = $this->component
            ->getAttributes()
            ->whereInstanceOf(BaseRequired::class)
            ->map(fn ($property) => $property->getName());

        $missingProperties = $requiredProperties->diff(array_keys($params));

        throw_if(
            $missingProperties->count(),
            new RequiredPropertyNotProvidedException($this->component->getName(), $missingProperties),
        );
    }
}
