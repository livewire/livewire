<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

class FloatSynthUnitTest extends \Tests\TestCase
{
    public function test_hydrated_component_with_null_value_returns_null()
    {
        Livewire::test(ComponentWithNullableFloat::class)
            ->set('floatField', null)
            ->assertSetStrict('floatField', null);
    }

    public function test_hydrated_component_with_empty_string_returns_null()
    {
        Livewire::test(ComponentWithNullableFloat::class)
            ->set('floatField', "")
            ->assertSetStrict('floatField', null);
    }

    public function test_hydrated_form_with_null_value_returns_null()
    {
        Livewire::test(FormComponentWithNullableFloat::class)
            ->set('form.floatField', null)
            ->assertSetStrict('form.floatField', null);
    }

    public function test_hydrated_form_with_empty_string_returns_null()
    {
        Livewire::test(FormComponentWithNullableFloat::class)
            ->set('form.floatField', "")
            ->assertSetStrict('form.floatField', null);
    }

    public function test_int_value_hydrated_returns_float()
    {
        Livewire::test(ComponentWithFloat::class)
            ->set('floatField', 3)
            ->assertSetStrict('floatField', 3.00);
    }

    public function test_string_value_hydrated_returns_float()
    {
        Livewire::test(ComponentWithFloat::class)
            ->set('floatField', '3')
            ->assertSetStrict('floatField', 3.00)
            ->set('floatField', '3.14')
            ->assertSetStrict('floatField', 3.14);
    }

    public function test_float_value_hydrated_returns_float()
    {
        Livewire::test(ComponentWithFloat::class)
            ->set('floatField', 3.00)
            ->assertSetStrict('floatField', 3.00)
            ->set('floatField', 3.14)
            ->assertSetStrict('floatField', 3.14);
    }

    public function test_can_hydrate_float_or_string()
    {
        Livewire::test(ComponentWithFloatOrString::class)
            ->set('floatOrStringField', 3)
            ->assertSetStrict('floatOrStringField', 3.00)
            ->set('floatOrStringField', 3.00)
            ->assertSetStrict('floatOrStringField', 3.00)
            ->set('floatOrStringField', 3.14)
            ->assertSetStrict('floatOrStringField', 3.14)
            ->set('floatOrStringField', '3')
            ->assertSetStrict('floatOrStringField', 3.00)
            ->set('floatOrStringField', '3.00')
            ->assertSetStrict('floatOrStringField', 3.00)
            ->set('floatOrStringField', '3.14')
            ->assertSetStrict('floatOrStringField', 3.14)
            ->set('floatOrStringField', 'foo')
            ->assertSetStrict('floatOrStringField', 'foo')
            ->set('floatOrStringField', null)
            ->assertSetStrict('floatOrStringField', null);
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

class ComponentWithFloat extends Component
{
    public float $floatField;

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithFloatOrString extends Component
{
    public float|string $floatOrStringField;

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
