<?php

namespace Livewire\Mechanisms\HandleSynths;

use Livewire\Mechanisms\Mechanism;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\SecurityPolicy;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Livewire\Mechanisms\HandleComponents\Synthesizers;
use Livewire\Drawer\Utils;
use ReflectionUnionType;

use function Livewire\on;

class HandleSynths extends Mechanism
{
    protected array $synthesizers = [
        Synthesizers\CarbonSynth::class,
        Synthesizers\CollectionSynth::class,
        Synthesizers\StringableSynth::class,
        Synthesizers\EnumSynth::class,
        Synthesizers\StdClassSynth::class,
        Synthesizers\ArraySynth::class,
        Synthesizers\IntSynth::class,
        Synthesizers\FloatSynth::class,
    ];

    // Performance optimization: Cache which synthesizer matches which type
    protected array $typeCache = [];

    public function registerSynth($synth)
    {
        foreach ((array) $synth as $class) {
            array_unshift($this->synthesizers, $class);
        }
    }

    public function dehydrate($target, $context, $path)
    {
        if (Utils::isAPrimitive($target)) {
            // Normalize negative zero (-0.0) to 0 to prevent checksum mismatches
            if ($target === -0.0) return 0;

            return $target;
        }

        $synth = $this->resolve($target, $context, $path);

        [ $data, $meta ] = $synth->dehydrate($target, function ($name, $child) use ($context, $path) {
            return $this->dehydrate($child, $context, "{$path}.{$name}");
        });

        $meta['s'] = $synth::getKey();

        return [ $data, $meta ];
    }

    public function hydrate($valueOrTuple, $context, $path)
    {
        if (! Utils::isSyntheticTuple($value = $tuple = $valueOrTuple)) return $value;

        [$value, $meta] = $tuple;

        // Nested properties get set as `__rm__` when they are removed. We don't want to hydrate these.
        if ($this->isRemoval($value) && str($path)->contains('.')) {
            return $value;
        }

        // Validate class against denylist before any synthesizer can instantiate it...
        if (isset($meta['class'])) {
            SecurityPolicy::validateClass($meta['class']);
        }

        $synth = $this->resolve($meta['s'], $context, $path);

        return $synth->hydrate($value, $meta, function ($name, $child) use ($context, $path) {
            return $this->hydrate($child, $context, "{$path}.{$name}");
        });
    }

    public function hydratePropertyUpdate($valueOrTuple, $context, $path)
    {
        if (! Utils::isSyntheticTuple($value = $tuple = $valueOrTuple)) return $value;

        [$value, $meta] = $tuple;

        // Nested properties get set as `__rm__` when they are removed. We don't want to hydrate these.
        if ($this->isRemoval($value) && str($path)->contains('.')) {
            return $value;
        }

        // Validate class against denylist before any synthesizer can instantiate it...
        if (isset($meta['class'])) {
            SecurityPolicy::validateClass($meta['class']);
        }

        $synth = $this->resolve($meta['s'], $context, $path);

        return $synth->hydrate($value, $meta, function ($name, $child) {
            return $child;
        });
    }

    // Hydrate raw wire data INTO an existing instance instead of building
    // a fresh one from class + meta. Synths opt in by defining
    // hydrateInto($target, $value, $meta), which mutates the instance in
    // place — anything it carries that doesn't serialize (closures,
    // configuration) survives the trip. Synths without it fall back to a
    // plain hydrate and the instance gets replaced...
    public function hydrateInto($instance, $valueOrTuple, $context, $path)
    {
        if (! Utils::isSyntheticTuple($tuple = $valueOrTuple)) return $valueOrTuple;

        [$value, $meta] = $tuple;

        if (isset($meta['class'])) {
            SecurityPolicy::validateClass($meta['class']);
        }

        $synth = $this->resolve($meta['s'], $context, $path);

        if (method_exists($synth, 'hydrateInto')) {
            $synth->hydrateInto($instance, $value, $meta);

            return $instance;
        }

        return $synth->hydrate($value, $meta, function ($name, $child) use ($context, $path) {
            return $this->hydrate($child, $context, "{$path}.{$name}");
        });
    }

    // A virtual property's instance comes from the user's method, so the synth
    // never gets asked to build it and never runs the wire-up initialize()
    // would have done. Offer the instance to its synth after the fact —
    // synths opt in by defining adopt($target, $previous)...
    public function adopt($component, $path, $instance, $previous = null)
    {
        if (! is_object($instance)) return;

        $context = new ComponentContext($component);

        // An unsupported type throws at dehydration with a better message than
        // we could give here, so stay quiet and let it get that far...
        try {
            $synth = $this->resolve($instance, $context, $path);
        } catch (\Exception) {
            return;
        }

        if (! method_exists($synth, 'adopt')) return;

        $synth->adopt($instance, $previous);
    }

