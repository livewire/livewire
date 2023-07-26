<?php

namespace Livewire\Concerns;

use Livewire\Features\SupportFormObjects\Reset;
use Livewire\Features\SupportFormObjects\Form;

trait InteractWithPropertyReset
{
    public function reset(...$properties)
    {
        $properties = count($properties) && is_array($properties[0])
            ? $properties[0]
            : $properties;

        $livewireForm = $this instanceof Form;
        $properties = !$livewireForm ? $properties : Reset::getResettableProperties($this, $properties);

        foreach ($properties as $property) {
            $freshInstance = $livewireForm
                ? new static($this->getComponent(), $this->getPropertyName())
                : new static;

            data_set($this, $property, data_get($freshInstance, $property));
        }
    }
}
