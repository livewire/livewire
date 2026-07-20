<?php

namespace Livewire\Features\SupportAutoInitializedProperties;

use Livewire\ComponentHook;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleSynths\HandleSynths;

/**
 * Typed public properties whose synthesizer knows how to initialize them
 * (an initialize() method on the synth) spring to life automatically:
 *
 *     public Selection $selection;   // no mount() assignment needed
 *
 * The property scan is reflection-backed but cached per component CLASS —
 * a page of a thousand instances pays for one reflection pass, and every
 * subsequent boot is an array lookup plus an isInitialized() check per
 * discovered property (zero entries for most components). Form objects
 * ride this same cache, replacing their old per-boot reflection scan.
 */
class SupportAutoInitializedProperties extends ComponentHook
{
    // class name -> [[ReflectionProperty, type name, synth class], ...]
    protected static array $discovered = [];

    function boot()
    {
        $component = $this->component;

        $entries = static::$discovered[$component::class] ??= static::discover($component::class);

        foreach ($entries as [$property, $typeName, $synthClass]) {
            // Only fill properties nothing else has initialized (a default,
            // a hydration, an earlier hook)...
            if ($property->isInitialized($component)) continue;

            $synth = new $synthClass(new ComponentContext($component), $property->getName());

            // The synth assigns through the callback so it controls ordering
            // around the assignment (e.g. form objects boot after)...
            $synth->initialize($typeName, fn ($value) => $property->setValue($component, $value));
        }
    }

    protected static function discover(string $class): array
    {
        $entries = [];

        foreach ((new \ReflectionClass($class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) continue;

            $type = $property->getType();

            if (! $type instanceof \ReflectionNamedType) continue;

            if ($type->isBuiltin()) continue;

            $synthClass = app(HandleSynths::class)->findSynthClassByType($type->getName());

            if ($synthClass === null || ! method_exists($synthClass, 'initialize')) continue;

            $entries[] = [$property, $type->getName(), $synthClass];
        }

        return $entries;
    }
}
