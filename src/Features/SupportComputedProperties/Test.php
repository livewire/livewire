<?php

namespace Livewire\Features\SupportComputedProperties;

use Tests\TestCase;
use Livewire\Component;

class Test extends TestCase
{
    /** @test */
    function parses_computed_properties()
    {
        $this->assertEquals(
            ['foo' => 'bar', 'bar' => 'baz', 'bobLobLaw' => 'blog'],
            SupportComputedProperties::getComputedProperties(new class {
                public function getFooProperty() { return 'bar'; }
                public function getBarProperty() { return 'baz'; }
                public function getBobLobLawProperty() { return 'blog'; }
            })
        );
    }

    /** @test */
    function computed_properties_are_made_available_to_view_and_only_called_once_per_request()
    {
        $this->visit(new class extends Component {
            public $calls = 0;
            public $getterFoo;

            public function mount()
            {
                $this->getterFoo = $this->foo;
            }

            public function getFooProperty() {
                $this->calls++;

                return 'bar';
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <h1 dusk="calls">{{ $calls }}</div>
                    <h1 dusk="getter-foo">{{ $getterFoo }}</div>
                    <h1 dusk="foo">{{ $foo }}</div>
                </div>
                HTML;
            }
        }, function ($browser) {
            $browser->assertSeeIn('@calls', '1');
            $browser->assertSeeIn('@getter-foo', 'bar');
            $browser->assertSeeIn('@foo', 'bar');
        });
    }
}
