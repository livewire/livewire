<?php

namespace Livewire\Concerns\Tests;

use Livewire\Livewire;
use Tests\TestComponent;

class ResetPropertiesUnitTest extends \Tests\TestCase
{
    public function test_can_reset_properties()
    {
        Livewire::test(ResetPropertiesComponent::class)
            ->assertSetStrict('foo', 'bar')
            ->assertSetStrict('bob', 'lob')
            ->assertSetStrict('mwa', 'hah')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('bob', 'law')
            ->assertSetStrict('mwa', 'aha')
            // Reset all.
            ->call('resetAll')
            ->assertSetStrict('foo', 'bar')
            ->assertSetStrict('bob', 'lob')
            ->assertSetStrict('mwa', 'hah')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('bob', 'law')
            ->assertSetStrict('mwa', 'aha')
            // Reset foo and bob.
            ->call('resetKeys', ['foo', 'bob'])
            ->assertSetStrict('foo', 'bar')
            ->assertSetStrict('bob', 'lob')
            ->assertSetStrict('mwa', 'aha')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('bob', 'law')
            ->assertSetStrict('mwa', 'aha')
            // Reset only foo.
            ->call('resetKeys', 'foo')
            ->assertSetStrict('foo', 'bar')
            ->assertSetStrict('bob', 'law')
            ->assertSetStrict('mwa', 'aha')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('bob', 'law')
            ->assertSetStrict('mwa', 'aha')
            // Reset all except foo.
            ->call('resetKeysExcept', 'foo')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('bob', 'lob')
            ->assertSetStrict('mwa', 'hah')
            ->set('foo', 'baz')
            ->set('bob', 'law')
            ->set('mwa', 'aha')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('bob', 'law')
            ->assertSetStrict('mwa', 'aha')
            // Reset all except foo and bob.
            ->call('resetKeysExcept', ['foo', 'bob'])
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('bob', 'law')
            ->assertSetStrict('mwa', 'hah');
    }

    public function test_can_reset_unset_properties()
    {
        $component = Livewire::test(ResetPropertiesComponent::class)
            ->set('notSet', 1)
            ->assertSetStrict('notSet', 1)
            // Reset only notSet.
            ->call('resetKeys', 'notSet');

        $this->assertFalse(isset($component->notSet));
    }

    public function test_can_reset_null_properties()
    {
        $component = Livewire::test(ResetPropertiesComponent::class)
            ->set('nullProp', 1)
            ->assertSetStrict('nullProp', 1)
            // Reset only nullProp.
            ->call('resetKeys', 'nullProp')
            ->assertSetStrict('nullProp', null);

        $this->assertTrue(is_null($component->nullProp));
    }

    public function test_can_reset_and_return_property_with_pull_method()
    {
        $component = Livewire::test(ResetPropertiesComponent::class)
            ->assertSetStrict('foo', 'bar')
            ->set('foo', 'baz')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('pullResult', null)
            ->call('proxyPull', 'foo')
            ->assertSetStrict('foo', 'bar')
            ->assertSetStrict('pullResult', 'baz');
    }

    public function test_can_pull_all_properties()
    {
        $component = Livewire::test(ResetPropertiesComponent::class)
            ->assertSetStrict('foo', 'bar')
            ->set('foo', 'baz')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('pullResult', null)
            ->call('proxyPull');

        $this->assertEquals('baz', $component->pullResult['foo']);
        $this->assertEquals('lob', $component->pullResult['bob']);
    }

    public function test_can_pull_some_properties()
    {
        $component = Livewire::test(ResetPropertiesComponent::class)
            ->assertSetStrict('foo', 'bar')
            ->set('foo', 'baz')
            ->assertSetStrict('foo', 'baz')
            ->assertSetStrict('pullResult', null)
            ->call('proxyPull', ['foo']);

        $this->assertEquals('baz', $component->pullResult['foo']);
        $this->assertFalse(array_key_exists('bob', $component->pullResult));
    }
}

class ResetPropertiesComponent extends TestComponent
{
    public $foo = 'bar';

    public $bob = 'lob';

    public $mwa = 'hah';

    public int $notSet;

    public ?int $nullProp = null;

    public $pullResult = null;

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

    public function proxyPull(...$args)
    {
        $this->pullResult = $this->pull(...$args);
    }
}
