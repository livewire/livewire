<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Drawer\Utils;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use ReflectionAttribute;
use ReflectionClass;

use function Livewire\store;
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
    // component), so typed updates without snapshot meta pass through
    // untouched...
    function hydrateFromType($type, $value)
    {
        return $value;
    }

    // Uninitialized `public PostForm $form` properties spring to life.
    // The form's boot runs after the property assignment so boot-time
    // logic can reach the form through its component...
    function initialize($type, $assign)
    {
        $form = new $type($this->context->component, $this->path);

        $assign($form);

        static::bootFormObject($form);
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
        // update), reuse it — it has already booted, and replacing it would
        // discard any state the instance carries...
        $existing = data_get($this->context->component, $this->path);

        if ($existing instanceof Form && $existing instanceof $meta['class']) {
            return $this->hydrateFormProperties($existing, $data, $hydrateChild);
        }

        $form = new $meta['class']($this->context->component, $this->path);

        $this->hydrateFormProperties($form, $data, $hydrateChild);

        static::bootFormObject($form);

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

    // The single birth rite for form objects: boot() runs exactly once per
    // instance per request, no matter which door the form came through
    // (declared initialization, snapshot hydration, or virtual
    // materialization). Attributes need no merging here — they're part of
    // the component's statically-derived attribute tree already...
    public static function bootFormObject($form)
    {
        if (store($form)->get('formHasBooted', false)) return;

        store($form)->set('formHasBooted', true);

        wrap($form)->boot();
    }

    // A form born from a #[Virtual] method was constructed by user code, so
    // its coordinates are verified before it's welcomed: the path it was
    // handed must be the property it lives under, and — because attributes
    // are facts about the DECLARED class shape — a subclass instance may not
    // smuggle in Livewire attributes the declared return type doesn't have...
    public static function formObjectBorn($component, $name, $declaredType, Form $form)
    {
        if ($form->getPropertyName() !== $name) {
            throw new \LogicException(
                'Livewire: form object for virtual property ['.$name.'] was constructed with property name ['.$form->getPropertyName().'] — they must match: `new '.class_basename($form).'($this, \''.$name.'\')`.'
            );
        }

        if ($form->getComponent() !== $component) {
            throw new \LogicException(
                'Livewire: form object for virtual property ['.$name.'] belongs to a different component — construct it with `new '.class_basename($form).'($this, \''.$name.'\')`.'
            );
        }

        if (get_class($form) !== $declaredType) {
            static::assertSubclassAddsNoAttributes($declaredType, get_class($form), $name);
        }

        static::bootFormObject($form);
    }

    // Cache the (declared, actual) verdict per class pair — the reflection
    // walk runs once per process...
    protected static array $subclassAttributeAudit = [];

    protected static function assertSubclassAddsNoAttributes($declaredType, $actualClass, $name)
    {
        $violation = static::$subclassAttributeAudit["$declaredType|$actualClass"]
            ??= static::findAttributeDeclaredBelow($declaredType, $actualClass);

        if ($violation === false) return;

        throw new \LogicException(
            'Livewire: the form returned for virtual property ['.$name.'] declares ['.$violation.'] on a subclass of its declared return type ['.$declaredType.']. Attributes are read from the declared type — declare the form as a named class and use it as the return type.'
        );
    }

    protected static function findAttributeDeclaredBelow($declaredType, $actualClass)
    {
        $reflected = new ReflectionClass($actualClass);

        while ($reflected && $reflected->getName() !== $declaredType) {
            foreach ($reflected->getAttributes(LivewireAttribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                return $attribute->getName();
            }

            foreach ([...$reflected->getMethods(), ...$reflected->getProperties()] as $member) {
                if ($member->getDeclaringClass()->getName() !== $reflected->getName()) continue;

                foreach ($member->getAttributes(LivewireAttribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                    return $attribute->getName();
                }
            }

            $reflected = $reflected->getParentClass();
        }

        return false;
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
