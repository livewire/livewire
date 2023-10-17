<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\AttributeCollection;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class FormObjectSynth extends Synth
{
    public static $key = 'form';

    public static function match($target)
    {
        return $target instanceof Form;
    }

    public function dehydrate($target, $dehydrateChild)
    {
        $data = $target->toArray();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [$data, ['class' => get_class($target)]];
    }

    public function hydrate($data, $meta, $hydrateChild)
    {
        $form = new $meta['class']($this->context->component, $this->path);

        static::bootFormObject($this->context->component, $form, $this->path);

        foreach ($data as $key => $child) {
            if ($child === null && Utils::propertyIsTypedAndUninitialized($form, $key)) {
                continue;
            }

            $form->$key = $hydrateChild($key, $child);
        }

        return $form;
    }

    public function set(&$target, $key, $value)
    {
        if ($value === null && Utils::propertyIsTyped($target, $key)) {
            unset($target->$key);
        } else {
            $target->$key = $value;
        }
    }

    public static function bootFormObject($component, $form, $path)
    {
        $component->mergeOutsideAttributes(
            AttributeCollection::fromComponent($component, $form, $path.'.')
        );
    }
}
