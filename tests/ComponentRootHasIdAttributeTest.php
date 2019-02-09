<?php

namespace Tests;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Livewire\LivewireComponent;
use Livewire\LivewireManager;
use Illuminate\View\Factory;

class ComponentRootHasIdAttributeTest extends TestCase
{
    public function setUp()
    {
        ($this->livewire = app(LivewireManager::class))->register('dummy', Dummy::class);

        parent::setUp();
    }

    /** @test */
    function root_has_id_property()
    {
        $component = $this->livewire->activate('dummy');

        $this->assertEquals(0, strpos(
            trim($component->dom('some-id')),
            '<div wire:root-id="some-id"'
        ));
    }
}

class Dummy extends LivewireComponent {
    public function render()
    {
        return app('view')->make('root-id-test');
    }
}
