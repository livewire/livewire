<?php

namespace Tests;

use Livewire\Component;
use Livewire\Livewire;

class PublicPropertiesAreInitializedTest extends TestCase
{
    /** @test */
    public function uninitialized_public_property_is_null()
    {
        Livewire::test(UninitializedPublicPropertyComponent::class)
            ->assertSet('message', null);
    }

    /** @test */
    public function initialized_public_property_shows_value()
    {
        Livewire::test(InitializedPublicPropertyComponent::class)
            ->assertSee('Non-typed Properties are boring');
    }
}

class UninitializedPublicPropertyComponent extends Component
{
    public $message;

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class InitializedPublicPropertyComponent extends Component
{
    public $message = 'Non-typed Properties are boring';

    public function render()
    {
        return app('view')->make('show-property-value');
    }
}
