<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\Exceptions\ComponentNotFoundException;

class CanRegisterMissingComponentResolverTest extends TestCase
{
    /** @test */
    public function can_register_a_missing_component_resolver()
    {
        Livewire::component('foo', MissingResolverFoo::class);

        Livewire::test('foo')->assertSee('foo');

        // Hacky way to assert the component is missing:
        try {
            Livewire::test('bar');
        } catch (ComponentNotFoundException $e) {}

        $attempts = 0;

        Livewire::resolveMissingComponent(function ($name) use (&$attempts) {
            $attempts++;

            if ($name === 'bar') return MissingResolverBar::class;
        });

        Livewire::test('bar')->assertSee('bar');

        Livewire::test('bar')->assertSee('bar');

        // Assert that a missing component STAYS resolved...
        $this->assertEquals(1, $attempts);
    }
}

class MissingResolverFoo extends Component
{
    public function render()
    {
        return '<div>foo</div>';
    }
}

class MissingResolverBar extends Component
{
    public function render()
    {
        return '<div>bar</div>';
    }
}
