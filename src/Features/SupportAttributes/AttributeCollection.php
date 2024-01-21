<?php

namespace Livewire\Features\SupportAttributes;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionObject;

class AttributeCollection extends Collection
{
    static function fromComponent($component, $subTarget = null, $propertyNamePrefix = '')
    {
        $instance = new static;

        $reflected = new ReflectionObject($subTarget ?? $component);

        foreach (static::getClassAttributesRecursively($reflected) as $attribute) {
            $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component, $subTarget) {
                $attribute->__boot($component, AttributeLevel::ROOT, null, null, $subTarget);
            }));
        }

        foreach ($reflected->getMethods() as $method) {
            foreach ($method->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component, $method, $propertyNamePrefix, $subTarget) {
                    $attribute->__boot($component, AttributeLevel::METHOD, $propertyNamePrefix . $method->getName(), $method->getName(), $subTarget);
                }));
            }
        }

        foreach ($reflected->getProperties() as $property) {
            foreach ($property->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component, $property, $propertyNamePrefix, $subTarget) {
                    $attribute->__boot($component, AttributeLevel::PROPERTY, $propertyNamePrefix . $property->getName(), $property->getName(), $subTarget);
                }));
            }
        }

        return $instance;
    }

    protected static function getClassAttributesRecursively($reflected) {
        $attributes = [];

        while ($reflected) {
            foreach ($reflected->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $attributes[] = $attribute;
            }

            $reflected = $reflected->getParentClass();
        }

        return $attributes;
    }
}
