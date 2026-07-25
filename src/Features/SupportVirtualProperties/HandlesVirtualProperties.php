<?php

namespace Livewire\Features\SupportVirtualProperties;

// A virtual property is a public method marked #[Virtual] that acts as a
// property. The method is its constructor — it materializes on first access
// (like #[Computed]) into a lookup on the component; there's no backing
// declaration. On the wire it's indistinguishable from a normal property...
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

    // The raw (non-camelCased) method names, for excluding them from the
    // callable-action list...
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

        // Writes must land as the class the method declared, the same way a
        // typed public property enforces its type on assignment. Discovery
        // guarantees a concrete class return type, so a plain instanceof
        // covers every case — no builtin/union gaps to handle...
        if ($value === null ? ! $method['nullable'] : ! $value instanceof $method['type']) {
            throw new \TypeError(
                'Virtual property ['.$name.'] on component ['.static::class.'] must be an instance of ['.$method['type'].'].'
            );
        }

        $this->__virtualProperties[$name] = $value;
    }

    // A virtual property has no empty state — unsetting reconstructs it...
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

        // Virtual properties exist to construct rich objects (carrying config,
        // closures, etc.); scalars need no construction and should be normal
        // properties. Requiring a concrete class also keeps the write-time
        // type guard a total instanceof — no builtin/union/void edge cases...
        if (! $type instanceof \ReflectionNamedType || $type->isBuiltin() || ! (class_exists($type->getName()) || interface_exists($type->getName()))) {
            throw new \LogicException(
                'Livewire: virtual property method ['.$method->getName().'()] on component ['.static::class.'] must declare a class return type. Scalars and unions aren\'t supported — use a normal property for those.'
            );
        }
    }
}
