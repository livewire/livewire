<?php

namespace Livewire\Concerns;

use Livewire\Features\SupportFormObjects\Form;

trait InteractWithPropertyReset
{
    public function reset(...$properties)
    {
        $properties = count($properties) && is_array($properties[0])
            ? $properties[0]
            : $properties;

        $form = $this instanceof Form;

        if (empty($properties) && $form) $properties = array_keys($this->all());

        $freshInstance = $form
            ? new static($this->getComponent(), $this->getPropertyName())
            : new static;

        foreach ($properties as $property) data_set($this, $property, data_get($freshInstance, $property));
    }
}
