<?php

namespace Tests\Unit;

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
            ->assertSet('mwa', 'hah')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('mwa', 'aha')
            // Reset all.
            ->call('resetAll')
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('mwa', 'aha')
            // Reset foo and bob.
            ->call('resetKeys', ['foo', 'bob'])
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'aha')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('mwa', 'aha')
            // Reset only foo.
            ->call('resetKeys', 'foo')
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'law')
            ->assertSet('mwa', 'aha')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('mwa', 'aha')
            // Reset all except foo.
            ->call('resetKeysExcept', 'foo')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('mwa', 'aha')
            // Reset all except foo and bob.
            ->call('resetKeysExcept', ['foo', 'bob'])
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('mwa', 'hah');
    }
}

class ResetPropertiesComponent extends Component
{
    public $foo = 'bar';
    public $bob = 'lob';
    public $mwa = 'hah';

    public function resetAll()
    {
        $this->reset();
    }

    public function resetKeys($keys)
    {
        $this->reset($keys);
    }
    
    public function resetKeysExcept($keys)
    {
        $this->resetExcept($keys);
    }

    public function render()
    {
        return view('null-view');
    }
}
