<?php

namespace Livewire\Features\SupportComputedProperties;

use Livewire\Mechanisms\ComponentDataStore;
use Synthetic\Utils as SyntheticUtils;

class SupportComputedProperties
{
    public function boot()
    {
        app('synthetic')->on('render', function ($target, $view, $data) {
            foreach (static::getComputedProperties($target) as $property => $value) {
                isset($view[$property]) || $view->with($property, $value);
            };
        });

        app('synthetic')->on('__get', function ($target, $property, $returnValue) {
            if (static::hasComputedProperty($target, $property)) {
                $returnValue(static::getComputedProperty($target, $property));
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
        return array_search($property, static::getComputedPropertyNames($target)) !== false;
    }

    public static function getComputedProperty($target, $property)
    {
        if (! static::hasComputedProperty($target, $property)) {
            throw new \Exception('No computed property found: $'.$property);
        }

        $method = 'get'.str($property)->studly().'Property';

        ComponentDataStore::push(
            $target,
            'computedProperties',
            $value = ComponentDataStore::find($target, 'computedProperties', $property, fn () => $target->$method()),
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
            ->toArray();
    }
}
