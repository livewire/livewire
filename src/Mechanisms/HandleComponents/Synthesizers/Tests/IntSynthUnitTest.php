<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Livewire;

class IntSynthUnitTest extends \Tests\TestCase
{
    public function test_null_value_hydrated_returns_null()
    {
        Livewire::test(ComponentWithNullableInt::class)
            ->set('intField', null)
            ->assertSetStrict('intField', null);
    }

    public function test_int_value_hydrated_returns_int()
    {
        Livewire::test(ComponentWithInt::class)
            ->set('intField', 3)
            ->assertSetStrict('intField', 3);
    }

    public function test_string_value_hydrated_returns_int()
    {
        Livewire::test(ComponentWithInt::class)
            ->set('intField', '3')
            ->assertSetStrict('intField', 3)
            ->set('intField', '3.14')
            ->assertSetStrict('intField', 3);
    }

    public function test_float_value_hydrated_returns_int()
    {
        Livewire::test(ComponentWithInt::class)
            ->set('intField', 3.00)
            ->assertSetStrict('intField', 3)
            ->set('intField', 3.14)
            ->assertSetStrict('intField', 3);
    }

    public function test_can_hydrate_int_or_string()
    {
        Livewire::test(ComponentWithIntOrString::class)
            ->set('intOrStringField', 3)
            ->assertSetStrict('intOrStringField', 3)
            ->set('intOrStringField', 3.00)
            ->assertSetStrict('intOrStringField', 3)
            ->set('intOrStringField', 3.14)
            ->assertSetStrict('intOrStringField', 3)
            ->set('intOrStringField', '3')
            ->assertSetStrict('intOrStringField', 3)
            ->set('intOrStringField', '3.00')
            ->assertSetStrict('intOrStringField', 3)
            ->set('intOrStringField', '3.14')
            ->assertSetStrict('intOrStringField', '3.14')
            ->set('intOrStringField', 'foo')
            ->assertSetStrict('intOrStringField', 'foo')
            ->set('intOrStringField', null)
            ->assertSetStrict('intOrStringField', null);
    }
}

class ComponentWithNullableInt extends Component
{
    public ?int $intField = null;

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithInt extends Component
{
    public int $intField;

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithIntOrString extends Component
{
    public int|string $intOrStringField;

    public function render()
    {
        return view('null-view');
    }
}
