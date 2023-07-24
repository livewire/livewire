<?php

namespace Livewire\Drawer;

use ReflectionClass;

class BaseUtils
{
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
        return static::getPublicProperties($target, function ($property) {
            // Filter out any properties from the first-party Component class...
            return $property->getDeclaringClass()->getName() !== \Livewire\Component::class;
        });
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
                    $value = (method_exists($property->getType(), 'getName') && $property->getType()->getName() === 'array')
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
            $isInBaseComponentClass = $method->getDeclaringClass()->getName() === \Livewire\Component::class;

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
        return (new ReflectionClass($target))->getProperty($property);
    }

    static function propertyHasAnnotation($target, $property, $annotation) {
        foreach (static::getAnnotations($target) as $prop => $annotations) {
            if ($prop === $property && array_key_exists($annotation, $annotations)) {
                return true;
            }
        }

        return false;
    }

    static function getAnnotations($target) {
        if (! is_object($target)) return [];

        return collect()
            ->concat((new \ReflectionClass($target))->getProperties())
            ->concat((new \ReflectionClass($target))->getMethods())
            ->filter(function ($subject) use ($target) {
                if ($subject->class !== get_class($target)) return false;
                if ($subject->getDocComment() === false) return false;
                return true;
            })
            ->mapWithKeys(function ($subject) {
                return [$subject->getName() => static::parseAnnotations($subject->getDocComment())];
            })->toArray();
    }

    static function parseAnnotations($raw) {
        return str($raw)
            ->matchAll('/\@([^\*]+)/')
            ->mapWithKeys(function ($line) {
                $segments = explode(' ', trim($line));

                $annotation = array_shift($segments);

                return [$annotation => $segments];
            })
            ->toArray();
    }
}
