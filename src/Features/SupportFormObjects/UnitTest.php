<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Attributes\Form\Reset;
use Livewire\Component;
use Livewire\Exceptions\ResetPropertyNotAllowed;
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
    function can_reset_content_property_using_attribute()
    {
        Livewire::test(new class extends Component {
            public PostFormResetContentWithAttribute $form;

            function save()
            {
                $this->form->reset();
            }

            function render() {
                return '<div></div>';
            }
        })
        ->assertSet('form.title', 'Some title...')
        ->set('form.content', 'Some content...')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.title', 'Some title...')
        ->assertSet('form.content', '')
        ;
    }

    /** @test */
    function can_reset_title_property_using_attribute()
    {
        Livewire::test(new class extends Component {
            public PostFormResetTitleWithAttribute $form;

            function save()
            {
                $this->form->reset();
            }

            function render() {
                return '<div></div>';
            }
        })
        ->set('form.title', 'Some title...')
        ->assertSet('form.content', 'Some content...')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('form.title', '')
        ->assertSet('form.content', 'Some content...')
        ;
    }

    /** @test */
    function can_reset_title_and_content_using_attribute()
    {
        Livewire::test(new class extends Component {
            public PostFormResetWithAttribute $form;

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
    function can_reset_title_and_content_without_attribute()
    {
        Livewire::test(new class extends Component {
            public PostFormReset $form;

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
    function can_reset_single_property()
    {
        Livewire::test(new class extends Component {
            public PostFormReset $form;

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
    function cannot_reset_property_with_attribute_as_false()
    {
        $this->expectException(ResetPropertyNotAllowed::class);
        $this->expectExceptionMessage("Property not allowed to be reset: [title].");

        Livewire::test(new class extends Component {
            public PostFormDontReset $form;

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
        ->assertSet('form.title', 'Some title...')
        ->assertSet('form.content', 'Some content...')
        ;
    }
}

class PostFormStub extends Form
{
    public $title = '';

    public $content = '';
}

class PostFormResetContentWithAttribute extends Form
{
    #[Reset(false)]
    public $title = 'Some title...';

    public $content = '';
}

class PostFormResetTitleWithAttribute extends Form
{
    public $title = '';

    #[Reset(false)]
    public $content = 'Some content...';
}

class PostFormResetWithAttribute extends Form
{
    #[Reset]
    public $title = '';

    #[Reset]
    public $content = '';
}

class PostFormDontReset extends Form
{
    #[Reset(false)]
    public $title = 'Some title...';

    public $content = '';
}

class PostFormReset extends Form
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
