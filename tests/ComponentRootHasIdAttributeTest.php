<?php

namespace Tests;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Livewire\LivewireComponent;
use Livewire\LivewireManager;
use Illuminate\View\Factory;
use Livewire\LivewireComponentWrapper;

class ComponentRootHasIdAttributeTest extends TestCase
{
    public function setUp()
    {
        ($this->livewire = app(LivewireManager::class))->component('dummy', ComponentRootHasIdStub::class);

        parent::setUp();
    }

    /** @test */
    function root_has_id_property()
    {
        $component = LivewireComponentWrapper::wrap($this->livewire->activate('dummy'));

        $this->assertEquals(0, strpos(
            trim($component->output()),
            '<div wire:root-id="'.$component->id.'"'
        ));
    }
}

class ComponentRootHasIdStub extends LivewireComponent {
    public function render()
    {
        return app('view')->make('root-id-test');
    }
}
