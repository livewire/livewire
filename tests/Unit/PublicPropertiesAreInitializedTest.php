<?php

namespace Tests\Unit;

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
    public function modified_initialized_public_property_should_not_revert_after_subsequent_hydration()
    {
        $propertyValue = Livewire::test(InitializedPublicPropertyComponent::class)
            ->set('some_id', null)
            ->set('message', 'whatever')
            ->get('some_id')
        ;

        $this->assertEquals(null, $propertyValue);
    }

    /** @test */
    public function uninitialized_public_typed_property_is_null()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        $class = <<<'PHP'
namespace Tests\Unit;
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
    public function uninitialized_public_typed_property_is_still_null_after_refresh()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        $class = <<<'PHP'
namespace Tests\Unit;
use Livewire\Component;
class UninitializedPublicTypedPropertyAfterRefreshComponent extends Component
{
    public string $message;

    public function render()
    {
        return app('view')->make('null-view');
    }
}
PHP;
        eval($class);

        Livewire::test(UninitializedPublicTypedPropertyAfterRefreshComponent::class)
            ->call('$refresh')
            ->assertSet('message', null);
    }

    /** @test */
    public function initialized_public_typed_property_shows_value()
    {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Typed Property Initialization not supported prior to PHP 7.4');
        }

        $class = <<<'PHP'
namespace Tests\Unit;
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
    public $some_id = 3;

    public function render()
    {
        return app('view')->make('show-property-value');
    }
}
