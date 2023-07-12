<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanAssertPropertiesUnitTest extends \Tests\TestCase
{
    /** @test */
    function can_assert_basic_property_value()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('foo', 'bar')
            ->set('foo', 'baz')
            ->assertSet('foo', 'baz');
    }

    /** @test */
    function can_assert_computed_property_value()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('bob', 'lob');
    }

    /** @test */
    function swallows_property_not_found_exceptions()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSet('nonExistentProperty', null);
    }

    /** @test */
    function throws_non_property_not_found_exceptions()
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

    function getBobProperty()
    {
        return 'lob';
    }

    function render()
    {
        return '<div></div>';
    }
}

class ComputedPropertyWithExceptionTestingComponent extends Component
{
    function getThrowsExceptionProperty()
    {
        throw new \Exception('Test exception');
    }

    function render()
    {
        return '<div></div>';
    }
}
