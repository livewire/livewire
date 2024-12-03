<?php

namespace Livewire\Concerns\Tests;

use Livewire\Livewire;
use Tests\TestComponent;

class ComponentCanReturnPublicPropertiesUnitTest extends \Tests\TestCase
{
    public function test_a_livewire_component_can_return_an_associative_array_of_public_properties()
    {
        Livewire::test(ComponentWithProperties::class)
            ->call('setAllProperties')
            ->assertSetStrict('allProperties', [
                 'onlyProperties' => [],
                 'exceptProperties' => [],
                 'allProperties' => [],
                 'foo' => 'Foo',
                 'bar' => 'Bar',
                 'baz' => 'Baz',
            ])
            ->call('setOnlyProperties', ['foo', 'bar'])
            ->assertSetStrict('onlyProperties', [
                'foo' => 'Foo',
                'bar' => 'Bar',
            ])
            ->call('setExceptProperties', ['foo', 'onlyProperties', 'exceptProperties', 'allProperties'])
            ->assertSetStrict('exceptProperties', [
                 'bar' => 'Bar',
                 'baz' => 'Baz',
            ]);
    }
}

class ComponentWithProperties extends TestComponent
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
}
