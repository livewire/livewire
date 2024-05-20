<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Livewire;
use Tests\TestComponent;

trait WithSorting
{
    protected function queryStringWithSorting()
    {
        return [
            'queryFromTrait',
        ];
    }
}

class UnitTest extends \Tests\TestCase
{
    function test_can_track_properties_in_the_url()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseUrl]
            public $count = 1;

            function increment() { $this->count++; }
        });

        $this->assertTrue(isset($component->effects['url']));
    }

    function test_sub_name_is_null_in_attributes_from_query_string_component_method()
    {
        $component = Livewire::test(new class extends TestComponent {
            protected function queryString()
            {
                return [
                    'queryFromMethod',
                ];
            }
        });

        $attributes = $component->instance()->getAttributes();

        $queryFromMethod = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'queryFromMethod');

        $this->assertEquals(null, $queryFromMethod->getSubName());
    }

    function test_sub_name_is_null_in_attributes_from_query_string_trait_method()
    {
        $component = Livewire::test(new class extends TestComponent {
            use WithSorting;
        });

        $attributes = $component->instance()->getAttributes();

        $queryFromTrait = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'queryFromTrait');

        $this->assertEquals(null, $queryFromTrait->getSubName());
    }

    function test_sub_name_is_same_as_name_in_attributes_from_base_url_property_attribute()
    {
        $component = Livewire::test(new class extends TestComponent {
            #[BaseUrl]
            public $queryFromAttribute;
        });

        $attributes = $component->instance()->getAttributes();

        $queryFromAttribute = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'queryFromAttribute');

        $this->assertEquals('queryFromAttribute', $queryFromAttribute->getSubName());
    }
}
