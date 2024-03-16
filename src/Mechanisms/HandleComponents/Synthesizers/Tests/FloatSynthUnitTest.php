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
    public function hydrated_component_with_null_value_returns_null()
    {
        Livewire::test(ComponentWithNullableFloat::class)
            ->set('floatField', null)
            ->assertSetStrict('floatField', null);
    }

    /**
     * @test
     */
    public function hydrated_component_with_empty_string_returns_null()
    {
        Livewire::test(ComponentWithNullableFloat::class)
            ->set('floatField', "")
            ->assertSetStrict('floatField', null);
    }

    /**
     * @test
     */
    public function hydrated_form_with_null_value_returns_null()
    {
        Livewire::test(FormComponentWithNullableFloat::class)
            ->set('form.floatField', null)
            ->assertSetStrict('form.floatField', null);
    }

    /**
     * @test
     */
    public function hydrated_form_with_empty_string_returns_null()
    {
        Livewire::test(FormComponentWithNullableFloat::class)
            ->set('form.floatField', "")
            ->assertSetStrict('form.floatField', null);
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
