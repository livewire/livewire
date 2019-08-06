<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentUsesCustomName extends TestCase
{
    /** @test */
    public function preserves_name_property()
    {
        $component = app(LivewireManager::class)->test(PreservesNameProperty::class);

        $this->assertEquals('Hello World', $component->instance->name);
        $this->assertEquals('uses-custom-name', $component->instance->name());
        $this->assertEquals('uses-custom-name', $component->instance->getName());
    }
}

class PreservesNameProperty extends Component
{
    public $name = 'Hello World';

    public function render()
    {
        return app('view')->make('null-view');
    }

    public function getName()
    {
        return 'uses-custom-name';
    }
}
