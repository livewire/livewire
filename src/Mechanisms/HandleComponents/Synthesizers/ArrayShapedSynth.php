<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

/**
 * Marker for synthesizers whose hydrate() assumes an array wire value.
 *
 * On property updates, Livewire reuses synthesizer meta from the previous
 * checksum-verified snapshot (#10489). The update payload can still change
 * shape at that path (scalar, null, or "__rm__"). Implementors are skipped
 * when the incoming value is not an array, matching the guard ArraySynth
 * has always applied.
 *
 * Implement this if your hydrate() does foreach / array construction /
 * fromLivewire() on array data. Scalar-friendly synths (Carbon, Enum, Int,
 * Float, Stringable) must not implement it.
 *
 * @see \Livewire\Mechanisms\HandleSynths\HandleSynths::hydratePropertyUpdate()
 */
interface ArrayShapedSynth
{
    //
}