<?php

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Livewire\Connection\ComponentHydrator;
use Livewire\Component;

class ComponentHydratorTest extends TestCase
{
    /** @test */
    function re_hydrate_component()
    {
        app('livewire')->component('for-hydration', ForHydration::class);
        $original = app('livewire')->activate('for-hydration');

        $reHydrated = ComponentHydrator::hydrate(
            'for-hydration',
            $original->id,
            ComponentHydrator::dehydrate($original),
            md5('for-hydration'.$original->id)
        );

        $this->assertNotSame($original, $reHydrated);
        $this->assertEquals($original, $reHydrated);
        $this->assertInstanceOf(ForHydration::class, $reHydrated);
    }

    /** @test */
    function changes_to_public_properties_are_preserved()
    {
        app('livewire')->component('for-hydration', ForHydration::class);
        $original = app('livewire')->activate('for-hydration');
        $original->foo = 'baz';

        $reHydrated = ComponentHydrator::hydrate(
            'for-hydration',
            $original->id,
            ComponentHydrator::dehydrate($original),
            md5('for-hydration'.$original->id)
        );

        $this->assertEquals($reHydrated->foo, 'baz');
    }

    /** @test */
    function changes_to_protected_properties_are_not_preserved()
    {
        app('livewire')->component('for-hydration', ForHydration::class);
        $original = app('livewire')->activate('for-hydration');
        $original->setGoo('caz');

        $reHydrated = ComponentHydrator::hydrate(
            'for-hydration',
            $original->id,
            ComponentHydrator::dehydrate($original),
            md5('for-hydration'.$original->id)
        );

        $this->assertEquals($reHydrated->getGoo(), 'car');
    }
}

class ForHydration extends Component {
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