    public function hydrateForUpdate($raw, $path, $value, $context)
    {
        $meta = $this->getMetaForPath($raw, $path);

        // If we have meta data already for this property, let's use that to get a synth...
        if ($meta) {
            // A root update to a VIRTUAL property applies onto a CLONE of its
            // method-built instance: the clone keeps whatever the method
            // configured (closures included), while the original stays put so
            // update/updating hooks still see the old value during
            // trigger('update') — same semantics as declared/nested updates...
            if (! str($path)->contains('.')
                && $context->component->hasVirtualProperty($path)
                && is_object($target = $context->component->getVirtualProperty($path))
                && method_exists($synth = $this->resolve($meta['s'], $context, $path), 'hydrateInto')
                && $synth::match($target)
            ) {
                $clone = clone $target;

                $synth->hydrateInto($clone, $value, $meta);

                return $clone;
            }

            return $this->hydratePropertyUpdate([$value, $meta], $context, $path);
        }

        // If we don't, let's check to see if it's a typed property and fetch the synth that way...
        $parent = str($path)->contains('.')
            ? data_get($context->component, str($path)->beforeLast('.')->toString())
            : $context->component;

        $childKey = str($path)->afterLast('.');

        if ($parent && is_object($parent) && property_exists($parent, $childKey) && Utils::propertyIsTyped($parent, $childKey)) {
            $type = Utils::getProperty($parent, $childKey)->getType();

            $types = $type instanceof ReflectionUnionType ? $type->getTypes() : [$type];

            foreach ($types as $type) {
                $synth = $this->findByType($type->getName(), $context, $path);

                if ($synth) return $synth->hydrateFromType($type->getName(), $value);
            }
        }

        return $value;
    }

    public function find($keyOrTarget, $component): ?Synth
    {
        $context = new ComponentContext($component);
        try {
            return $this->resolve($keyOrTarget, $context, null);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function resolve($keyOrTarget, $context, $path): Synth
    {
        return is_string($keyOrTarget)
            ? $this->findByKey($keyOrTarget, $context, $path)
            : $this->findByTarget($keyOrTarget, $context, $path);
    }

    public function isRemoval($value)
    {
        return $value === '__rm__';
    }

    protected function findByKey($key, $context, $path)
    {
        foreach ($this->synthesizers as $synth) {
            if ($synth::getKey() === $key) {
                return new $synth($context, $path);
            }
        }

        throw new \Exception('No synthesizer found for key: "'.$key.'"');
    }

    protected function findByTarget($target, $context, $path)
    {
        // Performance optimization: Cache synthesizer matches by runtime type...
        $type = get_debug_type($target);

        if (! isset($this->typeCache[$type])) {
            foreach ($this->synthesizers as $synth) {
                if ($synth::match($target)) {
                    $this->typeCache[$type] = $synth;

                    return new $synth($context, $path);
                }
            }

            throw new \Exception('Property type not supported in Livewire for property: ['.json_encode($target).']');
        }

        return new ($this->typeCache[$type])($context, $path);
    }

    // Typed public properties whose synthesizer defines initialize() are
    // filled automatically at boot. Discovery is cached per component
    // class: one reflection pass per class per process, then each boot
    // pays an array lookup plus an isInitialized() check per entry...
    protected static array $initializable = [];

    public function boot()
    {
        on('flush-state', function () {
            static::$initializable = [];
        });
    }

    public function initializeProperties($component)
    {
        $entries = static::$initializable[$component::class] ??= $this->discoverInitializableProperties($component::class);

        foreach ($entries as [$property, $typeName, $synthClass]) {
            // Only fill properties nothing else has initialized (a default,
            // a hydration, an earlier hook)...
            if ($property->isInitialized($component)) continue;

            $synth = new $synthClass(new ComponentContext($component), $property->getName());

            // The synth assigns through the callback so it controls ordering
            // around the assignment (e.g. form objects boot afterwards)...
            $synth->initialize($typeName, fn ($value) => $property->setValue($component, $value));
        }

        // Virtual properties are deliberately NOT constructed here. They
        // materialize on first access (like #[Computed]) so a method can read
        // sibling state set in mount(). SupportVirtualProperties then forces
        // any that nothing touched, at the end of the mount/hydrate phase —
        // late enough for that, early enough for a form object's attributes
        // to still catch the lifecycle...
    }

    protected function discoverInitializableProperties(string $class): array
    {
        $entries = [];

        foreach ((new \ReflectionClass($class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) continue;

            $type = $property->getType();

            if (! $type instanceof \ReflectionNamedType) continue;

            if ($type->isBuiltin()) continue;

            foreach ($this->synthesizers as $synthClass) {
                if (! $synthClass::matchByType($type->getName())) continue;

                if (method_exists($synthClass, 'initialize')) {
                    $entries[] = [$property, $type->getName(), $synthClass];
                }

                break;
            }
        }

        return $entries;
    }

    protected function findByType($type, $context, $path)
    {
        foreach ($this->synthesizers as $synth) {
            if ($synth::matchByType($type)) {
                return new $synth($context, $path);
            }
        }

        return null;
    }

    protected function getMetaForPath($raw, $path)
    {
        $segments = explode('.', $path);

        $first = array_shift($segments);

        [$data, $meta] = Utils::isSyntheticTuple($raw) ? $raw : [$raw, null];

        if ($path !== '') {
            $value = $data[$first] ?? null;

            return $this->getMetaForPath($value, implode('.', $segments));
        }

        return $meta;
    }
}
