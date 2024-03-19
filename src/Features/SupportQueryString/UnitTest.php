<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Livewire;
use Livewire\Component;

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
    /** @test */
    function can_track_properties_in_the_url()
    {
        $component = Livewire::test(new class extends Component {
            #[BaseUrl]
            public $count = 1;

            function increment() { $this->count++; }

            public function render() {
                return '<div></div>';
            }
        });

        $this->assertTrue(isset($component->effects['url']));
    }

    /** @test */
    function sub_name_is_null_in_attributes_from_query_string_component_method()
    {
        $component = Livewire::test(new class extends Component {
            public function render()
            {
                return '<div></div>';
            }

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

    /** @test */
    function sub_name_is_null_in_attributes_from_query_string_trait_method()
    {
        $component = Livewire::test(new class extends Component {
            use WithSorting;

            public function render()
            {
                return '<div></div>';
            }
        });

        $attributes = $component->instance()->getAttributes();

        $queryFromTrait = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'queryFromTrait');

        $this->assertEquals(null, $queryFromTrait->getSubName());
    }

    /** @test */
    function sub_name_is_same_as_name_in_attributes_from_base_url_property_attribute()
    {
        $component = Livewire::test(new class extends Component {
            #[BaseUrl]
            public $queryFromAttribute;

            public function render()
            {
                return '<div></div>';
            }
        });

        $attributes = $component->instance()->getAttributes();

        $queryFromAttribute = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'queryFromAttribute');

        $this->assertEquals('queryFromAttribute', $queryFromAttribute->getSubName());
    }
}
