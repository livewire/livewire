<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

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

    function can_manually_add_errors_to_the_error_bag()
    {
        Livewire::test(new class extends Component {
            public PostFormValidateStub $form;

            function save()
            {
                $this->addError('status', 'An error message...');
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertSet('form.title', '')
        ->assertSet('form.content', '')
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.status')
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
    function can_reset_property()
    {
        Livewire::test(new class extends Component {
            public PostFormStub $form;

            function save()
            {
                $this->form->reset('title');
            }

            function render() {
                return '<div></div>';
            }
        })
        ->set('form.title', 'Some title...')
        ->set('form.content', 'Some content...')
        ->assertSet('form.title', 'Some title...')
        ->assertSet('form.content', 'Some content...')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.title', '')
        ->assertSet('form.content', 'Some content...')
        ;
    }

    /** @test */
    function can_reset_all_properties()
    {
        Livewire::test(new class extends Component {
            public PostFormStub $form;

            function save()
            {
                $this->form->reset();
            }

            function render() {
                return '<div></div>';
            }
        })
        ->set('form.title', 'Some title...')
        ->set('form.content', 'Some content...')
        ->assertSet('form.title', 'Some title...')
        ->assertSet('form.content', 'Some content...')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.title', '')
        ->assertSet('form.content', '')
        ;
    }

    /** @test */
    function can_get_properties_except()
    {
        $component = new class extends Component {};

        $form = new PostFormStub($component, 'foobar');

        $this->assertEquals(
            ["content" => ""],
            $form->except(["title"])
        );
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
    #[Rule('required')]
    public $title = '';

    #[Rule('required')]
    public $content = '';
}
