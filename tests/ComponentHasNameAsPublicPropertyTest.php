<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentHasNameAsPublicPropertyTest extends TestCase
{
    /** @test */
    public function public_name_property_is_set()
    {
        $component = app(LivewireManager::class)->test(ComponentWithNameProperty::class);

        $component->set('name', 'Caleb');

        $this->assertEquals($component->get('name'), 'Caleb');
    }

    /** @test */
    public function public_name_property_is_filled()
    {
        $component = app(LivewireManager::class)->test(ComponentWithNameProperty::class);

        $component->fill(['name' => 'Caleb']);

        $this->assertEquals($component->get('name'), 'Caleb');
    }
}

class ComponentWithNameProperty extends Component
{
    public $name;

    public function render()
    {
        return view('null-view');
    }
}
