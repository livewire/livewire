<?php

namespace Tests;

use Livewire\Component;
use Livewire\Livewire;

class PublicPropertiesAreAvailableInTheViewTest extends TestCase
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
    public function protected_static_properties_are_supported()
    {
        Livewire::test(PublicStaticPropertyOnComponent::class)
            ->call('$refresh');

        $this->assertTrue(true);
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

class ProtectedStaticPropertyOnComponent extends Component
{
    protected static $name = 'Caleb';

    public function render()
    {
        return app('view')->make('null-view');
    }
}
