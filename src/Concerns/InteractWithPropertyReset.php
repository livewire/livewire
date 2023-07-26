<?php

namespace Livewire\Concerns;

use Livewire\Features\SupportFormObjects\Reset;
use Livewire\Features\SupportFormObjects\Form;

trait InteractWithPropertyReset
{
    public function reset(...$properties)
    {
        if ($this instanceof Form) {
            $properties = Reset::getResettableProperties($this, $properties);
        } else {
            if (count($properties) && is_array($properties[0])) {
                $properties = $properties[0];
            }

            if (empty($properties)) {
                $properties = array_keys($this->all());
            }
        }

        foreach ($properties as $property) {
            $freshInstance = $this instanceof Form
                ? new static($this->getComponent(), $this->getPropertyName())
                : new static;

            data_set($this, $property, data_get($freshInstance, $property));
        }
    }
}
