<?php

namespace Livewire\Concerns;

use Illuminate\Support\Arr;

trait InteractsWithProperties
{
    public function getPublicPropertiesDefinedBySubClass()
    {
        $publicProperties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
        $data = [];

        foreach ($publicProperties as $property) {
            if ($property->getDeclaringClass()->getName() !== self::class) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }

        return $data;
    }

    public function getAllPropertiesDefinedBySubClass()
    {
        $properties = (new \ReflectionClass($this))->getProperties();
        $data = [];

        foreach ($properties as $property) {
            if ($property->getDeclaringClass()->getName() !== self::class) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }

        return $data;
    }

    public function getPropertyValue($prop)
    {
        // This is used by wrappers. Otherwise,
        // users would have to declare props as "public".
        return $this->{$prop};
    }

    public function hasProperty($prop)
    {
        return property_exists($this, $prop);
    }

    public function setPropertyValue($name, $value)
    {
        $hasArrayKey = count(explode('.', $name)) > 1;

        if ($hasArrayKey) {
            $keys = explode('.', $name);
            $firstKey = array_shift($keys);
            Arr::set($this->{$firstKey}, implode('.', $keys), $value);
        } else {
            return $this->{$name} = $value;
        }
    }
}
