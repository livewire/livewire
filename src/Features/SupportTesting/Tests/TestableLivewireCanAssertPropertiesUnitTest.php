<?php

namespace Livewire\Features\SupportTesting\Tests;

use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanAssertPropertiesUnitTest extends \Tests\TestCase
{
    function test_can_assert_basic_property_value()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSetStrict('foo', 'bar')
            ->set('foo', 'baz')
            ->assertSetStrict('foo', 'baz');
    }

    function test_can_assert_computed_property_value()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSetStrict('bob', 'lob');
    }

    function test_swallows_property_not_found_exceptions()
    {
        Livewire::test(PropertyTestingComponent::class)
            ->assertSetStrict('nonExistentProperty', null);
    }

    function test_throws_non_property_not_found_exceptions()
    {
        $this->markTestSkipped('In V2 computed properties are "LAZY", what should we do in V3?');

        $this->expectException(\Exception::class);

        Livewire::test(ComputedPropertyWithExceptionTestingComponent::class)
            ->assertSetStrict('throwsException', null);
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
