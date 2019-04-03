<?php

namespace Livewire\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

    public function hasProperty($prop)
    {
        return property_exists(
            $this,
            $this->beforeFirstDot($prop)
        );
    }

    public function getPropertyValue($name)
    {
        $value = $this->{$this->beforeFirstDot($name)};

        if ($this->containsDots($name)) {
            return data_get($value, $this->afterFirstDot($name));
        }

        return $value;
    }

    public function setPropertyValue($name, $value)
    {
        if ($this->containsDots($name)) {
            return data_set(
                $this->{$this->beforeFirstDot($name)},
                $this->afterFirstDot($name),
                $value
            );
        }

        return $this->{$name} = $value;
    }

    public function containsDots($subject)
    {
        return strpos($subject, '.') !== false;
    }

    public function beforeFirstDot($subject)
    {
        return head(explode('.', $subject));
    }

    public function afterFirstDot($subject)
    {
        return Str::after($subject, '.');
    }
}
