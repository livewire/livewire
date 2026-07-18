<?php

namespace Livewire\Features\SupportSelection;

use ReflectionClass;
use ReflectionNamedType;
use Livewire\ComponentHook;

class SupportSelection extends ComponentHook
{
    public static function provide()
    {
        app('livewire')->propertySynthesizer(
            SelectionSynth::class
        );
    }

    function boot()
    {
        $this->initializeSelections();
    }

    protected function initializeSelections()
    {
        foreach ((new ReflectionClass($this->component))->getProperties() as $property) {
            // Public properties only...
            if ($property->isPublic() !== true) continue;
            // Uninitialized properties only...
            if ($property->isInitialized($this->component)) continue;

            $type = $property->getType();

            if (! $type instanceof ReflectionNamedType) continue;

            $typeName = $type->getName();

            // "Selection" property types only...
            if (! is_a($typeName, Selection::class, true)) continue;

            $property->setValue($this->component, new $typeName);
        }
    }
}
