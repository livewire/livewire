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
    function it_correctly_fills_base_url_attribute_properties()
    {
        $component = Livewire::test(new class extends Component {
            use WithSorting;

            #[BaseUrl]
            public $queryFromAttribute;

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

        $queryFromAttribute = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'queryFromAttribute');
        $queryFromMethod = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'queryFromMethod');
        $queryFromTrait = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'queryFromTrait');

        $this->assertEquals('queryFromAttribute', $queryFromAttribute->getSubName());
        $this->assertEquals('queryFromMethod', $queryFromMethod->getSubName());
        $this->assertEquals('queryFromTrait', $queryFromTrait->getSubName());
    }
}
