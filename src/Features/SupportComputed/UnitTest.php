<?php

namespace Livewire\Features\SupportComputed;

use Illuminate\Support\Facades\Cache;
use Tests\TestComponent;
use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Computed;

class UnitTest extends TestCase
{
    /** @test */
    function can_make_method_a_computed()
    {
        Livewire::test(new class extends TestComponent {
            #[Computed]
            function foo() {
                return 'bar';
            }
        })
            ->assertSet('foo', 'bar');
    }

    /** @test */
    function can_access_computed_properties_inside_views()
    {
        Livewire::test(new class extends TestComponent {
            #[Computed]
            function foo() {
                return 'bar';
            }

            function render() {
                return <<<'HTML'
                    <div>foo{{ $this->foo }}</div>
                HTML;
            }
        })
            ->assertSee('foobar');
    }

    /** @test */
    function computed_properties_only_get_accessed_once_per_request()
    {
        Livewire::test(new class extends TestComponent {
            public $count = 0;

            #[Computed]
            function foo() {
                $this->count++;

                return 'bar';
            }

            function render() {
                $noop = $this->foo;
                $noop = $this->foo;
                $noop = $this->foo;

                return <<<'HTML'
                    <div>foo{{ $this->foo }}</div>
                HTML;
            }
        })
            ->assertSee('foobar')
            ->assertSet('count', 1)
            ->call('$refresh')
            ->assertSet('count', 2);
    }

    /** @test */
    function can_bust_computed_cache_using_unset()
    {
        Livewire::test(new class extends TestComponent {
            public $count = 0;

            #[Computed]
            function foo() {
                $this->count++;

                return 'bar';
            }

            function render() {
                $noop = $this->foo;
                unset($this->foo);
                $noop = $this->foo;

                return <<<'HTML'
                    <div>foo{{ $this->foo }}</div>
                HTML;
            }
        })
            ->assertSee('foobar')
            ->assertSet('count', 2)
            ->call('$refresh')
            ->assertSet('count', 4);
    }

    /** @test */
    function can_tag_cached_computed_property()
    {
        // need to set a cache driver, which can handle tags
        Cache::setDefaultDriver('array');
        Livewire::test(new class extends TestComponent {
            public $count = 0;

            #[Computed(cache: true, tags: ['foo'])]
            function foo() {
                $this->count++;

                return 'bar';
            }

            function deleteCachedTags() {
                if (Cache::supportsTags()) {
                    Cache::tags(['foo'])->flush();
                }
            }

            function render() {
                $noop = $this->foo;

                return <<<'HTML'
                    <div>foo{{ $this->foo }}</div>
                HTML;
            }
        })
            ->assertSee('foobar')
            ->call('$refresh')
            ->assertSet('count', 1)
            ->call('deleteCachedTags')
            ->assertSet('count', 2);
    }

    /** @test */
    function can_tag_persisten_computed_property()
    {
        // need to set a cache driver, which can handle tags
        Cache::setDefaultDriver('array');
        Livewire::test(new class extends TestComponent {
            public $count = 0;

            #[Computed(persist: true, tags: ['foo'])]
            function foo() {
                $this->count++;

                return 'bar';
            }

            function deleteCachedTags() {
                if (Cache::supportsTags()) {
                    Cache::tags(['foo'])->flush();
                }
            }

            function render() {
                $noop = $this->foo;

                return <<<'HTML'
                    <div>foo{{ $this->foo }}</div>
                HTML;
            }
        })
            ->assertSee('foobar')
            ->call('$refresh')
            ->assertSet('count', 1)
            ->call('deleteCachedTags')
            ->assertSet('count', 2);
    }

    /** @test */
    function cant_call_a_computed_directly()
    {
        $this->expectException(CannotCallComputedDirectlyException::class);

        Livewire::test(new class extends TestComponent {
            #[Computed]
            function foo() {
                return 'bar';
            }

            function render() {
                return <<<'HTML'
                    <div>foo{{ $this->foo }}</div>
                HTML;
            }
        })
            ->assertSee('foobar')
            ->call('foo');
    }


    /** @test */
    function can_use_multiple_computed_properties_for_different_properties()
    {
        Livewire::test(new class extends TestComponent {
            public $count = 0;

            #[Computed]
            function foo() {
                $this->count++;

                return 'bar';
            }

            #[Computed]
            function bob() {
                $this->count++;

                return 'lob';
            }

            function render() {
                $noop = $this->foo;
                $noop = $this->foo;
                $noop = $this->bob;
                $noop = $this->bob;

                return <<<'HTML'
                <div>
                    <div>foo{{ $this->foo }}</div>
                    <div>bob{{ $this->bob }}</div>
                </div>
                HTML;
            }
        })
            ->assertSee('foobar')
            ->assertSee('boblob')
            ->assertSet('count', 2)
            ->call('$refresh')
            ->assertSet('count', 4);
    }

    /** @test */
    function parses_computed_properties()
    {
        $this->assertEquals(
            ['foo' => 'bar', 'bar' => 'baz', 'bobLobLaw' => 'blog'],
            SupportLegacyComputedPropertySyntax::getComputedProperties(new class {
                public function getFooProperty() { return 'bar'; }
                public function getBarProperty() { return 'baz'; }
                public function getBobLobLawProperty() { return 'blog'; }
            })
        );
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

    /** @test */
    public function isset_is_false_on_null_computed_property()
    {
        Livewire::test(NullIssetComputedPropertyStub::class)
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

class NullIssetComputedPropertyStub extends Component{
    public $upperCasedFoo = 'FOO_BAR';

    public function getFooProperty()
    {
        return null;
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
