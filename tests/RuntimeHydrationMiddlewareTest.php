<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;

class RuntimeHydrationMiddlewareTest extends TestCase
{
    /** @test */
    public function a_livewire_component_can_return_an_associative_array_of_only_the_specified_properties()
    {
        Livewire::hydrateProperty(function ($value, $property) {
            if ($value === 'BAR') {
                assert('foo' === $property);
                return 'bar';
            }

            return $value;
        })->dehydrateProperty(function ($value, $property) {
            if ($value === 'bar') {
                assert('foo' === $property);
                return 'BAR';
            }

            return $value;
        });

        Livewire::test(ComponentForCustomRuntimeHydrationMiddleware::class)
            ->assertSet('foo', 'BAR')
            ->call('assertSlice')
            ->assertSet('foo', 'BAR');
    }
}

class ComponentForCustomRuntimeHydrationMiddleware extends Component
{
    public $foo = 'bar';

    public function assertSlice()
    {
        assert('bar' === $this->foo);
    }

    public function render()
    {
        return view('null-view');
    }
}
