<?php

namespace Livewire\Features\SupportComputed;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils as SyntheticUtils;

use function Livewire\on;
use function Livewire\store;
use function Livewire\wrap;

class SupportLegacyComputedPropertySyntax extends ComponentHook
{
    static function provide()
    {
        on('__get', function ($target, $property, $returnValue) {
            // Handle #[Computed] attribute properties (takes priority over legacy)...
            $attribute = static::findComputedAttribute($target, $property);

            if ($attribute) {
                $attribute->handleMagicGet($returnValue);

                return;
            }

            // Handle legacy computed properties (getXxxProperty pattern)...
            if (static::hasComputedProperty($target, $property)) {
                $returnValue(static::getComputedProperty($target, $property));
            }
        });

        on('__unset', function ($target, $property) {
            // Handle #[Computed] attribute properties (takes priority over legacy)...
            $attribute = static::findComputedAttribute($target, $property);

            if ($attribute) {
                $attribute->handleMagicUnset();

                return;
            }

            // Handle legacy computed properties (getXxxProperty pattern)...
            if (static::hasComputedProperty($target, $property)) {
                store($target)->unset('computedProperties', $property);
            }
        });
    }

    public static function findComputedAttribute($target, $property)
    {
        $propertyName = (string) str($property)->camel();

        return $target->getAttributes()
            ->whereInstanceOf(BaseComputed::class)
            ->first(fn ($attr) => $attr->getName() === $propertyName);
    }

    public static function getComputedProperties($target)
    {
        return collect(static::getComputedPropertyNames($target))
            ->mapWithKeys(function ($property) use ($target) {
                return [$property => static::getComputedProperty($target, $property)];
            })
            ->all();
    }

    public static function hasComputedProperty($target, $property)
    {
        return array_search((string) str($property)->camel(), static::getComputedPropertyNames($target)) !== false;
    }

    public static function getComputedProperty($target, $property)
    {
        if (! static::hasComputedProperty($target, $property)) {
            throw new \Exception('No computed property found: $'.$property);
        }

        $method = 'get'.str($property)->studly().'Property';

        store($target)->push(
            'computedProperties',
            $value = store($target)->find('computedProperties', $property, fn() => wrap($target)->$method()),
            $property,
        );

        return $value;
    }

    public static function getComputedPropertyNames($target)
    {
        $methodNames = SyntheticUtils::getPublicMethodsDefinedBySubClass($target);

        return collect($methodNames)
            ->filter(function ($method) {
                return str($method)->startsWith('get')
                    && str($method)->endsWith('Property');
            })
            ->map(function ($method) {
                return (string) str($method)->between('get', 'Property')->camel();
            })
            ->all();
    }
}
