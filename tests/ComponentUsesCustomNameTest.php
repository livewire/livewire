<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ComponentUsesCustomNameTest extends TestCase
{
    /** @test */
    public function uses_default_component_name()
    {
        $component = app(LivewireManager::class)->test(UsesDefaultComponentName::class);

        $this->assertEquals('Hello World', $component->get('name'));
        $this->assertEquals('tests.uses-default-component-name', $component->instance()->getName());
    }

    /** @test */
    public function preserves_name_property()
    {
        $component = app(LivewireManager::class)->test(PreservesNameProperty::class);

        $this->assertEquals('Hello World', $component->get('name'));
        $this->assertEquals('uses-custom-name', $component->instance()->getName());
    }
}

class UsesDefaultComponentName extends Component
{
    public $name = 'Hello World';

    public function render()
    {
        return app('view')->make('null-view');
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
