<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;

class ComponentCanReturnPublicPropertiesTest extends TestCase
{
    /** @test */
    public function a_livewire_component_can_return_an_associative_array_of_public_properties()
    {
        Livewire::test(ComponentWithProperties::class)
            ->call('setAllProperties')
            ->assertSet('allProperties', [
                 'onlyProperties' => [],
                 'exceptProperties' => [],
                 'allProperties' => [],
                 'foo' => 'Foo',
                 'bar' => 'Bar',
                 'baz' => 'Baz',
            ])
            ->call('setOnlyProperties', ['foo', 'bar'])
            ->assertSet('onlyProperties', [
                'foo' => 'Foo',
                'bar' => 'Bar',
            ])
            ->call('setExceptProperties', ['foo', 'onlyProperties', 'exceptProperties', 'allProperties'])
            ->assertSet('exceptProperties', [
                 'bar' => 'Bar',
                 'baz' => 'Baz',
            ]);
    }
}

class ComponentWithProperties extends Component
{
    public $onlyProperties = [];
    
    public $exceptProperties = [];
    
    public $allProperties = [];

    public $foo = 'Foo';
    
    public $bar = 'Bar';

    public $baz = 'Baz';

    public function setOnlyProperties($properties)
    {
        $this->onlyProperties = $this->only($properties);
    }
    
    public function setExceptProperties($properties)
    {
        $this->exceptProperties = $this->except($properties);
    }
    
    public function setAllProperties()
    {
        $this->allProperties = $this->all();
    }

    public function render()
    {
        return view('null-view');
    }
}
