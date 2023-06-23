<?php

namespace Livewire\Features\SupportProps;

use function Livewire\on;

use ReflectionObject;
use Livewire\ComponentHook;

class SupportProps extends ComponentHook
{
    static function provide()
    {
        // Throw a helpful error if someone is trying to pass in a component value
        // and the property on the component isn't marked with `#[Prop]`...

        on('mount', function ($component, $params) {
            $reflected = new ReflectionObject($component);

            // We only care about components missing a mount() method...
            if ($reflected->hasMethod('mount')) return;

            foreach (array_keys($params) as $key) {
                $reflected = new ReflectionObject($component);
                $property = $reflected->getProperty($key);
                $attributes = $property->getAttributes();

                $found = false;

                foreach ($attributes as $attribute) {
                    if (is_a($attribute->getName(), Prop::class, allow_string: true)) {
                        $found = true;
                    }
                }

                if (! $found) {
                    throw new MissingPropAttributeException($component, $key);
                }
            }
        });
    }
}
