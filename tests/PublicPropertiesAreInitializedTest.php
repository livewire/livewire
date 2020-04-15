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

    /** @test */
    public function uninitialized_public_typed_property_is_null()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        $class = <<<'PHP'
namespace Tests;
use Livewire\Component;
class UninitializedPublicTypedPropertyComponent extends Component
{
    public string $message;

    public function render()
    {
        return app('view')->make('null-view');
    }
}
PHP;
        eval($class);

        Livewire::test(UninitializedPublicTypedPropertyComponent::class)
            ->assertSet('message', null);
    }

    /** @test */
    public function initialized_public_typed_property_shows_value()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        $class = <<<'PHP'
namespace Tests;
use Livewire\Component;
class InitializedPublicTypedPropertyComponent extends Component
{
    public string $message = 'Typed Properties FTW!';

    public function render()
    {
        return app('view')->make('show-property-value');
    }
}
PHP;
        eval($class);

        Livewire::test(InitializedPublicTypedPropertyComponent::class)
            ->assertSee('Typed Properties FTW!');
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
