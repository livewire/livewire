<?php

namespace Livewire\Features\SupportVirtualProperties;

// A virtual property is a public method marked #[Virtual] that acts as a
// property. The method is the property's constructor: it runs fresh each
// request, and its instance lives in a lookup here on the component —
// there is never a backing declaration. On the wire it's indistinguishable
// from a normal property...
trait HandlesVirtualProperties
{
    protected $__virtualProperties = [];

    protected static $__virtualPropertyMethods = [];

    function initializeVirtualProperties()
    {
        foreach (static::virtualPropertyMethods() as $name => $method) {
            if (! array_key_exists($name, $this->__virtualProperties)) {
                $this->__virtualProperties[$name] = $this->{$method['name']}();
            }
        }
    }

    function getVirtualProperties()
    {
        $this->initializeVirtualProperties();

        return $this->__virtualProperties;
    }

    function hasVirtualProperty($name)
    {
        return array_key_exists(
            (string) str($name)->camel(), static::virtualPropertyMethods(),
        );
    }

    function getVirtualProperty($name)
    {
        $name = (string) str($name)->camel();

        if (! array_key_exists($name, $this->__virtualProperties)) {
            $this->__virtualProperties[$name] = $this->{static::virtualPropertyMethods()[$name]['name']}();
        }

        return $this->__virtualProperties[$name];
    }

    function setVirtualProperty($name, $value)
    {
        $name = (string) str($name)->camel();

        $method = static::virtualPropertyMethods()[$name];

        // Writes must land as the type the method promised — the same
        // contract a typed public property enforces on assignment...
        if ($method['type'] && ! $method['builtin']) {
            if ($value === null ? ! $method['nullable'] : ! $value instanceof $method['type']) {
                throw new \TypeError(
                    'Virtual property ['.$name.'] on component ['.static::class.'] must be an instance of ['.$method['type'].'].'
                );
            }
        }

        $this->__virtualProperties[$name] = $value;
    }

    // Unsetting re-initializes: the method runs again and the fresh
    // instance takes the old one's place in the lookup...
    function unsetVirtualProperty($name)
    {
        $name = (string) str($name)->camel();

        $this->__virtualProperties[$name] = $this->{static::virtualPropertyMethods()[$name]['name']}();
    }

    // Apply raw wire state to the freshly constructed instance. The synth
    // mutates it in place via hydrateInto so everything the method
    // configured on it (closures included) survives the trip...
    function hydrateVirtualProperty($name, $valueOrTuple, $context)
    {
        $name = (string) str($name)->camel();

        $this->__virtualProperties[$name] = app(\Livewire\Mechanisms\HandleSynths\HandleSynths::class)->hydrateInto(
            $this->getVirtualProperty($name), $valueOrTuple, $context, $name,
        );
    }

    protected static function virtualPropertyMethods()
    {
        return static::$__virtualPropertyMethods[static::class] ??= static::discoverVirtualPropertyMethods();
    }

    protected static function discoverVirtualPropertyMethods()
    {
        $methods = [];

        foreach ((new \ReflectionClass(static::class))->getMethods() as $method) {
            if ($method->isStatic()) continue;

            if (empty($method->getAttributes(BaseVirtual::class, \ReflectionAttribute::IS_INSTANCEOF))) continue;

            $name = (string) str($method->getName())->camel();

            $type = $method->getReturnType();

            if (! $type) {
                throw new VirtualPropertyMissingReturnTypeException(static::class, $method->getName());
            }

            if (property_exists(static::class, $name)) {
                throw new \LogicException(
                    'Livewire: ['.$name.'] is already a declared property on component: ['.static::class.'] — a virtual property can\'t share its name.'
                );
            }

            $methods[$name] = [
                'name' => $method->getName(),
                'type' => $type instanceof \ReflectionNamedType ? $type->getName() : null,
                'builtin' => $type instanceof \ReflectionNamedType && $type->isBuiltin(),
                'nullable' => $type->allowsNull(),
            ];
        }

        return $methods;
    }
}
