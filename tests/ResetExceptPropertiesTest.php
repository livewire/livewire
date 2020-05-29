<?php

namespace Tests;

use Livewire\Component;
use Livewire\Livewire;

class ResetExceptPropertiesTest extends TestCase
{
    /** @test */
    public function can_reset_except_properties()
    {
        Livewire::test(ResetExceptPropertiesComponent::class)
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->assertSet('john', 'doe')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('john', 'poe')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('john', 'poe')
            // Reset except foo and bob.
            ->call('resetExceptFooAndBob')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('john', 'doe');
    }
}

class ResetExceptPropertiesComponent extends Component
{
    public $foo  = 'bar';
    public $bob  = 'lob';
    public $john = 'doe';

    public function resetExceptFooAndBob()
    {
        $this->resetExcept(['foo', 'bob']);
    }

    public function render()
    {
        return view('null-view');
    }
}
