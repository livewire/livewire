<?php

namespace Livewire\Features\SupportFormObjects;

use ReflectionClass;
use Livewire\ComponentHook;
use ReflectionNamedType;

use function Livewire\wrap;

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

    public function update($formName, $fullPath, $newValue)
    {
        $form = $this->getProperty($formName);

        if (! $form instanceof Form) {
            return;
        }

        if (! str($fullPath)->contains('.')) {
            return;
        }

        $path = str($fullPath)->after('.')->__toString();

        $name = str($path);

        $propertyName = $name->studly()->before('.');
        $keyAfterFirstDot = $name->contains('.') ? $name->after('.')->__toString() : null;
        $keyAfterLastDot = $name->contains('.') ? $name->afterLast('.')->__toString() : null;

        $beforeMethod = 'updating'.$propertyName;
        $afterMethod = 'updated'.$propertyName;

        $beforeNestedMethod = $name->contains('.')
            ? 'updating'.$name->replace('.', '_')->studly()
            : false;

        $afterNestedMethod = $name->contains('.')
            ? 'updated'.$name->replace('.', '_')->studly()
            : false;

        $this->callFormHook($form, 'updating', [$path, $newValue]);

        $this->callFormHook($form, $beforeMethod, [$newValue, $keyAfterFirstDot]);

        $this->callFormHook($form, $beforeNestedMethod, [$newValue, $keyAfterLastDot]);

        return function () use ($form, $path, $afterMethod, $afterNestedMethod, $keyAfterFirstDot, $keyAfterLastDot, $newValue) {
            $this->callFormHook($form, 'updated', [$path, $newValue]);

            $this->callFormHook($form, $afterMethod, [$newValue, $keyAfterFirstDot]);

            $this->callFormHook($form, $afterNestedMethod, [$newValue, $keyAfterLastDot]);
        };
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

    protected function callFormHook($form, $name, $params = [])
    {
        if (method_exists($form, $name)) {
            wrap($form)->$name(...$params);
        }
    }
}
