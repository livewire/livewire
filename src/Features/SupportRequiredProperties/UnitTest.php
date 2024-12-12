<?php

namespace Features\SupportRequiredProperties;

use Livewire\Attributes\Required;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestComponent;

class UnitTest extends TestCase
{
    public function test_unprovided_required_property_triggers_exception()
    {
        $this->expectExceptionMessageMatches(
            '/Missing required property \[foo] in component \[.*]/',
        );

        Livewire::test(new class extends TestComponent {
            #[Required]
            public string $foo;
        });
    }

    public function test_multiple_unprovided_required_properties_triggers_exception()
    {
        $this->expectExceptionMessageMatches(
            '/Missing required properties \[foo, bar] in component \[.*]/',
        );

        Livewire::test(new class extends TestComponent {
            #[Required]
            public string $foo;

            #[Required]
            public string $bar;
        });
    }

    public function test_providing_required_properties_renders_component()
    {
        Livewire::test(new class extends TestComponent {
            #[Required]
            public string $foo;

            #[Required]
            public string $bar;
        }, ['foo' => '1', 'bar' => '2'])
            ->assertOk()
            ->assertSetStrict('foo', '1')
            ->assertSetStrict('bar', '2');
    }

    public function test_provided_required_properties_with_incorrect_types_are_still_applied()
    {
        $this->expectExceptionMessage(
            'Cannot assign null to property Tests\TestComponent@anonymous::$foo of type string',
        );

        Livewire::test(new class extends TestComponent {
            #[Required]
            public string $foo;
        }, ['foo' => null]);
    }
}
