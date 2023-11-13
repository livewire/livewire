<?php

namespace Livewire\Features\SupportFormObjects;

use ReflectionClass;
use Livewire\ComponentHook;
use ReflectionNamedType;

use function Livewire\on;

class SupportFormObjects extends ComponentHook
{
    public static function provide()
    {
        app('livewire')->propertySynthesizer(
            FormObjectSynth::class
        );
    }

    function boot()
    {
        $this->initializeFormObjects();
    }

    protected function initializeFormObjects()
    {
        foreach ((new ReflectionClass($this->component))->getProperties() as $property) {
            // Public properties only...
            if ($property->isPublic() !== true) continue;
            // Uninitialized properties only...
            if ($property->isInitialized($this->component)) continue;

            $type = $property->getType();

            if (! $type instanceof ReflectionNamedType) continue;

            $typeName = $type->getName();

            // "Form" object property types only...
            if (! is_subclass_of($typeName, Form::class)) continue;

            $form = new $typeName(
                $this->component,
                $name = $property->getName()
            );

            $callBootMethod = FormObjectSynth::bootFormObject($this->component, $form, $name);

            $property->setValue($this->component, $form);

            $callBootMethod();
        }
    }
}
