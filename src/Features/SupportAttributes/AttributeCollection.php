<?php

namespace Livewire\Features\SupportAttributes;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;

class AttributeCollection extends Collection
{
    protected static array $metadataCache = [];

    public static function flushCache(): void
    {
        static::$metadataCache = [];
    }

    static function fromComponent($component, $subTarget = null, $propertyNamePrefix = '')
    {
        $target = $subTarget ?? $component;
        $class = get_class($target);

        $metadata = static::$metadataCache[$class] ??= static::discoverClassAttributeMetadata($class);

        if (! $metadata['hasAny']) {
            return new static;
        }

        $instance = new static;

        foreach ($metadata['class'] as $attribute) {
            $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component, $subTarget) {
                $attribute->__boot($component, AttributeLevel::ROOT, null, null, $subTarget);
            }));
        }

        foreach ($metadata['methods'] as $methodName => $attributes) {
            foreach ($attributes as $attribute) {
                $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component, $methodName, $propertyNamePrefix, $subTarget) {
                    $attribute->__boot($component, AttributeLevel::METHOD, $propertyNamePrefix . $methodName, $methodName, $subTarget);
                }));
            }
        }

        foreach ($metadata['properties'] as $propertyName => $attributes) {
            foreach ($attributes as $attribute) {
                $instance->push(tap($attribute->newInstance(), function ($attribute) use ($component, $propertyName, $propertyNamePrefix, $subTarget) {
                    $attribute->__boot($component, AttributeLevel::PROPERTY, $propertyNamePrefix . $propertyName, $propertyName, $subTarget);
                }));
            }
        }

        return $instance;
    }

    protected static function discoverClassAttributeMetadata(string $class): array
    {
        $reflected = new ReflectionClass($class);

        $classAttrs = static::getClassAttributesRecursively($reflected);

        $methodAttrs = [];
        foreach ($reflected->getMethods() as $method) {
            $attrs = $method->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF);
            if (! empty($attrs)) {
                $methodAttrs[$method->getName()] = $attrs;
            }
        }

        $propertyAttrs = [];
        foreach ($reflected->getProperties() as $property) {
            $attrs = $property->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF);
            if (! empty($attrs)) {
                $propertyAttrs[$property->getName()] = $attrs;
            }
        }

        return [
            'hasAny' => ! empty($classAttrs) || ! empty($methodAttrs) || ! empty($propertyAttrs),
            'class' => $classAttrs,
            'methods' => $methodAttrs,
            'properties' => $propertyAttrs,
        ];
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
