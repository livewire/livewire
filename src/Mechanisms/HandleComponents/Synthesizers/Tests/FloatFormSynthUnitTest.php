<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

class FloatFormSynthUnitTest extends \Tests\TestCase
{
    /**
     * @test
     */
    public function null_value_hydrated_returns_null()
    {
        Livewire::test(FormComponentWithNullableFloat::class)
            ->set('form.floatField', null)
            ->assertSet('form.floatField', null, true); // Use strict mode
    }

    /**
     * @test
     */
    public function empty_string_hydrated_returns_null()
    {
        Livewire::test(FormComponentWithNullableFloat::class)
            ->set('form.floatField', "")
            ->assertSet('form.floatField', null, true); // Use strict mode
    }
}

class FormComponentWithNullableFloat extends Component
{
    public FormWithNullableFloat $form;

    public function render()
    {
        return view('null-view');
    }
}

class FormWithNullableFloat extends Form
{
    public ?float $floatField = null;
}
