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
            ->assertSee('foo_bar');
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

class MemoizedComputedPropertyStub extends Component
{
    public $count = 1;

    public function getFooBarProperty()
    {
        return $this->count += 1;
    }

    public function render()
    {
        // Access foo once here to start the cache.
        $this->foo_bar;

        return view('var-dump-foo-bar');
    }
}
