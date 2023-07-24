<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Livewire;
use Livewire\Form;
use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    function can_use_a_form_object()
    {
        Livewire::test(new class extends Component {
            public PostFormStub $form;

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSet('form.title', '')
        ->assertSet('form.content', '')
        ->set('form.title', 'Some Title')
        ->set('form.content', 'Some content...')
        ->assertSet('form.title', 'Some Title')
        ->assertSet('form.content', 'Some content...')
        ;
    }

    /** @test */
    function can_validate_a_form_object()
    {
        Livewire::test(new class extends Component {
            public PostFormValidateStub $form;

            function save()
            {
                $this->form->validate();
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSet('form.title', '')
        ->assertSet('form.content', '')
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasErrors('form.content')
        ;
    }

    /** @test */
    function can_validate_a_form_object_using_rule_attributes()
    {
        Livewire::test(new class extends Component {
            public PostFormRuleAttributeStub $form;

            function save()
            {
                $this->form->validate();
            }

            function render() {
                return '<div></div>';
            }
        })
        ->assertSet('form.title', '')
        ->assertSet('form.content', '')
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasErrors('form.content')
        ->set('form.title', 'title...')
        ->set('form.content', 'content...')
        ->assertHasNoErrors()
        ->call('save')
        ;
    }

    /** @test */
    function validation_can_show_a_form_object_dynamic_validation_attributes()
    {
        Livewire::test(new class extends Component {
            public PostFormDynamicValidationAttributeStub $withDynamicAttributesForm;

            function save()
            {
                $this->withDynamicAttributesForm->validate();
            }

            public function render() { return <<<'HTML'
                <div>
                    {{ $errors }}
                </div>
            HTML; }
        })
            ->set('withDynamicAttributesForm.title', '')
            ->set('withDynamicAttributesForm.content', '')
            ->call('save')
            ->assertSee('Custom Title')
            ->assertSee('Custom Content')
        ;
    }

    /** @test */
    function validation_showing_a_form_object_dynamic_messages()
    {
        Livewire::test(new class extends Component {
            public PostFormDynamicMessageAttributeStub $form;

            function save()
            {
                $this->form->validate();
            }

            public function render() { return <<<'HTML'
                <div>{{ $errors }}</div>
            HTML; }
        })
            ->set('form.title', '')
            ->set('form.content', 'Livewire')
            ->call('save')
            ->assertSee('title is must to fill')
            ->assertSee('content need at least 10 letters')
        ;
    }

    /** @test */
    function multiple_form_objects_in_component_not_interfering_between()
    {
        Livewire::test(new class extends Component {
            public PostFormDynamicValidationAttributeStub $firstForm;
            public PostFormDynamicMessageAndValidationAttributeStub $secondForm;

            function saveFirstForm()
            {
                $this->firstForm->validate();
            }

            function saveSecondForm()
            {
                $this->secondForm->validate();
            }

            public function render() { return <<<'HTML'
                <div>{{ $errors }}</div>
            HTML; }
        })
            ->set('firstForm.title', '')
            ->set('firstForm.content', '')
            ->call('saveFirstForm')
            ->assertSee('Custom Title')
            ->assertSee('The Custom Title field is required')
            ->assertSee('Custom Content')
            ->assertSee('The Custom Content field is required')
            ->assertDontSee('Name')
            ->assertDontSee('Body')

            ->set('secondForm.title', '')
            ->set('secondForm.content', '')
            ->call('saveSecondForm')
            ->assertSee('Name')
            ->assertSee('Name is required to fill')
            ->assertSee('Body')
            ->assertSee('Body is must to fill')
            ->assertDontSee('Custom Title')
            ->assertDontSee('Custom Content')
        ;
    }
}

class PostFormStub extends Form
{
    public $title = '';

    public $content = '';
}

class PostFormValidateStub extends Form
{
    public $title = '';

    public $content = '';

    protected $rules = [
        'title' => 'required',
        'content' => 'required',
    ];
}

class PostFormRuleAttributeStub extends Form
{
    #[\Livewire\Attributes\Rule('required')]
    public $title = '';

    #[\Livewire\Attributes\Rule('required')]
    public $content = '';
}

class PostFormDynamicValidationAttributeStub extends Form
{
    #[\Livewire\Attributes\Rule('required')]
    public $title = '';

    #[\Livewire\Attributes\Rule('required')]
    public $content = '';

    public function validationAttributes() {
        return [
            'title' => 'Custom Title',
            'content' => 'Custom Content',
        ];
    }
}

class PostFormDynamicMessageAttributeStub extends Form
{
    #[\Livewire\Attributes\Rule('required')]
    public $title = '';

    #[\Livewire\Attributes\Rule(['required', 'min:10'])]
    public $content = '';

    public function messages()
    {
        return [
            'title.required' => ':attribute is must to fill',
            'content.min' => ':attribute need at least 10 letters',
        ];
    }
}

class PostFormDynamicMessageAndValidationAttributeStub extends Form
{
    #[\Livewire\Attributes\Rule('required')]
    public $title = '';

    #[\Livewire\Attributes\Rule('required')]
    public $content = '';

    public function validationAttributes() {
        return [
            'title' => 'Name',
            'content' => 'Body',
        ];
    }

    public function messages()
    {
        return [
            'title' => ':attribute is required to fill',
            'content' => ':attribute is must to fill',
        ];
    }
}
