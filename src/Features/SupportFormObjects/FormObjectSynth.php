<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Livewire\Features\SupportAttributes\Attribute;
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
    // The form's boot runs after the property assignment so boot-time
    // logic can reach the form through its component...
    function initialize($type, $assign)
    {
        $component = $this->context->component;

        $form = new $type($component, $this->path);

        $callBootMethod = static::bootFormObject($component, $form, $this->path);

        $assign($form);

        $callBootMethod();
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

        $callBootMethod = static::bootFormObject($this->context->component, $form, $this->path);

        $this->hydrateFormProperties($form, $data, $hydrateChild);

        $callBootMethod();

        return $form;
    }

    // A form constructed outside the synth — returned from a #[Virtual]
    // property method — still boots exactly like one constructed by
    // initialize() or hydrate()...
    function adopt($form)
    {
        $callBootMethod = static::bootFormObject($this->context->component, $form, $this->path);

        $callBootMethod();
    }

    function set(&$target, $key, $value)
    {
        if ($value === null && Utils::propertyIsTyped($target, $key) && ! Utils::getProperty($target, $key)->getType()->allowsNull()) {
            unset($target->$key);
        } else {
            $target->$key = $value;
        }
    }

    // Booting is per-instance, not per-call: the same form can be presented
    // for booting more than once (a #[Virtual] method that memoizes its
    // instance, then a reset()). Doing it twice would register its
    // attributes — and so its validation rules — twice...
    protected static ?\WeakMap $booted = null;

    public static function bootFormObject($component, $form, $path)
    {
        static::$booted ??= new \WeakMap;

        if (isset(static::$booted[$form])) return fn () => null;

        static::$booted[$form] = true;

        // A reconstructed form at this path (reset/unset of a #[Virtual]
        // property) REPLACES its predecessor's attributes — accumulating
        // both sets would duplicate validation rules and leave attributes
        // bound to a discarded instance...
        $component->forgetAttributesWhere(function ($attribute) use ($form, $path) {
            return $attribute instanceof Attribute
                && ($subTarget = $attribute->getSubTarget()) instanceof Form
                && $subTarget !== $form
                && $subTarget->getPropertyName() === $path;
        });

        $component->mergeOutsideAttributes(
            AttributeCollection::fromComponent($component, $form, $path . '.')
        );

        return function () use ($form) {
            wrap($form)->boot();
        };
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
