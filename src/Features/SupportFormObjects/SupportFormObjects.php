<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\ComponentHook;

use function Livewire\wrap;

class SupportFormObjects extends ComponentHook
{
    public static function provide()
    {
        app('livewire')->propertySynthesizer(
            FormObjectSynth::class
        );
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


    protected function callFormHook($form, $name, $params = [])
    {
        if (method_exists($form, $name)) {
            wrap($form)->$name(...$params);
        }
    }
}
