<?php

namespace Livewire\Features\SupportUnitTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanAssertPropertiesUnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_assert_basic_property_value()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('foo', 'bar')
            ->set('foo', 'baz')
            ->assertSet('foo', 'baz');
    }

    /** @test */
    public function can_assert_computed_property_value()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('bob', 'lob');
    }

    /** @test */
    public function swallows_property_not_found_exceptions()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('nonExistentProperty', null);
    }

    /** @test */
    public function throws_non_property_not_found_exceptions()
    {
        $this->markTestSkipped('In V2 computed properties are "LAZY", what should we do in V3?');

        $this->expectException(\Exception::class);

        Livewire::test(ComputedPropertyWithExceptionTestingComponent::class)
            ->assertSet('throwsException', null);
    }
}

class PropertyTestingComponent extends Component
{
    public $foo = 'bar';

    public function getBobProperty()
    {
        return 'lob';
    }

    public function render()
    {
        return '<div></div>';
    }
}

class ComputedPropertyWithExceptionTestingComponent extends Component
{
    public function getThrowsExceptionProperty()
    {
        throw new \Exception('Test exception');
    }

    public function render()
    {
        return '<div></div>';
    }
}
