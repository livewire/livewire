<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;

class ComponentRootHasIdAndComponentDataTest extends TestCase
{
    /** @test */
    function root_element_has_id_and_component_data()
    {
        $component = app(LivewireManager::class)->test(ComponentRootHasIdAndDataStub::class);

        $this->assertTrue(str_contains(
            $component->dom,
            [$component->id, $component->data]
        ));
    }
}

class ComponentRootHasIdAndDataStub extends LivewireComponent {
    public function render()
    {
        return app('view')->make('null-view');
    }
}
