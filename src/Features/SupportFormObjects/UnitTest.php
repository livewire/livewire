<?php

namespace Livewire\Features\SupportFormObjects;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use Sushi\Sushi;

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
    function can_validate_a_form_object_with_validate_only()
    {
        Livewire::test(new class extends Component {
            public PostFormValidateStub $form;

            function save()
            {
                $this->form->validateOnly('title');
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasNoErrors('form.content')
        ;
    }

    /** @test */
    function can_validate_a_form_object_with_root_component_validate_only()
    {
        Livewire::test(new class extends Component {
            public PostFormValidateStub $form;

            function save()
            {
                $this->validateOnly('form.title');
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasNoErrors('form.content')
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
    function can_validate_a_form_object_using_rule_attribute_with_custom_name()
    {
        Livewire::test(new class extends Component {
            public PostFormRuleAttributeWithCustomNameStub $form;

            function save()
            {
                $this->form->validate();
            }

            function render() {
                return '<div></div>';
            }
        })
            ->assertSet('form.name', '')
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasErrors('form.name')
            ->set('form.name', 'Mfawa...')
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
    function all_properties_are_available_in_rules_method()
    {
        Livewire::test(new class extends Component {
            public PostFormWithRulesStub $form;

            public function mount()
            {
                $this->form->setPost(42);
            }

            function save() {
                $this->form->validate();
            }

            function render() {
                return '<div></div>';
            }
        })
        ->assertSet('form.post', 42)
        ->call('save')
        ->assertSet('form.post', 42)
        ->assertHasErrors()
        ;
    }

    /** @test */
    function can_get_only_specific_properties()
    {
        $component = new class extends Component {};

        $form = new PostFormStub($component, 'foobar');

        $this->assertEquals(
            ['title' => ''],
            $form->only('title')
        );

        $this->assertEquals(
            ['content' => ''],
            $form->except(['title'])
        );

        $this->assertEquals(
            ['title' => '', 'content' => ''],
            $form->only('title', 'content')
        );
    }

    /** @test */
    function can_get_properties_except()
    {
        $component = new class extends Component {};

        $form = new PostFormStub($component, 'foobar');

        $this->assertEquals(
            ['content' => ''],
            $form->except('title')
        );

        $this->assertEquals(
            ['content' => ''],
            $form->except(['title'])
        );

        $this->assertEquals(
            [],
            $form->except('title', 'content')
        );
    }

    /** @test */
    function validation_can_show_a_form_object_dynamic_validation_attributes()
    {
        Livewire::test(new class extends Component {
            public PostFormDynamicValidationAttributesStub $withDynamicValidationAttributesForm;

            function save()
            {
                $this->withDynamicValidationAttributesForm->validate();
            }

            public function render() { return <<<'HTML'
                <div>
                    {{ $errors }}
                </div>
            HTML; }
        })
            ->set('withDynamicValidationAttributesForm.title', '')
            ->set('withDynamicValidationAttributesForm.content', '')
            ->call('save')
            ->assertSee('Custom Title')
            ->assertSee('Custom Content')
        ;
    }

    /** @test */
    function multiple_form_objects_in_component_not_interfering_between()
    {
        Livewire::test(new class extends Component {
            public PostFormDynamicValidationAttributesStub $firstForm;
            public PostFormDynamicMessagesAndAttributesStub $secondForm;

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

    /** @test */
    function validation_showing_a_form_object_dynamic_messages()
    {
        Livewire::test(new class extends Component {
            public PostFormDynamicMessagesStub $form;

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
    public function can_fill_a_form_object_from_model()
    {
        Livewire::test(new class extends Component {
            public PostForFormObjectTesting $post;
            public PostFormStub $form;

            public function mount()
            {
                $this->post = PostForFormObjectTesting::first();
            }

            public function fillForm()
            {
                $this->form->fill($this->post);
            }

            public function render()
            {
                return '<div></div>';
            }
        })
            ->assertSet('form.title', '')
            ->assertSet('form.content', '')
            ->call('fillForm')
            ->assertSet('form.title', 'A Title')
            ->assertSet('form.content', 'Some content')
        ;
    }

    /** @test */
    public function can_fill_a_form_object_from_array()
    {
        Livewire::test(new class extends Component {
            public PostFormStub $form;

            public function fillForm()
            {
                $this->form->fill([
                    'title' => 'Title from array',
                    'content' => 'Content from array',
                ]);
            }

            public function render()
            {
                return '<div></div>';
            }
        })
            ->assertSet('form.title', '')
            ->assertSet('form.content', '')
            ->call('fillForm')
            ->assertSet('form.title', 'Title from array')
            ->assertSet('form.content', 'Content from array')
        ;
    }

    /** @test */
    function form_object_validation_runs_alongside_component_validation()
    {
        Livewire::test(new class extends Component {
            public PostFormValidateStub $form;

            #[Validate('required')]
            public $username = '';

            function save()
            {
                $this->validate();
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasErrors('form.content')
        ->assertHasErrors('username')
        ;
    }

    /** @test */
    function form_object_validation_wont_run_if_rules_are_passed_into_validate()
    {
        Livewire::test(new class extends Component {
            public PostFormValidateStub $form;

            public $username = '';

            function save()
            {
                $this->validate(['username' => 'required']);
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasNoErrors('form.title')
        ->assertHasNoErrors('form.content')
        ->assertHasErrors('username')
        ;
    }

    /** @test */
    function resetting_validation_errors_resets_form_objects_as_well()
    {
        Livewire::test(new class extends Component {
            public PostFormValidateStub $form;

            #[Validate('required')]
            public $username = '';

            function save()
            {
                $this->validate();
            }

            function resetVal()
            {
                $this->resetValidation();
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasErrors('form.content')
        ->call('resetVal')
        ->assertHasNoErrors('form.title')
        ->assertHasNoErrors('form.content')
        ;
    }

    /** @test */
    function can_intercept_form_object_validator_instance()
    {
        Livewire::test(new class extends Component {
            public PostFormValidateWithInterceptStub $form;

            function save()
            {
                $this->validate();
            }

            function resetVal()
            {
                $this->resetValidation();
            }

            public function render() {
                return '<div></div>';
            }
        })
        ->assertHasNoErrors()
        ->set('form.title', '"title with quotes"')
        ->set('form.content', 'content')
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasNoErrors('form.content')
        ;
    }

    /** @test */
    function can_use_the_mount_hook_from_form_objects()
    {
        Livewire::test(new class extends Component {
            public FormObjectWithMountMethod $form;

            public $formFooValue;

            public function mount($thing)
            {
                $this->formFooValue = $this->form->foo;
            }

            public function render() {
                return '<div></div>';
            }
        }, ['bar'])
        ->assertSet('formFooValue', 'bar')
        ->call('$refresh')
        ->assertSet('formFooValue', 'bar')
        ;
    }
}

class PostFormStub extends Form
{
    public $title = '';

    public $content = '';
}

class PostFormWithRulesStub extends Form
{
    public ?int $post = null;
    public $title = '';
    public $content = '';

    public function setPost($model)
    {
        $this->post = $model;
    }

    public function rules()
    {
        Assert::assertEquals(42, $this->post, 'post should be available to run more complex rules');

        return [
            'title' => 'required',
            'content' => 'required',
        ];
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

class PostFormValidateWithInterceptStub extends Form
{
    public $title = '';

    public $content = '';

    protected $rules = [
        'title' => 'required',
        'content' => 'required',
    ];

    public function boot()
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                if (str($this->title)->startsWith('"')) {
                    $validator->errors()->add('title', 'Titles cannot start with quotations');
                }
            });
        });
    }
}

class PostFormRuleAttributeStub extends Form
{
    #[Validate('required')]
    public $title = '';

    #[Validate('required')]
    public $content = '';
}

class PostFormRuleAttributeWithCustomNameStub extends Form
{
    #[Validate(
        rule: [
            'required',
            'min:3',
            'max:255'
        ],
        as: 'my name'
    )]
    public $name = '';
}

class PostFormDynamicValidationAttributesStub extends Form
{
    #[Validate('required')]
    public $title = '';

    #[Validate('required')]
    public $content = '';

    public function validationAttributes() {
        return [
            'title' => 'Custom Title',
            'content' => 'Custom Content',
        ];
    }
}

class PostFormDynamicMessagesStub extends Form
{
    #[Validate('required')]
    public $title = '';

    #[Validate(['required', 'min:10'])]
    public $content = '';

    public function messages()
    {
        return [
            'title.required' => ':attribute is must to fill',
            'content.min' => ':attribute need at least 10 letters',
        ];
    }
}

class PostFormDynamicMessagesAndAttributesStub extends Form
{
    #[Validate('required')]
    public $title = '';

    #[Validate('required')]
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

class PostForFormObjectTesting extends Model
{
    use Sushi;

    protected $rows = [
        [
            'title' => 'A Title',
            'content' => 'Some content',
        ],
    ];
}

class FormObjectWithMountMethod extends Form
{
    public $foo;

    public function mount($foo)
    {
        $this->foo = $foo;
    }
}
