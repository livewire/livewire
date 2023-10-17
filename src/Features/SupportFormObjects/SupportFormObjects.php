<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\ComponentHook;
use ReflectionClass;
use ReflectionNamedType;

class SupportFormObjects extends ComponentHook
{
    public static function provide()
    {
        app('livewire')->propertySynthesizer(
            FormObjectSynth::class
        );
    }

    public function boot()
    {
        $this->initializeFormObjects();
    }

    protected function initializeFormObjects()
    {
        foreach ((new ReflectionClass($this->component))->getProperties() as $property) {
            // Public properties only...
            if ($property->isPublic() !== true) {
                continue;
            }
            // Uninitialized properties only...
            if ($property->isInitialized($this->component)) {
                continue;
            }

            $type = $property->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            $typeName = $type->getName();

            // "Form" object property types only...
            if (! is_subclass_of($typeName, Form::class)) {
                continue;
            }

            $form = new $typeName(
                $this->component,
                $name = $property->getName()
            );

            FormObjectSynth::bootFormObject($this->component, $form, $name);

            $property->setValue($this->component, $form);
        }
    }
}
