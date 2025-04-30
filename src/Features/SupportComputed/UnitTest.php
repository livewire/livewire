<?php

namespace Livewire\Features\SupportComputed;

use Illuminate\Support\Facades\Cache;
use Tests\TestComponent;
use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Features\SupportEvents\BaseOn;

class UnitTest extends TestCase
{
    function test_can_make_method_a_computed()
    {
        Livewire::test(new class extends TestComponent {
            #[Computed]
            function foo() {
                return 'bar';
            }
        })
            ->assertSetStrict('foo', 'bar');
    }

    function test_can_access_computed_properties_inside_views()
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

    function test_computed_properties_only_get_accessed_once_per_request()
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
            ->assertSetStrict('count', 1)
            ->call('$refresh')
            ->assertSetStrict('count', 2);
    }

    function test_can_bust_computed_cache_using_unset()
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
            ->assertSetStrict('count', 2)
            ->call('$refresh')
            ->assertSetStrict('count', 4);
    }

    function test_can_tag_cached_computed_property()
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
            ->assertSetStrict('count', 1)
            ->call('deleteCachedTags')
            ->assertSetStrict('count', 2);
    }

    function test_can_tag_persisten_computed_property()
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
            ->assertSetStrict('count', 1)
            ->call('deleteCachedTags')
            ->assertSetStrict('count', 2);
    }

    function test_can_tag_persisted_computed_with_custom_key_property()
    {
        Cache::setDefaultDriver('array');

        Livewire::test(new class extends TestComponent {
            public $count = 0;

            #[Computed(persist: true, key: 'baz')]
            function foo() {
                $this->count++;

                return 'bar';
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
            ->assertSetStrict('count', 1);

        $this->assertTrue(Cache::has('baz'));
    }

    function test_cant_call_a_computed_directly()
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


    function test_can_use_multiple_computed_properties_for_different_properties()
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
            ->assertSetStrict('count', 2)
            ->call('$refresh')
            ->assertSetStrict('count', 4);
    }

    function test_parses_computed_properties()
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

    function test_computed_property_is_accessible_using_snake_case()
    {
        Livewire::test(new class extends TestComponent {
            public $upperCasedFoo = 'FOO_BAR';

            #[Computed]
            public function fooBar()
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
        })
            ->assertSee('foo_bar');
    }

    function test_computed_property_is_accessible_when_using_snake_case_or_camel_case_in_the_method_name_in_the_class()
    {
        Livewire::test(new class extends TestComponent {
            public $upperCasedFoo = 'FOO_BAR';

            #[Computed]
            public function foo_bar_snake_case_in_component_class()
            {
                return strtolower($this->upperCasedFoo);
            }

            #[Computed]
            public function fooBarCamelCaseInComponentClass()
            {
                return strtolower($this->upperCasedFoo);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <!-- Snake Case in Component Class -->
                        snake_case_in_component_class_{{ $this->foo_bar_snake_case_in_component_class }}

                        <!-- Camel Case in Blade View -->
                        camelCaseInBladeView_snake_case_method_{{ $this->fooBarCamelCaseInComponentClass }}

                        <!-- Camel Case in Component Class -->
                        camel_case_in_component_class_{{ $this->foo_bar_camel_case_in_component_class }}

                        <!-- Camel Case in Blade View -->
                        camelCaseInBladeView_camel_case_method_{{ $this->fooBarCamelCaseInComponentClass }}
                    </div>
                HTML;
            }
        })
            ->assertSeeInOrder([
                'snake_case_in_component_class_foo_bar',
                'camelCaseInBladeView_snake_case_method_foo_bar',
                'camel_case_in_component_class_foo_bar',
                'camelCaseInBladeView_camel_case_method_foo_bar'
            ]);
    }

    public function test_computed_property_is_accessable_within_blade_view()
    {
        Livewire::test(ComputedPropertyStub::class)
            ->assertSee('foo');
    }

    public function test_injected_computed_property_is_accessable_within_blade_view()
    {
        Livewire::test(InjectedComputedPropertyStub::class)
            ->assertSee('bar');
    }

    public function test_computed_property_is_memoized_after_its_accessed()
    {
        Livewire::test(MemoizedComputedPropertyStub::class)
            ->assertSee('int(2)');
    }

    public function test_isset_is_true_on_existing_computed_property()
    {
        Livewire::test(IssetComputedPropertyStub::class)
            ->assertSee('true');
    }

    public function test_isset_is_false_on_non_existing_computed_property()
    {
        Livewire::test(FalseIssetComputedPropertyStub::class)
            ->assertSee('false');
    }

    public function test_isset_is_false_on_null_computed_property()
    {
        Livewire::test(NullIssetComputedPropertyStub::class)
            ->assertSee('false');
    }

    public function test_it_supports_legacy_computed_properties()
    {
        Livewire::test(new class extends TestComponent {
            public function getFooProperty()
            {
                return 'bar';
            }
        })
            ->assertSetStrict('foo', 'bar');
    }

    public function test_it_supports_unsetting_legacy_computed_properties()
    {
        Livewire::test(new class extends TestComponent {
            public $changeFoo = false;

            public function getFooProperty()
            {
                return $this->changeFoo ? 'baz' : 'bar';
            }

            public function save()
            {
                // Access foo to ensure it is memoized.
                $this->foo;

                $this->changeFoo = true;

                unset($this->foo);
            }
        })
            ->assertSetStrict('foo', 'bar')
            ->call('save')
            ->assertSetStrict('foo', 'baz');
    }

    public function test_it_supports_unsetting_legacy_computed_properties_for_events()
    {
        Livewire::test(new class extends TestComponent {
            public $changeFoo = false;

            public function getFooProperty()
            {
                return $this->changeFoo ? 'baz' : 'bar';
            }

            #[BaseOn('bar')]
            public function onBar()
            {
                $this->changeFoo = true;

                unset($this->foo);
            }
        })
            ->assertSetStrict('foo', 'bar')
            ->dispatch('bar', 'baz')
            ->assertSetStrict('foo', 'baz');
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
