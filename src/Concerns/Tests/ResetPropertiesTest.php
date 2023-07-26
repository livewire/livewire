<?php

namespace Livewire\Concerns\Tests;

use Livewire\Component;
use Livewire\Livewire;

class ResetPropertiesTest extends \Tests\TestCase
{
    /** @test */
    public function can_reset_all_properties()
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
            ->call('resetAll')
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah');
    }

    /** @test */
    public function can_reset_foo_and_bob_properties()
    {
        Livewire::test(ResetPropertiesComponent::class)
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'law')
            ->assertSet('mwa', 'hah')
            ->call('resetKeys', ['foo', 'bob'])
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah');
    }

    /** @test */
    public function can_reset_only_foo_property() // not passing...
    {
        Livewire::test(ResetPropertiesComponent::class)
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah')
            ->set('foo', 'baz')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah')
            ->call('resetKeys', 'foo')
            ->assertSet('foo', 'bar')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah');
    }

    /** @test */
    public function can_reset_all_except_foo_property() // not passing...
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
            ->call('resetKeysExcept', 'foo')
            ->assertSet('foo', 'baz')
            ->assertSet('bob', 'lob')
            ->assertSet('mwa', 'hah');
    }

    /** @test */
    public function can_reset_all_except_foo_and_bob_properties() // not passing
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
