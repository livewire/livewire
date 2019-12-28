<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentHasIdAsPublicPropertyTest extends TestCase
{
    /** @test */
    public function public_id_property_is_set()
    {
        $component = app(LivewireManager::class)->test(ComponentWithIdProperty::class);

        $this->assertNotNull($component->id());
    }
}

class ComponentWithIdProperty extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}
