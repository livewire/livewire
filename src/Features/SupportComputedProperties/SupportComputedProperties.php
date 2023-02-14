<?php

namespace Livewire\Features\SupportComputedProperties;

use Livewire\ComponentHook;
use Livewire\Drawer\ImplicitlyBoundMethod;
use Livewire\Mechanisms\DataStore;
use Livewire\Drawer\Utils as SyntheticUtils;

use function Livewire\on;
use function Livewire\store;
use function Livewire\wrap;

class SupportComputedProperties extends ComponentHook
{
    static function provide()
    {
        on('render', function ($target, $view, $data) {
            foreach (static::getComputedProperties($target) as $property => $value) {
                isset($view[$property]) || $view->with($property, $value);
            };

            foreach (static::getGetterProperties($target) as $property => $value) {
                isset($view[$property]) || $view->with($property, $value);
            };
        });

        on('__get', function ($target, $property, $returnValue) {
            if (static::hasComputedProperty($target, $property)) {
                $returnValue(static::getComputedProperty($target, $property));
            }

            if (static::hasGetterProperty($target, $property)) {
                $returnValue(static::getGetterProperty($target, $property));
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

    public static function getGetterProperties($target)
    {
        return collect(static::getGetterPropertyNames($target))
            ->mapWithKeys(function ($property) use ($target) {
                return [$property => static::getGetterProperty($target, $property)];
            })
            ->all();
    }

    public static function hasComputedProperty($target, $property)
    {
        return array_search((string) str($property)->camel(), static::getComputedPropertyNames($target)) !== false;
    }

    public static function hasGetterProperty($target, $property)
    {
        return array_search((string) str($property)->camel(), static::getGetterPropertyNames($target)) !== false;
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

    public static function getGetterProperty($target, $property)
    {
        if (! static::hasGetterProperty($target, $property)) {
            throw new \Exception('No computed property found: $'.$property);
        }

        $method = $property;

        store($target)->push(
            'getterProperties',
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

    public static function getGetterPropertyNames($target)
    {
        return collect(SyntheticUtils::getAnnotations($target))
            ->map(function ($thing, $methodName) {
                if (isset($thing['getter'])) return $methodName;
                return false;
            })
            ->filter()
            ->all();
    }
}
