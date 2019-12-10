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
        $component = app(LivewireManager::class)->test(PublicPropertiesInViewStub::class);

        $this->assertTrue(Str::contains(
            $component->dom,
            'Caleb'
        ));
    }

    /** @test */
    public function public_property_is_accessible_in_view_without_this_by_default()
    {
        $this->expectException(ErrorException::class);

        $component = app(LivewireManager::class)->test(PublicPropertiesInViewWithoutThisAndWithoutTraitStub::class);

        $this->assertTrue(Str::contains(
            $component->dom,
            'Caleb'
        ));
    }

    /** @test */
    public function public_property_is_accessible_in_view()
    {
        $component = app(LivewireManager::class)->test(PublicPropertiesInViewWithoutThisStub::class);

        $this->assertTrue(Str::contains(
            $component->dom,
            'Caleb'
        ));
    }
}

class PublicPropertiesInViewStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name-with-this');
    }
}

class PublicPropertiesInViewWithoutThisAndWithoutTraitStub extends Component
{
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}

class PublicPropertiesInViewWithoutThisStub extends Component
{
    use PassPublicPropertiesToView;

    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}
