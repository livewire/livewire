<?php

namespace Tests;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Livewire\LivewireComponent;
use Livewire\LivewireManager;
use Illuminate\View\Factory;
use Livewire\LivewireComponentWrapper;

class PublicPropertiesAreAvailableInTheViewTest extends TestCase
{
    public function setUp()
    {
        ($this->livewire = app(LivewireManager::class))->component('dummy', Dummy::class);

        parent::setUp();
    }

    /** @test */
    function public_property_is_accessible_in_view()
    {
        $component = LivewireComponentWrapper::wrap($this->livewire->activate('dummy'));

        $this->assertTrue(str_contains(
            $component->output(),
            'Caleb'
        ));
    }
}

class Dummy extends LivewireComponent {
    public $name = 'Caleb';

    public function render()
    {
        return app('view')->make('public-properties-test');
    }
}
