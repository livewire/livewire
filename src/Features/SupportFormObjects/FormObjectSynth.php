<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Livewire\Features\SupportAttributes\AttributeCollection;

use function Livewire\wrap;

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
    // Adoption runs after the property assignment so boot-time logic can
    // reach the form through its component...
    function initialize($type, $assign)
    {
        $assign($form = new $type($this->context->component, $this->path));

        $this->adopt($form);
    }

    // A form built somewhere other than this synth — a #[Virtual] method
    // returning a Form — still needs its attributes registered on the
    // component and its boot() run. This is initialize() minus the
    // construction, for instances that arrive already made...
    function adopt($target, $previous = null)
    {
        $component = $this->context->component;

        // Replacing an earlier instance (an unset or a reset): its attributes
        // still point at the old form, so drop them before registering the
        // new ones. Otherwise rules and query-string effects double up...
        if ($previous !== null) $component->forgetOutsideAttributes($previous);

        $component->mergeOutsideAttributes(
            AttributeCollection::fromComponent($component, $target, $this->path . '.')
        );

        wrap($target)->boot();
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

        // The form object usually already exists by now — initialize() built
        // declared ones, and a #[Virtual] method built its own. Reuse it:
        // building a second instance would discard the booted #[Validate]
        // state and leave the first one registered but orphaned...
        $existing = data_get($this->context->component, $this->path);

        if ($existing instanceof Form && $existing instanceof $meta['class']) {
            return $this->hydrateFormProperties($existing, $data, $hydrateChild);
        }

        $form = new $meta['class']($this->context->component, $this->path);

        $this->hydrateFormProperties($form, $data, $hydrateChild);

        $this->adopt($form);

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
