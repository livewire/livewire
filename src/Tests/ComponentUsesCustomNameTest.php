<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class ComponentUsesCustomNameTest extends \Tests\TestCase
{
    /** @test */
    public function uses_default_component_name()
    {
        $component = Livewire::test(UsesDefaultComponentName::class);

        $this->assertEquals('Hello World', $component->get('name'));
        $this->assertNotEquals('Hello World', $component->instance()->getName());
    }

    /** @test */
    public function preserves_name_property()
    {
        $component = Livewire::test(PreservesNameProperty::class);

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
