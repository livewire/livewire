<?php

namespace Livewire\Features\SupportAttributes;

use Livewire\Component;
use Livewire\Drawer\Utils;

use function Livewire\store;

abstract class Attribute
{
    protected Component $component;

    // An attribute addresses a PATH on the component ($levelName). When the
    // path lives inside a form-typed member, $subTargetClass names that form
    // class — a static fact. The live instance is never bound at boot time;
    // anything that needs it resolves it on demand via getSubTarget()...
    protected $subTargetClass;

    protected $subName;

    protected AttributeLevel $level;

    protected $levelName;

    function __boot($component, AttributeLevel $level, $name = null, $subName = null, $subTargetClass = null)
    {
        $this->component = $component;
        $this->subName = $subName;
        $this->subTargetClass = $subTargetClass;
        $this->level = $level;
        $this->levelName = $name;
    }

    function getComponent()
    {
        return $this->component;
    }

    function getSubTargetClass()
    {
        return $this->subTargetClass;
    }

    // Resolve the live sub-target instance on demand. Reading a virtual
    // member materializes it — callers should only reach for the instance in
    // phases where it's guaranteed to exist (updates, calls, dehydration)...
    function getSubTarget()
    {
        if (! $this->subTargetClass || $this->levelName === null) return null;

        return data_get($this->component, Utils::beforeFirstDot($this->levelName));
    }

    function getSubName()
    {
        return $this->subName;
    }

    function getLevel()
    {
        return $this->level;
    }

    function getName()
    {
        return $this->levelName;
    }

    function getValue()
    {
        if ($this->level !== AttributeLevel::PROPERTY) {
            throw new \Exception('Can\'t get the value of a non-property attribute.');
        }

        // Never force an unmaterialized virtual property into being just to
        // read a value — birth timing belongs to the component's lifecycle...
        $root = Utils::beforeFirstDot($this->levelName);

        if ($this->component->hasVirtualProperty($root) && ! $this->component->virtualPropertyIsMaterialized($root)) {
            return null;
        }

        return data_get($this->component, $this->levelName);
    }

    function setValue($value, ?bool $nullable = false)
    {
        if ($this->level !== AttributeLevel::PROPERTY) {
            throw new \Exception('Can\'t set the value of a non-property attribute.');
        }

        if ($enum = $this->tryingToSetStringOrIntegerToEnum($value)) {
            if($nullable) {
                $value = $enum::tryFrom($value);
            }

            else {
                $value = $enum::from($value);
            }
        }

        // A write into a virtual property that hasn't materialized yet is
        // staged and applied the moment the property is born — so the value
        // lands identically no matter when first access happens...
        $root = Utils::beforeFirstDot($this->levelName);

        if ($this->component->hasVirtualProperty($root) && ! $this->component->virtualPropertyIsMaterialized($root)) {
            $this->component->stageVirtualPropertyWrite($this->levelName, $value);

            return;
        }

        data_set($this->component, $this->levelName, $value);
    }

    protected function tryingToSetStringOrIntegerToEnum($subject)
    {
        if (! is_string($subject) && ! is_int($subject)) return;

        $target = $this->subTargetClass ?? $this->component;

        $name = $this->subName ?? $this->levelName;

        $property = str($name)->before('.')->toString();

        $reflection = new \ReflectionProperty($target, $property);

        $type = $reflection->getType();

        // If the type is available, display its name
        if ($type instanceof \ReflectionNamedType) {
            $name = $type->getName();

            // If the type is a BackedEnum then return it's name
            if (is_subclass_of($name, \BackedEnum::class)) {
                return $name;
            }
        }

        return false;
    }

    function storeSet($key, $value)
    {
        store($this->component)->set($key, $value);
    }

    function storePush($key, $value, $iKey = null)
    {
        store($this->component)->push($key, $value, $iKey);
    }

    function storeGet($key, $default = null)
    {
        return store($this->component)->get($key, $default);
    }

    function storeFind($key, $iKey = null, $default = null)
    {
        return store($this->component)->find($key, $iKey, $default);
    }

    function storeHas($key)
    {
        return store($this->component)->has($key);
    }
}
