<?php

namespace Livewire\Features\SupportLockedProperties;

use Livewire\ComponentHook;
use Livewire\Features\SupportAttributes\AttributeLevel;

class SupportLockedProperties extends ComponentHook
{
    public static bool $locked = false;

    function update($propertyName, $fullPath, $newValue)
    {
        if(self::$locked === false) {
            return;
        }

        $componentIsUnlocked = $this->component
            ->getAttributes()
            ->whereInstanceOf(BaseUnlocked::class)
            ->filter(fn(BaseUnlocked $attribute) => $attribute->getLevel() === AttributeLevel::ROOT)
            ->isNotEmpty();

        if($componentIsUnlocked) {
            return;
        }

        $propertyIsUnlocked = $this->component
            ->getAttributes()
            ->whereInstanceOf(BaseUnlocked::class)
            ->filter(fn(BaseUnlocked $attribute) => $attribute->getSubName() === $propertyName && $attribute->getLevel() === AttributeLevel::PROPERTY)
            ->isNotEmpty();

        throw_unless($propertyIsUnlocked, CannotUpdateLockedPropertyException::class, $propertyName);
    }
}
