<?php

namespace Livewire\Features\SupportPropertyFactories;

use Livewire\ComponentHook;

use function Livewire\on;
use function Livewire\store;

class SupportPropertyFactories extends ComponentHook
{
    static function provide()
    {
        // Rebuild every factory property at the start of each subsequent
        // request: the factory method supplies the configured instance,
        // then its synth hydrates the client's raw state into it...
        on('hydrate', function ($component, $memo, $context) {
            foreach (static::factoryAttributes($component) as $attribute) {
                $attribute->handleHydrate($context);
            }
        });

        on('__get', function ($target, $property, $returnValue) {
            $attribute = static::findFactoryAttribute($target, $property);

            if ($attribute) $returnValue($attribute->handleMagicGet());
        });

        // Unsetting a factory property resets it back to a freshly
        // constructed factory instance on next access...
        on('__unset', function ($target, $property) {
            $attribute = static::findFactoryAttribute($target, $property);

            if ($attribute) store($target)->unset('propertyFactories', $property);
        });
    }

    static function factoryAttributes($component)
    {
        return $component->getAttributes()->whereInstanceOf(BasePropertyFactory::class);
    }

    static function findFactoryAttribute($component, $property)
    {
        $name = (string) str($property)->camel();

        return static::factoryAttributes($component)->first(fn ($attribute) => $attribute->getName() === $name);
    }

    static function isFactoryProperty($component, $property)
    {
        return (bool) static::findFactoryAttribute($component, $property);
    }

    static function getFactoryProperties($component)
    {
        return static::factoryAttributes($component)
            ->mapWithKeys(fn ($attribute) => [$attribute->getName() => $attribute->handleMagicGet()])
            ->all();
    }

    static function setFactoryProperty($component, $property, $value)
    {
        static::findFactoryAttribute($component, $property)->setStoredValue($value);
    }

    static function forgetFactoryProperty($component, $property)
    {
        store($component)->unset('propertyFactories', (string) str($property)->camel());
    }
}
