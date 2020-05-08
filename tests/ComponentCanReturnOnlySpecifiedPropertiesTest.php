<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;

class ComponentCanReturnOnlySpecifiedPropertiesTest extends TestCase
{
    /** @test */
    public function a_livewire_component_can_return_an_associative_array_of_only_the_specified_properties()
    {
        Livewire::test(ComponentWithProperties::class)
            ->call('setOnlyProperties', ['foo', 'bar'])
            ->assertSet('onlyProperties', [
                'foo' => 'Foo',
                'bar' => 'Bar',
            ]);
    }
}

class ComponentWithProperties extends Component
{
    public $onlyProperties = [];

    public $foo = 'Foo';

    public $bar = 'Bar';

    public $baz = 'Baz';

    public function setOnlyProperties($properties)
    {
        $this->onlyProperties = $this->only($properties);
    }

    public function render()
    {
        return view('null-view');
    }
}
