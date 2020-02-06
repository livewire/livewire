<?php

namespace Livewire\ComponentConcerns;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait InteractsWithProperties
{
    protected $casts = [];

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

    public function getProtectedOrPrivatePropertiesDefinedBySubClass()
    {
        $properties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);
        $data = [];

        foreach ($properties as $property) {
            if ($property->getDeclaringClass()->getName() !== self::class) {
                $property->setAccessible(true);
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

    public function setProtectedPropertyValue($name, $value)
    {
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

    public function propertyIsPublicAndNotDefinedOnBaseClass($propertyName)
    {
        return collect((new \ReflectionClass($this))->getProperties(\ReflectionMethod::IS_PUBLIC))
            ->reject(function ($property) {
                return $property->class === self::class;
            })
            ->pluck('name')
            ->search($propertyName) !== false;
    }

    public function fill($values)
    {
        $publicProperties = array_keys($this->getPublicPropertiesDefinedBySubClass());

        if ($values instanceof Model) {
            $values = $values->toArray();
        }

        foreach ($values as $key => $value) {
            if (in_array($key, $publicProperties)) {
                $this->{$key} = $value;
            }
        }
    }

    public function reset(...$properties)
    {
        $propertyKeys = array_keys($this->getPublicPropertiesDefinedBySubClass());

        if (empty($properties)) {
            $properties = $propertyKeys;
        }

        foreach ($properties as $property) {
            $freshInstance = new static($this->id);

            $this->{$property} = $freshInstance->{$property};
        }
    }
}
