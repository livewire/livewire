<?php

namespace Livewire\Types;

use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;
use Livewire\LivewirePropertyType;

class BuiltInType implements LivewirePropertyType
{
    public function hydrate($instance, $request, $name, $value)
    {
        return $this->hydrateOrDehydrate($instance, $name, $value);
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        return $this->hydrateOrDehydrate($instance, $name, $value);
    }

    protected function hydrateOrDehydrate($instance, $name, $value)
    {
        if (
            is_bool($value)
                || is_null($value)
                || is_array($value)
                || is_numeric($value)
                || is_string($value)
        ) {
            return $value;
        }

        throw new PublicPropertyTypeNotAllowedException(
            $instance::getName(), $name, $value
        );
    }
}
