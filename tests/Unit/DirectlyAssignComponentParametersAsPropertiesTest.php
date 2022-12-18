<?php

namespace Tests\Unit;

use Livewire\Livewire;

class DirectlyAssignComponentParametersAsPropertiesTest extends TestCase
{
    /** @test */
    public function parameters_are_directly_set_as_properties_without_mount_method()
    {
        Livewire::test(ComponentWithDirectlyAssignedProperties::class, [
            'foo' => 'bar',
            'baz' => 'bob',
        ])->assertSeeText('barbob');
    }

    /** @test */
    public function parameters_are_directly_set_as_properties_even_if_mount_method_accepts_them_too()
    {
        Livewire::test(ComponentWithDirectlyAssignedPropertiesAndMountMethod::class, [
            'foo' => 'bar',
            'baz' => 'bob',
        ])->assertSeeText('barbobbarbob');
    }
}

class ComponentWithDirectlyAssignedProperties extends \Livewire\Component
{
    public $foo;
    public $baz;

    public function render()
    {
        return app('view')->make('show-name', [
            'name' => $this->foo.$this->baz,
        ]);
    }
}

#[\AllowDynamicProperties]
class ComponentWithDirectlyAssignedPropertiesAndMountMethod extends \Livewire\Component
{
    public $foo;
    public $baz;

    public function mount($foo, $baz)
    {
        $this->fooFromMount = $foo;
        $this->bazFromMount = $baz;
    }

    public function render()
    {
        return app('view')->make('show-name', [
            'name' => $this->foo.$this->baz.$this->fooFromMount.$this->bazFromMount,
        ]);
    }
}
