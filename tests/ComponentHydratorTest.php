<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireComponentWrapper;
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
}

class ForHydration extends LivewireComponent {
    public $foo = 'bar';

    public function render()
    {
        return app('view')->make('null-view');
    }
}
