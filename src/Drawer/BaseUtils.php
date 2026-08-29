<?php

namespace Livewire\Drawer;

class BaseUtils
{
    protected static $reflectionCache = [];

    public static function flushReflectionCache()
    {
        static::$reflectionCache = [];
    }

    static function isSyntheticTuple($payload) {
        return is_array($payload)
            && count($payload) === 2
            && isset($payload[1]['s']);
    }

    static function isAPrimitive($target) {
        return
            is_numeric($target) ||
            is_string($target) ||
            is_bool($target) ||
            is_null($target);
    }

    static function getPublicPropertiesDefinedOnSubclass($target) {
        $class = get_class($target);

        if (!isset(static::$reflectionCache[$class])) {
            static::$reflectionCache[$class] = static::reflectAndCachePropertyMetadata($target, function ($property) {
                return $property->getDeclaringClass()->getName() !== \Livewire\Component::class
                    && $property->getDeclaringClass()->getName() !== \Livewire\Volt\Component::class;
            });
        }

        return static::extractPropertyValuesFromInstance($target, static::$reflectionCache[$class]);
    }

    protected static function reflectAndCachePropertyMetadata($target, $filter = null)
    {
        return collect((new \ReflectionObject($target))->getProperties())
            ->filter(function ($property) {
                return $property->isPublic() && ! $property->isStatic() && $property->isDefault();
            })
            ->filter($filter ?? fn () => true)
            ->mapWithKeys(function ($property) {
                $type = null;
                if (method_exists($property, 'getType') && $property->getType()) {
                    $type = method_exists($property->getType(), 'getName')
                        ? $property->getType()->getName()
                        : null;
                }

                return [$property->getName() => [
                    'name' => $property->getName(),
                    'type' => $type,
                ]];
            })
            ->all();
    }

    protected static function extractPropertyValuesFromInstance($target, $cachedMetadata)
    {
        $properties = [];
        $reflection = new \ReflectionObject($target); // One reflection object for all properties

        foreach ($cachedMetadata as $propertyName => $meta) {
            $property = $reflection->getProperty($propertyName);

            if (method_exists($property, 'isInitialized') && !$property->isInitialized($target)) {
                $value = ($meta['type'] === 'array') ? [] : null;
            } else {
                $value = $property->getValue($target);
            }

            $properties[$propertyName] = $value;
        }

        return $properties;
    }

    static function getPublicProperties($target, $filter = null)
    {
        return collect((new \ReflectionObject($target))->getProperties())
            ->filter(function ($property) {
                return $property->isPublic() && ! $property->isStatic() && $property->isDefault();
            })
            ->filter($filter ?? fn () => true)
            ->mapWithKeys(function ($property) use ($target) {
                // Ensures typed property is initialized in PHP >=7.4, if so, return its value,
                // if not initialized, return null (as expected in earlier PHP Versions)
                if (method_exists($property, 'isInitialized') && !$property->isInitialized($target)) {
                    // If a type of `array` is given with no value, let's assume users want
                    // it prefilled with an empty array...
                    $value = (method_exists($property, 'getType') && $property->getType() && method_exists($property->getType(), 'getName') && $property->getType()->getName() === 'array')
                        ? [] : null;
                } else {
                    $value = $property->getValue($target);
                }

                return [$property->getName() => $value];
            })
            ->all();
    }

    static function getPublicMethodsDefinedBySubClass($target)
    {
        $methods = array_filter((new \ReflectionObject($target))->getMethods(), function ($method) {
            $isInBaseComponentClass = $method->getDeclaringClass()->getName() === \Livewire\Component::class || $method->getDeclaringClass()->getName() === \Livewire\Volt\Component::class;

            return $method->isPublic()
                && ! $method->isStatic()
                && ! $isInBaseComponentClass;
        });

        return array_map(function ($method) {
            return $method->getName();
        }, $methods);
    }

    static function hasAttribute($target, $property, $attributeClass) {
        $property = static::getProperty($target, $property);

        foreach ($property->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            if ($instance instanceof $attributeClass) return true;
        }

        return false;
    }

    static function getProperty($target, $property) {
        return (new \ReflectionObject($target))->getProperty($property);
    }

    static function propertyIsTyped($target, $property) {
        $property = static::getProperty($target, $property);

        return $property->hasType();
    }

    static function propertyIsTypedAndUninitialized($target, $property) {
        $property = static::getProperty($target, $property);

        return $property->hasType() && (! $property->isInitialized($target));
    }

    /**
     * Compare a value's native PHP type with a property's declared type.
     *
     * This deliberately does not apply PHP's weak scalar coercion. It returns
     * null when the property is missing or untyped because there is no declared
     * type contract to compare the value against.
     */
    static function propertyTypeMatchesValue($target, $property, $value): ?bool {
        if (! $property || ! property_exists($target, $property)) return null;

        $reflectionProperty = static::getProperty($target, $property);

        if (! $type = $reflectionProperty->getType()) return null;

        return static::typeMatchesValue($type, $value, $reflectionProperty->getDeclaringClass());
    }

    protected static function typeMatchesValue($type, $value, $declaringClass): bool {
        if ($value === null) return $type->allowsNull();

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if (static::typeMatchesValue($unionType, $value, $declaringClass)) return true;
            }

            return false;
        }

        if ($type instanceof \ReflectionIntersectionType) {
            foreach ($type->getTypes() as $intersectionType) {
                if (! static::typeMatchesValue($intersectionType, $value, $declaringClass)) return false;
            }

            return true;
        }

        $name = $type->getName();

        return match ($name) {
            'mixed' => true,
            'string' => is_string($value),
            'int' => is_int($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'iterable' => is_iterable($value),
            'callable' => is_callable($value),
            'false' => $value === false,
            'true' => $value === true,
            'self', 'static' => $value instanceof ($declaringClass->getName()),
            'parent' => ($parent = $declaringClass->getParentClass()) && $value instanceof ($parent->getName()),
            default => $value instanceof $name,
        };
    }
}
