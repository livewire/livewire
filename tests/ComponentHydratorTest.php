<?php

namespace Tests;

use Livewire\Component;
use Livewire\ComponentChecksumManager;
use Livewire\Connection\ComponentHydrator;

class ComponentHydratorTest extends TestCase
{
    /** @test */
    public function re_hydrate_component()
    {
        app('livewire')->component('for-hydration', ForHydration::class);
        $original = app('livewire')->activate('for-hydration', 'component-id');

        $reHydrated = ComponentHydrator::hydrate(
            'for-hydration',
            $original->id,
            $data = ComponentHydrator::dehydrate($original),
            (new ComponentChecksumManager)->generate('for-hydration', $original->id, $data)
        );

        $this->assertNotSame($original, $reHydrated);
        $this->assertEquals($original, $reHydrated);
        $this->assertInstanceOf(ForHydration::class, $reHydrated);
    }

    /** @test */
    public function changes_to_public_properties_are_preserved()
    {
        app('livewire')->component('for-hydration', ForHydration::class);
        $original = app('livewire')->activate('for-hydration', 'component-id');
        $original->foo = 'baz';

        $reHydrated = ComponentHydrator::hydrate(
            'for-hydration',
            $original->id,
            $data = ComponentHydrator::dehydrate($original),
            (new ComponentChecksumManager)->generate('for-hydration', $original->id, $data)
        );

        $this->assertEquals($reHydrated->foo, 'baz');
    }

    /** @test */
    public function changes_to_protected_properties_are_not_preserved()
    {
        app('livewire')->component('for-hydration', ForHydration::class);
        $original = app('livewire')->activate('for-hydration', 'component-id');
        $original->setGoo('caz');

        $reHydrated = ComponentHydrator::hydrate(
            'for-hydration',
            $original->id,
            $data = ComponentHydrator::dehydrate($original),
            (new ComponentChecksumManager)->generate('for-hydration', $original->id, $data)
        );

        $this->assertEquals($reHydrated->getGoo(), 'caz');
    }
}

class ForHydration extends Component
{
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
