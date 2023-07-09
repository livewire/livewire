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

        foreach ($reflected->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component) {
                $attribute->__boot($component, AttributeLevel::ROOT);
            }));
        }

        foreach ($reflected->getMethods() as $method) {
            foreach ($method->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component, $method, $propertyNamePrefix) {
                    $attribute->__boot($component, AttributeLevel::METHOD, $propertyNamePrefix . $method->getName());
                }));
            }
        }

        foreach ($reflected->getProperties() as $property) {
            foreach ($property->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component, $property, $propertyNamePrefix) {
                    $attribute->__boot($component, AttributeLevel::PROPERTY, $propertyNamePrefix . $property->getName());
                }));
            }
        }

        return $instance;
    }
}
