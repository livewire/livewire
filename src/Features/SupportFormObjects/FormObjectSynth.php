<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class FormObjectSynth extends Synth {
    public static $key = 'form';

    static function match($target)
    {
        return $target instanceof Form;
    }

    static function matchByType($type)
    {
        return is_subclass_of($type, Form::class);
    }

    // A form can't be rebuilt from a raw value alone (it needs its
    // component and booted attribute state), so typed updates without
    // snapshot meta pass through untouched...
    function hydrateFromType($type, $value)
    {
        return $value;
    }

    // Uninitialized `public PostForm $form` properties spring to life.
    // The form boots after the property assignment so boot-time logic can
    // reach the form through its component...
    function initialize($type, $assign)
    {
        $form = new $type($this->context->component, $this->path);

        $assign($form);

        $form->bootIfNotBooted();
    }

    function dehydrate($target, $dehydrateChild)
    {
        $data = $target->toArray();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [$data, ['class' => get_class($target)]];
    }

    function hydrate($data, $meta, $hydrateChild)
    {
        // Verify class extends Form even though checksum protects this...
        if (! isset($meta['class']) || ! is_a($meta['class'], Form::class, true)) {
            throw new \Exception('Livewire: Invalid form object class.');
        }

        // If the form object already exists on the component (e.g. during a
        // consolidated property update where the entire form is sent as one
        // update), reuse it. Creating a new instance would discard the booted
        // #[Validate] attribute state that was set up during hydration.
        $existing = data_get($this->context->component, $this->path);

        if ($existing instanceof Form && $existing instanceof $meta['class']) {
            return $this->hydrateFormProperties($existing, $data, $hydrateChild);
        }

        $form = new $meta['class']($this->context->component, $this->path);

        $this->hydrateFormProperties($form, $data, $hydrateChild);

        $form->bootIfNotBooted();

        return $form;
    }

    function set(&$target, $key, $value)
    {
        if ($value === null && Utils::propertyIsTyped($target, $key) && ! Utils::getProperty($target, $key)->getType()->allowsNull()) {
            unset($target->$key);
        } else {
            $target->$key = $value;
        }
    }

    protected function hydrateFormProperties($form, $data, $hydrateChild)
    {
        foreach ($data as $key => $child) {
            if ($child === null && Utils::propertyIsTypedAndUninitialized($form, $key)) {
                continue;
            }

            $form->$key = $hydrateChild($key, $child);
        }

        return $form;
    }
}
