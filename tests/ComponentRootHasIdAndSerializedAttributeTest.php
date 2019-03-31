<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;

class ComponentRootHasIdAndSerializedAttributeTest extends TestCase
{
    /** @test */
    function root_element_has_id_and_serialized_attributes()
    {
        $component = app(LivewireManager::class)->test(ComponentRootHasIdAndSerializedStub::class);

        $this->assertTrue(str_contains(
            $component->dom,
            [$component->id, $component->serialized]
        ));
    }
}

class ComponentRootHasIdAndSerializedStub extends LivewireComponent {
    public function render()
    {
        return app('view')->make('null-view');
    }
}
