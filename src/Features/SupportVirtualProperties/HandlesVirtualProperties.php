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

    // The raw method names behind virtual properties. These are filtered
    // out of the callable-action list so a #[Virtual] method can never be
    // invoked as an action, whatever its casing (the attribute's own
    // call() guard only catches names that survive camelCasing)...
    function getVirtualPropertyMethodNames()
    {
        return array_column(static::virtualPropertyMethods(), 'name');
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

        // Writes must land as the class the method promised — the same
        // contract a typed public property enforces on assignment. The
        // return type is always a concrete class (enforced at discovery),
        // so this instanceof check is total...
        if ($value === null ? ! $method['nullable'] : ! $value instanceof $method['type']) {
            throw new \TypeError(
                'Virtual property ['.$name.'] on component ['.static::class.'] must be an instance of ['.$method['type'].'].'
            );
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

            static::validateVirtualPropertyMethod($method);

            $name = (string) str($method->getName())->camel();

            // Two methods can't camelCase to the same property name — the
            // second would silently shadow the first based on reflection
            // order. Fail loudly instead...
            if (isset($methods[$name])) {
                throw new \LogicException(
                    'Livewire: virtual property methods ['.$methods[$name]['name'].'()] and ['.$method->getName().'()] on component ['.static::class.'] both resolve to property ['.$name.'].'
                );
            }

            $methods[$name] = [
                'name' => $method->getName(),
                'type' => $method->getReturnType()->getName(),
                'nullable' => $method->getReturnType()->allowsNull(),
            ];
        }

        return $methods;
    }

    protected static function validateVirtualPropertyMethod(\ReflectionMethod $method)
    {
        $name = (string) str($method->getName())->camel();

        if (! $method->isPublic()) {
            throw new \LogicException(
                'Livewire: virtual property method ['.$method->getName().'()] on component ['.static::class.'] must be public.'
            );
        }

        if ($method->getNumberOfRequiredParameters() > 0) {
            throw new \LogicException(
                'Livewire: virtual property method ['.$method->getName().'()] on component ['.static::class.'] can\'t declare required parameters — it\'s a property constructor, not an action.'
            );
        }

        if (property_exists(static::class, $name)) {
            throw new \LogicException(
                'Livewire: ['.$name.'] is already a declared property on component: ['.static::class.'] — a virtual property can\'t share its name.'
            );
        }

        $type = $method->getReturnType();

        if (! $type) {
            throw new VirtualPropertyMissingReturnTypeException(static::class, $method->getName());
        }

        // Virtual properties construct objects that don't serialize well
        // (they carry configuration, closures, etc.) — the return type
        // must be a concrete class/interface. This keeps the write-time
        // type guard total: a client update is always checked with a
        // plain instanceof, with no builtin/union/void gaps to slip
        // through. Scalars need no construction — use a real property...
        if (! $type instanceof \ReflectionNamedType || $type->isBuiltin() || ! (class_exists($type->getName()) || interface_exists($type->getName()))) {
            throw new \LogicException(
                'Livewire: virtual property method ['.$method->getName().'()] on component ['.static::class.'] must declare a class return type. Scalars and unions aren\'t supported — use a normal property for those.'
            );
        }
    }
}
