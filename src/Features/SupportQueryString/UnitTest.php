<?php

namespace Livewire\Features\SupportQueryString;

use HttpException;
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

    function test_noexist_query_parameter_is_allowed_value()
    {
        $component = Livewire::withQueryParams(['exists' => 'noexist'])
            ->test(new class extends TestComponent {
                #[BaseUrl]
                public $exists;
                #[BaseUrl]
                public $noexists;
            });

        $attributes = $component->instance()->getAttributes();

        $existsAttribute = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'exists');
        $noexistsAttribute = $attributes->first(fn (BaseUrl $attribute) => $attribute->getName() === 'noexists');

        $this->assertEquals('noexist', $existsAttribute->getFromUrlQueryString($existsAttribute->urlName(), 'does not exist'));
        $this->assertEquals('does not exist', $noexistsAttribute->getFromUrlQueryString($noexistsAttribute->urlName(), 'does not exist'));
        $this->assertEquals('noexist', $component->instance()->exists);
        $this->assertEquals('', $component->instance()->noexists);
    }

    function test_large_numbers_are_preserved_from_query_string()
    {
        $largeNumber = '74350194073086909398128';

        $component = Livewire::withQueryParams([
            'tableSearch' => $largeNumber,
        ])->test(new class extends TestComponent {
            #[BaseUrl]
            public $tableSearch;
        });

        $this->assertSame($largeNumber, $component->instance()->tableSearch);
    }

    function test_large_numbers_in_arrays_are_preserved_from_query_string()
    {
        $largeNumber = '74350194073086909398128';

        $component = Livewire::withQueryParams([
            'filters' => ['id' => $largeNumber, 'status' => 'active'],
        ])->test(new class extends TestComponent {
            #[BaseUrl]
            public $filters = [];
        });

        $this->assertSame($largeNumber, $component->instance()->filters['id']);
        $this->assertSame('active', $component->instance()->filters['status']);
    }

    function test_scientific_notation_strings_are_preserved_from_query_string()
    {
        $component = Livewire::withQueryParams([
            'filter' => '123456e7890',
        ])->test(new class extends TestComponent {
            #[BaseUrl]
            public string $filter = '';
        });

        $this->assertSame('123456e7890', $component->instance()->filter);
    }

    function test_negative_scientific_notation_strings_are_preserved_from_query_string()
    {
        $component = Livewire::withQueryParams([
            'filter' => '-123456e7890',
        ])->test(new class extends TestComponent {
            #[BaseUrl]
            public string $filter = '';
        });

        $this->assertSame('-123456e7890', $component->instance()->filter);
    }

    function test_small_scientific_notation_values_still_decode_correctly()
    {
        $component = Livewire::withQueryParams([
            'value' => '1e2',
        ])->test(new class extends TestComponent {
            #[BaseUrl]
            public $value;
        });

        // Small scientific notation values are valid JSON numbers
        // and should decode normally (1e2 = 100.0)...
        $this->assertSame(100.0, $component->instance()->value);
    }

    function test_return_400_error_on_invalid_type_input(){
        Livewire::withQueryParams([
            'test'=>['fake']
        ])->test(new class extends TestComponent {
            #[BaseUrl]
            public string $test = '';
        })->assertStatus(400);
    }
    function test_return_error_on_invalid_type_input(){

        Livewire::withQueryParams([
            'test'=>['fake']
        ])->test(new class extends TestComponent {
            #[BaseUrl(strict: false)]
            public string $test = '';
        })->assertStatus(200)->assertHasErrors('test');

    }
    function test_return_no_error_on_nullable_typed_property(){
        Livewire::withQueryParams([
            'test'=>null
        ])->test(new class extends TestComponent {
            #[BaseUrl(strict:false)]
            public ?string $test = null;
        })->assertStatus(200)->assertHasNoErrors();
    }
    function test_it_can_handle_union_typed_property()
    {
        Livewire::withQueryParams([
            'test'=>'fake'
        ])->test(new class extends TestComponent {
            #[BaseUrl]
            public string|int $test = '';
        })->assertStatus(200)->assertHasNoErrors();
        Livewire::withQueryParams([
            'test'=>['fake']
        ])->test(new class extends TestComponent {
            #[BaseUrl]
            public string|int $test = '';
        })->assertStatus(400);
        Livewire::withQueryParams([
            'test'=>'fake'
        ])->test(new class extends TestComponent {
            #[BaseUrl(strict: false)]
            public string|int $test = '';
        })->assertStatus(200)->assertHasNoErrors();
        Livewire::withQueryParams([
            'test'=>['fake']
        ])->test(new class extends TestComponent {
            #[BaseUrl(strict: false)]
            public string|int $test = '';
        })->assertStatus(200)->assertHasErrors();
    }
}
