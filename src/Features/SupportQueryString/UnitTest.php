<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Livewire;
use Livewire\Component;

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
    function can_handle_empty_querystring_value_as_empty_string()
    {
        Livewire::withQueryParams([
            'foo' => null,
        ])->test(new class extends Component {
            #[BaseUrl]
            public $foo;

            function render() { return '<div></div>'; }
        })->assertSet('foo', '');
    }

    /** @test */
    function can_handle_empty_querystring_value_as_null()
    {
        Livewire::withQueryParams([
            'foo' => null,
        ])->test(new class extends Component {
            #[BaseUrl]
            public ?string $foo;

            function render() { return '<div></div>'; }
        })->assertSet('foo', null);
    }
}
