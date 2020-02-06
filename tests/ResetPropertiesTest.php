<?php

namespace Tests;

use Livewire\Component;
use Livewire\Livewire;

class ResetPropertiesTest extends TestCase
{
    /** @test */
    public function can_reset_properties()
    {
        Livewire::test(ResetPropertiesComponent::class)
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            // Reset all.
            ->call('resetAll')
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            // Reset only foo.
            ->call('resetKey', 'foo')
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'law');
    }
}

class ResetPropertiesComponent extends Component
{
    public $foo = 'bar';
    public $bob = 'lob';

    public function resetAll()
    {
        $this->reset();
    }

    public function resetKey($keys)
    {
        $this->reset($keys);
    }

    public function render()
    {
        return view('null-view');
    }
}
