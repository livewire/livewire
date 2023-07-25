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
    function can_reset_form_object_property()
    {
        Livewire::test(new class extends Component {
            public PostFormStub $form;

            public function resetForm()
            {
                $this->reset('form.title', 'form.content');
            }

            public function render() {
                return '<div></div>';
            }
        })
            ->assertSet('form.title', '')
            ->assertSet('form.content', '')
            ->set('form.title', 'Some Title')
            ->set('form.content', 'Some content...')
            ->call('resetForm')
            ->assertSet('form.title', '')
            ->assertSet('form.content', '')
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
    function can_reset_a_form_object_inside_the_class()
    {
        Livewire::test(new class extends Component {
            public PostFormStoreStub $form;

            function save()
            {
                $this->form->store();
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->set('form.title', 'Some Title')
        ->set('form.content', 'Some content...')
        ->assertHasNoErrors()
        ->call('save')
        ->assertSet('form.title', '')
        ->assertSet('form.content', '')
        ;
    }

    /** @test */
    function can_reset_all_properties_of_form_object_inside_the_class()
    {
        Livewire::test(new class extends Component {
            public PostFormStub $form;

            function save()
            {
                $this->form->reset();
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->set('form.title', 'Some Title')
        ->set('form.content', 'Some content...')
        ->assertHasNoErrors()
        ->call('save')
        ->assertSet('form.title', '')
        ->assertSet('form.content', '')
        ;
    }

    /** @test */
    function can_reset_specific_properties_of_form_object_inside_the_class()
    {
        Livewire::test(new class extends Component {
            public PostFormStub $form;

            function save()
            {
                $this->form->reset('title');
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->set('form.title', 'Some Title')
        ->set('form.content', 'Some content...')
        ->assertHasNoErrors()
        ->call('save')
        ->assertSet('form.title', '')
        ->assertSet('form.content', 'Some content...')
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
}

class PostFormStub extends Form
{
    public $title = '';

    public $content = '';
}

class PostFormStoreStub extends Form
{
    public $title = '';

    public $content = '';

    public function store()
    {
        $this->reset();
    }
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
