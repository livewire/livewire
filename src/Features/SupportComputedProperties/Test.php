<?php

namespace Livewire\Features\SupportComputedProperties;

use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

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
        $this->markTestSkipped(); // @todo: Reenable this failing test
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
    
    /** @test */
    public function computed_property_is_accessable_within_blade_view()
    {
        Livewire::test(ComputedPropertyStub::class)
            ->assertSee('foo');
    }

    /** @test */
    public function injected_computed_property_is_accessable_within_blade_view()
    {
        Livewire::test(InjectedComputedPropertyStub::class)
            ->assertSee('bar');
    }

    /** @test */
    public function computed_property_is_memoized_after_its_accessed()
    {
        Livewire::test(MemoizedComputedPropertyStub::class)
            ->assertSee('int(2)');
    }

    /** @test */
    public function isset_is_true_on_existing_computed_property()
    {
        Livewire::test(IssetComputedPropertyStub::class)
            ->assertSee('true');
    }

    /** @test */
    public function isset_is_false_on_non_existing_computed_property()
    {
        Livewire::test(FalseIssetComputedPropertyStub::class)
            ->assertSee('false');
    }
}

class ComputedPropertyStub extends Component
{
    public $upperCasedFoo = 'FOO_BAR';

    public function getFooBarProperty()
    {
        return strtolower($this->upperCasedFoo);
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            {{ var_dump($this->foo_bar) }}
        </div>
        HTML;
    }
}

class FooDependency {
    public $baz = 'bar';
}

class InjectedComputedPropertyStub extends Component
{
    public function getFooBarProperty(FooDependency $foo)
    {
        return $foo->baz;
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            {{ var_dump($this->foo_bar) }}
        </div>
        HTML;
    }
}

class MemoizedComputedPropertyStub extends Component
{
    public $count = 1;

    public function getFooProperty()
    {
        return $this->count += 1;
    }

    public function render()
    {
        // Access foo once here to start the cache.
        $this->foo;

        return <<<'HTML'
        <div>
            {{ var_dump($this->foo) }}
        </div>
        HTML;
    }
}

class IssetComputedPropertyStub extends Component{
    public $upperCasedFoo = 'FOO_BAR';

    public function getFooBarProperty()
    {
        return strtolower($this->upperCasedFoo);
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            {{ var_dump(isset($this->foo_bar)) }}
        </div>
        HTML;
    }
}

class FalseIssetComputedPropertyStub extends Component{
    public $upperCasedFoo = 'FOO_BAR';

    public function getFooBarProperty()
    {
        return strtolower($this->upperCasedFoo);
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            {{ var_dump(isset($this->foo)) }}
        </div>
        HTML;
    }
}
