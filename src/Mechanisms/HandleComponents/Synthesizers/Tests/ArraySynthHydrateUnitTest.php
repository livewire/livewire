<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\Synthesizers\ArraySynth;

class ArraySynthHydrateUnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_hydrate_non_array_values()
    {
        $value = "__rm__";
        $meta = ['s' => 'arr'];
        $hydrateChild = function() {};

        $componentContext = new ComponentContext(
            new class extends Component {},
            'foo',
        );

        $result = (new ArraySynth($componentContext, 'bar'))
            ->hydrate($value, $meta, $hydrateChild);

        $this->assertEquals("__rm__", $result);
    }
}
