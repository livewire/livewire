<?php

namespace Tests;

use Livewire\Component;
use Livewire\Livewire;

class ComputedPropertiesTest extends TestCase
{
    /** @test */
    public function compute_property_is_accessable_within_blade_view()
    {
        Livewire::test(ComputedPropertyStub::class)
            ->assertSee('foo');
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
            ->call('callForgetComputed', 'bar', 'foo')
            ->assertSee('int(11)');
    }
}

class ComputedPropertyStub extends Component
{
    public $upperCasedFoo = 'FOO';

    public function getFooProperty()
    {
        return strtolower($this->upperCasedFoo);
    }

    public function render()
    {
        return view('var-dump-foo');
    }
}

class MemoizedComputedPropertyStub extends Component
{
    public $count = 1;

    public function getFooProperty()
    {
        return $this->count += 1;
    }

    public function callForgetComputed(...$args)
    {
        $this->foo;

        $this->forgetComputed(...$args);

        $this->foo;
    }

    public function callForgetComputedArray($args)
    {
        $this->foo;

        $this->forgetComputed($args);

        $this->foo;
    }

    public function callForgetComputedSpread(...$args)
    {
        $this->foo;

        $this->forgetComputed(...$args);

        $this->foo;
    }

    public function render()
    {
        // Access foo once here to start the cache.
        $this->foo;

        return view('var-dump-foo');
    }
}
