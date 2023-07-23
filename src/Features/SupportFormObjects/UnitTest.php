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
    function can_validate_a_form_object_with_dynamic_validation_attribute_method()
    {
        Livewire::test(new class extends Component {
            public PostFormDynamicValidationAttributeStub $form;

            function save()
            {
                $this->form->validate();
            }

            public function render() { return <<<'HTML'
                <div>{{ $errors }}</div>
            HTML; }
        })
            ->call('save')
            ->assertSee('Title')
            ->assertSee('Content')
        ;
    }

    /** @test */
    function can_validate_a_form_object_with_dynamic_messages_method()
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
            ->set('form.content', '')
            ->call('save')
            ->assertSee('Field title is must')
            ->assertSee('This field is required in this form')
            ->set('form.content', 'Hello')
            ->assertSee('Need at least 10 letters')
        ;
    }

    /** @test */
    function can_validate_multiple_form_object_without_interfering_between()
    {
        Livewire::test(new class extends Component {
            public PostFormDynamicMessageAttributeStub $withMessagesForm;
            public PostFormDynamicValidationAttributeStub $withValidationAttributesForm;

            function saveWithMessages()
            {
                $this->withMessagesForm->validate();
            }

            function saveWithValidationAttributes()
            {
                $this->withValidationAttributesForm->validate();
            }

            public function render() { return <<<'HTML'
                <div>{{ $errors }}</div>
            HTML; }
        })
            ->call('saveWithMessages')
            ->assertSee('title')
            ->assertSee('Field title is must')
            ->assertSee('content')
            ->assertSee('This field is required in this form')
            ->call('saveWithValidationAttributes')
            ->assertSee('Title')
            ->assertSee('The Title field is required')
            ->assertSee('Content')
            ->assertSee('The Content field is required')
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
    #[\Livewire\Attributes\Rule('required', 'title')]
    public $title = '';

    #[\Livewire\Attributes\Rule('required', 'content')]
    public $content = '';

    public function validationAttributes() {
        return [
            'title' => 'Title',
            'content' => 'Content',
        ];
    }
}

class PostFormDynamicMessageAttributeStub extends Form
{
    #[\Livewire\Attributes\Rule('required', 'title')]
    public $title = '';

    #[\Livewire\Attributes\Rule(['required', 'min:10'], 'content')]
    public $content = '';

    public function messages()
    {
        return [
            'title' => 'Field :attribute is must',
            'content.required' => 'This field is required in this form',
            'content.min' => 'Need at least 10 letters',
        ];
    }
}
