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
            $property = str($property);

            // Check if the property contains a dot which means it is actually on a nested object like a FormObject
            if (str($property)->contains('.')) {
                $propertyName = $property->afterLast('.');
                $objectName = $property->beforeLast('.');

                if (method_exists($this->{$objectName}, 'reset')) {
                    $this->{$objectName}->reset($propertyName);
                    continue;
                }

                $object = data_get($freshInstance, $objectName, null);

                if (is_object($object)) {
                    $isInitialized = (new \ReflectionProperty($object, (string) $propertyName))->isInitialized($object);
                } else {
                    $isInitialized = false;
                }
            } else {
                $isInitialized = (new \ReflectionProperty($freshInstance, (string) $property))->isInitialized($freshInstance);
            }

            // Handle resetting properties that are not initialized by default.
            if (! $isInitialized) {
                data_forget($this, (string) $property);
                continue;
            }

            data_set($this, $property, data_get($freshInstance, $property));
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

    public function pull($properties = null)
    {
        $wantsASingleValue = is_string($properties);

        $properties = is_array($properties) ? $properties : func_get_args();

        $beforeReset = match (true) {
            empty($properties) => $this->all(),
            $wantsASingleValue => $this->getPropertyValue($properties[0]),
            default => $this->only($properties),
        };

        $this->reset($properties);

        return $beforeReset;
    }

    public function only($properties)
    {
        $results = [];

        foreach (is_array($properties) ? $properties : func_get_args() as $property) {
            $results[$property] = $this->hasProperty($property) ? $this->getPropertyValue($property) : null;
        }

        return $results;
    }

    public function except($properties)
    {
        $properties = is_array($properties) ? $properties : func_get_args();

        return array_diff_key($this->all(), array_flip($properties));
    }

    public function all()
    {
        return Utils::getPublicPropertiesDefinedOnSubclass($this);
    }
}
