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
            if (static::hasComputedProperty($target, $property)) {
                $returnValue(static::getComputedProperty($target, $property));
            }
        });

        on('__unset', function ($target, $property) {
            if (static::hasComputedProperty($target, $property)) {
                store($target)->unset('computedProperties', $property);
            }
        });
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
