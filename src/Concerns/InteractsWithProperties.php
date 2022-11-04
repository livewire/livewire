<?php

namespace Livewire\Concerns;

use Livewire\Drawer\Utils;
use Illuminate\Database\Eloquent\Model;

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
        $propertyKeys = array_keys($this->all());

        // Keys to reset from array
        if (count($properties) && is_array($properties[0])) {
            $properties = $properties[0];
        }

        // Reset all
        if (empty($properties)) {
            $properties = $propertyKeys;
        }

        foreach ($properties as $property) {
            $freshInstance = new static;

            $this->{$property} = $freshInstance->{$property};
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
        return array_diff_key($this->all(), array_flip($properties));
    }

    public function all()
    {
        return Utils::getPublicPropertiesDefinedOnSubclass($this);
    }
}
