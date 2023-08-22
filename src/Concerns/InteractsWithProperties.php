<?php

namespace Livewire\Concerns;

use Illuminate\Database\Eloquent\Model;
use Livewire\Drawer\Utils;

trait InteractsWithProperties
{
    public function hasProperty($prop)
    {
        return property_exists($this, Utils::beforeFirstDot($prop));
    }

    public function getPropertyValue($name)
    {
        $value = $this->{Utils::beforeFirstDot($name)};

        if (Utils::containsDots($name)) {
            return data_get($value, Utils::afterFirstDot($name));
        }

        return $value;
    }

    public function fill($values)
    {
        $publicProperties = array_keys($this->all());

        if ($values instanceof Model) {
            $values = $values->toArray();
        }

        foreach ($values as $key => $value) {
            if (in_array(Utils::beforeFirstDot($key), $publicProperties)) {
                data_set($this, $key, $value);
            }
        }
    }

    public function reset(...$properties)
    {
        $properties = count($properties) && is_array($properties[0])
            ? $properties[0]
            : $properties;

        // Reset all
        if (empty($properties)) {
            $properties = array_keys($this->all());
        }

        $freshInstance = new static;

        foreach ($properties as $property) {
            $defaultValue = data_get($freshInstance, $property);
            $unset = '__unset__';
            $unsetByDefault = !$defaultValue && $unset === data_get($freshInstance, $property, $unset);

            // Handle resetting properties that are unset by default.
            if ($unsetByDefault) {
                data_forget($this, $property);
                continue;
            }

            data_set($this, $property, $defaultValue);
        }
    }

    protected function resetExcept(...$properties)
    {
        if (count($properties) && is_array($properties[0])) {
            $properties = $properties[0];
        }

        $keysToReset = array_diff(array_keys($this->all()), $properties);

        $this->reset($keysToReset);
    }

    public function only($properties)
    {
        $results = [];

        foreach ($properties as $property) {
            $results[$property] = $this->hasProperty($property) ? $this->getPropertyValue($property) : null;
        }

        return $results;
    }

    public function except($properties)
    {
        if (! is_array($properties)) $properties = [$properties];

        return array_diff_key($this->all(), array_flip($properties));
    }

    public function all()
    {
        return Utils::getPublicPropertiesDefinedOnSubclass($this);
    }
}
