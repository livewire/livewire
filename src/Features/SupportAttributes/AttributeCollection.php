<?php

namespace Livewire\Features\SupportAttributes;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionObject;

class AttributeCollection extends Collection
{
    static function fromComponent($target)
    {
        $instance = new static;

        $reflected = new ReflectionObject($target);

        foreach ($reflected->getAttributes() as $attribute) {
            $instance->push(tap($attribute->newInstance(), function ($attribute) use ($target) {
                $attribute->__boot($target, AttributeLevel::ROOT);
            }));
        }

        foreach ($reflected->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $instance->push(tap($attribute->newInstance(), function ($attribute) use ($target, $method) {
                    $attribute->__boot($target, AttributeLevel::METHOD, $method->getName());
                }));
            }
        }

        foreach ($reflected->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $instance->push(tap($attribute->newInstance(), function ($attribute) use ($target, $property) {
                    $attribute->__boot($target, AttributeLevel::PROPERTY, $property->getName());
                }));
            }
        }

        return $instance;
    }
}
