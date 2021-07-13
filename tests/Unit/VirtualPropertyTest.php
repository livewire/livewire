<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

const PROPERTY_NAME = 'virtualProperty';

class VirtualPropertyTest extends TestCase
{
    /** @test */
    public function virtual_property_is_accessible()
    {
        $component = Livewire::test(ComponentWithPublicVirtualproperty::class)
            ->assertSee('Caleb')
            ->set(PROPERTY_NAME, 'Porzio')
            ->assertSet('name', 'Porzio');

        $this->assertEquals($component->get(PROPERTY_NAME), 'Porzio');
    }
}

class ComponentWithPublicVirtualproperty extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }

    public function getPublicPropertiesDefinedBySubClass()
    {
        $data = parent::getPublicPropertiesDefinedBySubClass();
        $data[PROPERTY_NAME] = $this->name;
        return $data;
    }

    public function __get($property)
    {
        if($property == PROPERTY_NAME) return $this->name;
        return parent::__get($property);
    }

    public function __set($property, $value)
    {
        if($property == PROPERTY_NAME) {
            $this->name = $value;
            return;
        }
        parent::__set($property, $value);
    }

    public function propertyIsPublicAndNotDefinedOnBaseClass($propertyName)
    {
        if($propertyName == PROPERTY_NAME) return true;
        return parent::propertyIsPublicAndNotDefinedOnBaseClass($propertyName);
    }
}
