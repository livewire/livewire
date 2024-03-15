<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

class FloatSynthUnitTest extends \Tests\TestCase
{
    /**
     * @test
     */
    public function null_value_hydrated_returns_null()
    {
        Livewire::test(ComponentWithNullableFloat::class)
            ->set('floatField', null)
            ->assertSet('floatField', null, true); // Use strict mode
    }

    /**
     * @test
     */
    public function empty_string_hydrated_returns_null()
    {
        Livewire::test(ComponentWithNullableFloat::class)
            ->set('floatField', "")
            ->assertSet('floatField', null, true); // Use strict mode
    }
}

class ComponentWithNullableFloat extends Component
{
    public ?float $floatField = null;

    public function render()
    {
        return view('null-view');
    }
}
