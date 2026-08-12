<?php

namespace Livewire\Mechanisms\HandleSynths;

use Livewire\Mechanisms\Mechanism;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\SecurityPolicy;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Livewire\Mechanisms\HandleComponents\Synthesizers;
use Livewire\Drawer\Utils;
use ReflectionUnionType;

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

    public function hydratePropertyUpdate($valueOrTuple, $context, $path, $raw = null)
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

        return $synth->hydrate($value, $meta, function ($name, $child) use ($context, $path, $raw) {
            // Updates carry values only — the browser strips synthesizer meta out
            // of the payload — so nested values can only be reconstructed from the
            // meta in the previous, checksum-verified snapshot...
            if ($raw === null) return $child;

            // Wire data is JSON: scalars, arrays, null. Anything already a PHP
            // object was set server-side (the testing helpers do this) and is
            // in its final form...
            if (is_object($child)) return $child;

            $childPath = "{$path}.{$name}";

            // Children the snapshot has never seen — a freshly added repeater row,
            // for example — have no meta to reconstruct them from...
            if (! $childMeta = $this->getMetaForPath($raw, $childPath)) return $child;

            // The child value is untrusted update data and may itself look like a
            // synthetic tuple. Always pair it with the authenticated snapshot meta;
            // never let an update choose its own synthesizer or class...
            return $this->hydratePropertyUpdate([$child, $childMeta], $context, $childPath, $raw);
        });
    }

    public function hydrateForUpdate($raw, $path, $value, $context)
    {
        // This is a trust boundary: $value came from the update payload, while
        // $raw came from the snapshot that Checksum::verify() authenticated. Synth
        // identity and class must only ever be selected from the latter...
        $meta = $this->getMetaForPath($raw, $path);

        // If we have meta data already for this property, let's use that to get a synth...
        if ($meta) {
            return $this->hydratePropertyUpdate([$value, $meta], $context, $path, $raw);
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
