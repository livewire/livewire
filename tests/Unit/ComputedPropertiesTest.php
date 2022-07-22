<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class ComputedPropertiesTest extends TestCase
{
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
    public function computed_property_cache_can_be_cleared()
    {
        Livewire::test(MemoizedComputedPropertyStub::class)
            ->assertSee('int(2)')
            ->call('callForgetComputed')
            ->assertSee('int(4)')
            ->call('callForgetComputed', 'foo')
            ->assertSee('int(6)')
            ->call('callForgetComputed', 'bar')
            ->assertSee('int(7)')
            ->call('callForgetComputed', ['foo', 'bar'])
            ->assertSee('int(9)')
            ->call('callForgetComputedWithTwoArgs', 'bar', 'foo')
            ->assertSee('int(11)');
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
        return view('var-dump-foo-bar');
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
        return view('var-dump-foo-bar');
    }
}

class MemoizedComputedPropertyStub extends Component
{
    public $count = 1;

    public function getFooProperty()
    {
        return $this->count += 1;
    }

    public function callForgetComputed($arg = null)
    {
        $this->foo;

        $this->forgetComputed($arg);

        $this->foo;
    }

    public function callForgetComputedWithTwoArgs($argOne, $argTwo)
    {
        $this->foo;

        $this->forgetComputed($argOne, $argTwo);

        $this->foo;
    }

    public function render()
    {
        // Access foo once here to start the cache.
        $this->foo;

        return view('var-dump-foo');
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
        return view('isset-foo-bar');
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
        return view('isset-foo');
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
        return view('isset-foo');
    }
}

