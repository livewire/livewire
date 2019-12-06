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
    public function compute_property_is_memoized_after_its_accessed()
    {
        Livewire::test(MemoizedComputedPropertyStub::class)
            ->assertSee('int(2)');
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

    public function render()
    {
        // Access foo once here to start the cache.
        $this->foo;

        return view('var-dump-foo');
    }
}
