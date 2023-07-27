<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Attributes\Form\Reset as ResetAttribute;
use Livewire\Drawer\Utils;
use Livewire\Exceptions\ResetPropertyNotAllowed;

class Reset
{
    public function __construct(public $reset = true) {}

    public static function getResettableProperties($target, ...$properties): array
    {
        $publicProperties = array_keys(Utils::getPublicProperties($target));

        $formProperties = (new \ReflectionClass($target))->getProperties();

        if (count($properties) && is_array($properties[0])) {
            $properties = $properties[0];
        }
        $values = collect($formProperties)
            ->filter(function ($property) use ($properties, $publicProperties) {
                return in_array($property->getName(), $publicProperties);
            })
            ->filter(function ($property) use ($properties) {
                $attribute = $property->getAttributes(ResetAttribute::class);

                if (empty($attribute)) {
                    return true;
                }

                if (!empty($properties) && in_array($property->getName(), $properties)) {
                    throw new ResetPropertyNotAllowed($property->getName());
                }

                return $attribute[0]->newInstance()->reset === true;
            })
            ->map(function ($property) {
                return $property->getName();
            })->values();

        return !empty($properties) ? $properties : $values->toArray();
    }
}
