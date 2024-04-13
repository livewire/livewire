<?php

namespace Livewire\Concerns\Tests;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class ResetPropertiesUnitTest extends \Tests\TestCase
{
    #[Test]
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

    #[Test]
    public function can_reset_unset_properties()
    {
        $component = Livewire::test(ResetPropertiesComponent::class)
            ->set('notSet', 1)
            ->assertSet('notSet', 1)
            // Reset only notSet.
            ->call('resetKeys', 'notSet');

        $this->assertFalse(isset($component->notSet));
    }

    #[Test]
    public function can_reset_null_properties()
    {
        $component = Livewire::test(ResetPropertiesComponent::class)
            ->set('nullProp', 1)
            ->assertSet('nullProp', 1)
            // Reset only nullProp.
            ->call('resetKeys', 'nullProp')
            ->assertSet('nullProp', null);

        $this->assertTrue(is_null($component->nullProp));
    }
}

class ResetPropertiesComponent extends Component
{
    public $foo = 'bar';

    public $bob = 'lob';

    public $mwa = 'hah';

    public int $notSet;

    public ?int $nullProp = null;

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
