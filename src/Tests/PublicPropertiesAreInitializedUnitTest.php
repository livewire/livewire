<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;
use Stringable;
use Tests\TestComponent;

class PublicPropertiesAreInitializedUnitTest extends \Tests\TestCase
{
    public function test_uninitialized_public_property_is_null()
    {
        Livewire::test(UninitializedPublicPropertyComponent::class)
            ->assertSetStrict('message', null);
    }

    public function test_initialized_public_property_shows_value()
    {
        Livewire::test(InitializedPublicPropertyComponent::class)
            ->assertSee('Non-typed Properties are boring');
    }

    public function test_modified_initialized_public_property_should_not_revert_after_subsequent_hydration()
    {
        $propertyValue = Livewire::test(InitializedPublicPropertyComponent::class)
            ->set('some_id', null)
            ->set('message', 'whatever')
            ->get('some_id')
        ;

        $this->assertEquals(null, $propertyValue);
    }

    public function test_uninitialized_public_typed_property_is_null()
    {
        Livewire::test(UninitializedPublicTypedPropertyComponent::class)
            ->assertSetStrict('message', null);
    }

    public function test_uninitialized_public_union_typed_property_is_null()
    {
        Livewire::test(UninitializedPublicUnionTypedPropertyComponent::class)
            ->assertSetStrict('message', null);
    }

    public function test_uninitialized_public_typed_property_is_still_null_after_refresh()
    {
        Livewire::test(UninitializedPublicTypedPropertyAfterRefreshComponent::class)
            ->call('$refresh')
            ->assertSetStrict('message', null);
    }

    public function test_initialized_public_typed_property_shows_value()
    {
        Livewire::test(InitializedPublicTypedPropertyComponent::class)
            ->assertSee('Typed Properties FTW!');
    }
}

class UninitializedPublicPropertyComponent extends TestComponent
{
    public $message;
}

class InitializedPublicPropertyComponent extends Component
{
    public $message = 'Non-typed Properties are boring';
    public $some_id = 3;

    public function render()
    {
        return app('view')->make('show-property-value');
    }
}

class UninitializedPublicTypedPropertyComponent extends TestComponent
{
    public string $message;
}

class UninitializedPublicUnionTypedPropertyComponent extends TestComponent
{
    public string | Stringable $message;
}

class UninitializedPublicTypedPropertyAfterRefreshComponent extends TestComponent
{
    public string $message;
}

class InitializedPublicTypedPropertyComponent extends Component
{
    public string $message = 'Typed Properties FTW!';

    public function render()
    {
        return app('view')->make('show-property-value');
    }
}
