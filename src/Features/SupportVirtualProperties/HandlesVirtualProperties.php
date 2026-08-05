<?php

namespace Livewire\Features\SupportVirtualProperties;

use Livewire\Drawer\Utils;
use Livewire\Features\SupportFormObjects\Form;
use Livewire\Features\SupportFormObjects\FormObjectSynth;

use function Livewire\store;

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
                $this->materializeVirtualProperty($name);
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

    function virtualPropertyIsMaterialized($name)
    {
        return array_key_exists((string) str($name)->camel(), $this->__virtualProperties);
    }

    // The raw (non-camelCased) method names, for excluding them from the
    // callable-action list...
    function getVirtualPropertyMethodNames()
    {
        return array_column(static::virtualPropertyMethods(), 'name');
    }

    // The statically-declared return type of each virtual property, keyed by
    // property name — the class-shape fact static attribute discovery and
    // form-object detection are built on...
    public static function virtualPropertyTypes()
    {
        return array_map(fn ($method) => $method['type'], static::virtualPropertyMethods());
    }

    function getVirtualProperty($name)
    {
        $name = (string) str($name)->camel();

        if (! array_key_exists($name, $this->__virtualProperties)) {
            $this->materializeVirtualProperty($name);
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

        $isNewInstance = $value !== ($this->__virtualProperties[$name] ?? null);

        $this->__virtualProperties[$name] = $value;

        // An explicitly assigned instance is a birth too (it needs the same
        // rites, e.g. a form's boot()) — but the assigner's values are theirs:
        // staged attribute writes only fill instances Livewire constructs...
        if ($isNewInstance && $value !== null) {
            $this->virtualPropertyWasBorn($name, $value, applyStagedWrites: false);
        }
    }

    // A virtual property has no empty state — unsetting reconstructs it.
    // The replacement starts from the method's own defaults: staged
    // attribute writes (e.g. #[Url] values) applied to a previous
    // incarnation don't resurrect...
    function unsetVirtualProperty($name)
    {
        $name = (string) str($name)->camel();

        $this->forgetStagedVirtualPropertyWrites($name);

        $this->materializeVirtualProperty($name);
    }

    protected function materializeVirtualProperty($name)
    {
        $method = static::virtualPropertyMethods()[$name];

        $value = $this->{$method['name']}();

        $this->__virtualProperties[$name] = $value;

        if ($value !== null) {
            $this->virtualPropertyWasBorn($name, $value, applyStagedWrites: true);
        }

        return $value;
    }

    // The single birth rite for every way a virtual instance comes into
    // being: form objects boot (idempotently) the moment they exist, and
    // writes staged while the property didn't exist yet (e.g. #[Url] query
    // string values) land on the newborn instance — so first-access timing
    // never changes the outcome...
    protected function virtualPropertyWasBorn($name, $value, $applyStagedWrites = true)
    {
        if ($value instanceof Form) {
            FormObjectSynth::formObjectBorn($this, $name, static::virtualPropertyMethods()[$name]['type'], $value);
        }

        if ($applyStagedWrites) {
            $this->applyStagedVirtualPropertyWrites($name, $value);
        }
    }

    public function stageVirtualPropertyWrite($path, $value)
    {
        store($this)->push('stagedVirtualPropertyWrites', [$path, $value], $path);
    }

    protected function applyStagedVirtualPropertyWrites($name, $value)
    {
        $staged = store($this)->get('stagedVirtualPropertyWrites', []);

        foreach ($staged as $path => [$fullPath, $stagedValue]) {
            if (Utils::beforeFirstDot($fullPath) !== $name) continue;

            // Write directly into the instance — never back through the
            // component's magic __get, which is mid-materialization here...
            data_set($value, Utils::afterFirstDot($fullPath), $stagedValue);

            store($this)->unset('stagedVirtualPropertyWrites', $path);
        }
    }

    protected function forgetStagedVirtualPropertyWrites($name)
    {
        $staged = store($this)->get('stagedVirtualPropertyWrites', []);

        foreach ($staged as $path => $write) {
            if (Utils::beforeFirstDot($path) === $name) {
                store($this)->unset('stagedVirtualPropertyWrites', $path);
            }
        }
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
