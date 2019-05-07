<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class PublicPropertiesAreAvailableInTheViewTest extends TestCase
{
    /** @test */
    function public_property_is_accessible_in_view()
    {
        $component = app(LivewireManager::class)->test(PublicPropertiesInViewStub::class);

        $this->assertTrue(str_contains(
            $component->dom,
            'Caleb'
        ));
    }
}

class PublicPropertiesInViewStub extends Component {
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('show-name');
    }
}
