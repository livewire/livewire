<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class PropertiesAreAvailableInTheViewTest extends TestCase
{
    /** @test */
    public function public_property_is_accessible_in_view_via_this()
    {
        Livewire::test(PublicPropertiesInViewWithThisStub::class)
            ->assertSee('Caleb');
    }

    /** @test */
    public function public_properties_are_accessible_in_view_without_this()
    {
        Livewire::test(PublicPropertiesInViewWithoutThisStub::class)
            ->assertSee('Caleb');
    }

    /** @test */
    public function protected_property_is_accessible_in_view_via_this()
    {
        Livewire::test(ProtectedPropertiesInViewWithThisStub::class)
            ->assertSee('Caleb');
    }

    /** @test */
    public function protected_properties_are_not_accessible_in_view_without_this()
    {
        Livewire::test(ProtectedPropertiesInViewWithoutThisStub::class)
            ->assertDontSee('Caleb');
    }
}

class PublicPropertiesInViewWithThisStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class PublicPropertiesInViewWithoutThisStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}


class ProtectedPropertiesInViewWithThisStub extends Component
{
    protected $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class ProtectedPropertiesInViewWithoutThisStub extends Component
{
    protected $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}
