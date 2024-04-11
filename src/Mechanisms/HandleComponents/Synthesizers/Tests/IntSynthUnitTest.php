<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class IntSynthUnitTest extends \Tests\TestCase
{
    /**
     * @test
     */
    public function null_value_hydrated_returns_null()
    {
        Livewire::test(ComponentWithNullableInt::class)
            ->set('intField', null)
            ->assertSet('intField', null, true); // Use strict mode
    }

    #[Test]
    public function int_value_hydrated_returns_int()
    {
        Livewire::test(ComponentWithInt::class)
            ->set('intField', 3)
            ->assertSetStrict('intField', 3);
    }

    #[Test]
    public function string_value_hydrated_returns_int()
    {
        Livewire::test(ComponentWithInt::class)
            ->set('intField', '3')
            ->assertSetStrict('intField', 3);
    }

    #[Test]
    public function float_value_hydrated_returns_int()
    {
        Livewire::test(ComponentWithInt::class)
            ->set('intField', 3.00)
            ->assertSetStrict('intField', 3);
    }

    #[Test]
    public function can_hydrate_int_or_string()
    {
        Livewire::test(ComponentWithIntOrString::class)
            ->set('intOrStringField', 3)
            ->assertSetStrict('intOrStringField', 3)
            ->set('intOrStringField', 3.00)
            ->assertSetStrict('intOrStringField', 3)
            ->set('intOrStringField', '3')
            ->assertSetStrict('intOrStringField', '3')
            ->set('intOrStringField', '3.00')
            ->assertSetStrict('intOrStringField', '3.00')
            ->set('intOrStringField', 'foo')
            ->assertSetStrict('intOrStringField', 'foo');
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
