<?php

namespace Tests;

use ErrorException;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\LivewireManager;
use Livewire\PassPublicPropertiesToView;

class PublicPropertiesAreAvailableInTheViewTest extends TestCase
{
    /** @test */
    public function public_property_is_accessible_in_view_via_this()
    {
        $component = app(LivewireManager::class)->test(PublicPropertiesInViewWithThisStub::class);

        $this->assertTrue(Str::contains(
            $component->payload['dom'],
            'Caleb'
        ));
    }

    /** @test */
    public function public_properties_are_accessible_in_view_without_this()
    {
        $component = app(LivewireManager::class)->test(PublicPropertiesInViewWithoutThisStub::class);
        $component->assertSee('Caleb');
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
