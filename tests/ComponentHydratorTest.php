<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;
use Livewire\Connection\ComponentHydrator;

class ComponentHydratorTest extends TestCase
{
    /** @test */
    function re_hydrate_component()
    {
        $original = app(LivewireManager::class)->activate(ForHydration::class);

        $reHydrated = ComponentHydrator::hydrate(
            ComponentHydrator::dehydrate($original)
        );

        $this->assertNotSame($original, $reHydrated);
        $this->assertEquals($original, $reHydrated);
        $this->assertInstanceOf(ForHydration::class, $reHydrated);
    }

    /** @test */
    function changes_to_public_properties_are_preserved()
    {
        $original = app(LivewireManager::class)->activate(ForHydration::class);
        $original->foo = 'baz';

        $reHydrated = ComponentHydrator::hydrate(
            ComponentHydrator::dehydrate($original)
        );

        $this->assertEquals($reHydrated->foo, 'baz');
    }

    /** @test */
    function changes_to_protected_properties_are_not_preserved()
    {
        $original = app(LivewireManager::class)->activate(ForHydration::class);
        $original->setGoo('caz');

        $reHydrated = ComponentHydrator::hydrate(
            ComponentHydrator::dehydrate($original)
        );

        $this->assertEquals($reHydrated->getGoo(), 'car');
    }
}

class ForHydration extends LivewireComponent {
    public $foo = 'bar';
    protected $goo = 'car';

    public function getGoo()
    {
        return $this->goo;
    }

    public function setGoo($value)
    {
        $this->goo = $value;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
