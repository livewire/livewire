<?php

namespace Livewire\Features\SupportFormObjects;

use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;
use PHPUnit\Framework\Assert;
use Sushi\Sushi;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    function test_can_use_a_form_object()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormStub $form;
        })
        ->assertSetStrict('form.title', '')
        ->assertSetStrict('form.content', '')
        ->set('form.title', 'Some Title')
        ->set('form.content', 'Some content...')
        ->assertSetStrict('form.title', 'Some Title')
        ->assertSetStrict('form.content', 'Some content...')
        ;
    }

    function test_can_reset_form_object_property()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormStub $form;

            public function resetForm()
            {
                $this->reset('form.title', 'form.content');
            }
        })
            ->assertSetStrict('form.title', '')
            ->assertSetStrict('form.content', '')
            ->set('form.title', 'Some Title')
            ->set('form.content', 'Some content...')
            ->call('resetForm')
            ->assertSetStrict('form.title', '')
            ->assertSetStrict('form.content', '')
        ;
    }

    function test_can_reset_form_object_property_to_defaults()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormStubWithDefaults $form;

            public function resetForm()
            {
                $this->reset('form.title', 'form.content');
            }
        })
            ->assertSetStrict('form.title', 'foo')
            ->assertSetStrict('form.content', 'bar')
            ->set('form.title', 'Some Title')
            ->set('form.content', 'Some content...')
            ->call('resetForm')
            ->assertSetStrict('form.title', 'foo')
            ->assertSetStrict('form.content', 'bar')
        ;
    }

    function test_set_form_object_with_typed_nullable_properties()
    {
        Livewire::test(new class extends Component {
            public PostFormWithTypedProperties $form;

            public function render() {
                return <<<'BLADE'
                    <div>
                        Title: "{{ $form->title }}"
                        Content: "{{ $form->content }}"
                    </div>
                BLADE;
            }
        })
            ->assertSetStrict('form.title', null)
            ->assertSetStrict('form.content', null)
            ->assertSee('Title: ""', false)
            ->assertSee('Content: ""', false)
            ->set('form.title', 'Some Title')
            ->set('form.content', 'Some content...')
            ->assertSetStrict('form.title', 'Some Title')
            ->assertSetStrict('form.content', 'Some content...')
            ->assertSee('Title: "Some Title"', false)
            ->assertSee('Content: "Some content..."', false)
            ->set('form.title', null)
            ->set('form.content', null)
            ->assertSetStrict('form.title', null)
            ->assertSetStrict('form.content', null)
            ->assertSee('Title: ""', false)
            ->assertSee('Content: ""', false);
        ;
    }

    function test_can_validate_a_form_object()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form;

            function save()
            {
                $this->form->validate();
            }
        })
        ->assertSetStrict('form.title', '')
        ->assertSetStrict('form.content', '')
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasErrors('form.content')
        ;
    }

    function test_can_validate_a_form_with_the_general_validate_function()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form;

            function save()
            {
                $this->validate();
            }
        })
            ->call('save')
            ->tap(function ($component) {
                $this->assertCount(1, $component->errors()->get('form.title'));
                $this->assertCount(1, $component->errors()->get('form.content'));
            })
        ;
    }

    function test_can_validate_a_specific_rule_has_errors_in_a_form_object()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form;

            function save()
            {
                $this->validate();
            }
        })
        ->assertSetStrict('form.title', '')
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors(['form.title' => 'required'])
        ;
    }

    function test_can_validate_a_form_object_with_validate_only()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form;

            function save()
            {
                $this->form->validateOnly('title');
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasNoErrors('form.content')
        ;
    }

    function test_can_validate_a_specific_rule_for_form_object_with_validate_only()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form;

            function save()
            {
                $this->form->validateOnly('title');
            }
        })
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasErrors(['form.title' => 'required']);
        ;
    }

    function test_can_validate_a_specific_rule_has_errors_on_update_in_a_form_object()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateOnUpdateStub $form;
        })
            ->assertHasNoErrors()
            ->set('form.title', 'foo')
            ->assertHasErrors(['form.title' => 'min'])
        ;
    }

    function test_can_validate_a_form_object_with_root_component_validate_only()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form;

            function save()
            {
                $this->validateOnly('form.title');
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasNoErrors('form.content')
        ;
    }

    function test_can_validate_a_form_object_using_rule_attributes()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormRuleAttributeStub $form;

            function save()
            {
                $this->form->validate();
            }
        })
        ->assertSetStrict('form.title', '')
        ->assertSetStrict('form.content', '')
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

    function test_multiple_forms_show_all_errors()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form1;
            public PostFormValidateStub $form2;

            function save()
            {
                $this->validate();
            }

            function render()
            {
                return '<div>{{ $errors }}</div>';
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form1.title')
        ->assertHasErrors('form1.content')
        ->assertHasErrors('form2.title')
        ->assertHasErrors('form2.content')
        ->assertSee('The title field is required')
        ->assertSee('The content field is required')
        ->set('form1.title', 'Valid Title 1')
        ->set('form1.content', 'Valid Content 1')
        ->set('form2.title', 'Valid Title 2')
        ->set('form2.content', 'Valid Content 2')
        ->call('save')
        ->assertHasNoErrors();
    }

    function test_can_validate_a_form_object_using_rule_attribute_with_custom_name()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormRuleAttributeWithCustomNameStub $form;

            function save()
            {
                $this->form->validate();
            }
        })
            ->assertSetStrict('form.name', '')
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasErrors('form.name')
            ->set('form.name', 'Mfawa...')
            ->assertHasNoErrors()
            ->call('save')
        ;
    }
    
    public function test_validation_errors_persist_across_validation_errors()
    {
        $component = Livewire::test(new class extends Component {
            public FormWithLiveValidation $form;

            function save()
            {
                $this->form->validate();
            }

            function render() {
                return '<div>{{ $errors }}</div>';
            }
        });

        $component->assertDontSee('The title field is required')
            ->assertDontSee('The content field is required')
            ->set('form.title', '')
            ->assertSee('The title field is required')
            ->assertDontSee('The content field is required')
            ->set('form.content', '')
            ->assertSee('The title field is required')
            ->assertSee('The content field is required');
    }

    function test_can_reset_property()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormStub $form;

            function save()
            {
                $this->form->reset('title');
            }
        })
        ->set('form.title', 'Some title...')
        ->set('form.content', 'Some content...')
        ->assertSetStrict('form.title', 'Some title...')
        ->assertSetStrict('form.content', 'Some content...')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSetStrict('form.title', '')
        ->assertSetStrict('form.content', 'Some content...')
        ;
    }

    function test_can_reset_all_properties()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormStub $form;

            function save()
            {
                $this->form->reset();
            }
        })
        ->set('form.title', 'Some title...')
        ->set('form.content', 'Some content...')
        ->assertSetStrict('form.title', 'Some title...')
        ->assertSetStrict('form.content', 'Some content...')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSetStrict('form.title', '')
        ->assertSetStrict('form.content', '')
        ;
    }

    function test_all_properties_are_available_in_rules_method()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormWithRulesStub $form;

            public function mount()
            {
                $this->form->setPost(42);
            }

            function save() {
                $this->form->validate();
            }
        })
        ->assertSetStrict('form.post', 42)
        ->call('save')
        ->assertSetStrict('form.post', 42)
        ->assertHasErrors()
        ;
    }

    function test_can_get_only_specific_properties()
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

    function test_can_get_properties_except()
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

    function test_validation_can_show_a_form_object_dynamic_validation_attributes()
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

    function test_multiple_form_objects_in_component_not_interfering_between()
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

    function test_validation_showing_a_form_object_dynamic_messages()
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

    public function test_can_fill_a_form_object_from_model()
    {
        Livewire::test(new class extends TestComponent {
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
        })
            ->assertSetStrict('form.title', '')
            ->assertSetStrict('form.content', '')
            ->call('fillForm')
            ->assertSetStrict('form.title', 'A Title')
            ->assertSetStrict('form.content', 'Some content')
        ;
    }

    public function test_can_fill_a_form_object_from_array()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormStub $form;

            public function fillForm()
            {
                $this->form->fill([
                    'title' => 'Title from array',
                    'content' => 'Content from array',
                ]);
            }
        })
            ->assertSetStrict('form.title', '')
            ->assertSetStrict('form.content', '')
            ->call('fillForm')
            ->assertSetStrict('form.title', 'Title from array')
            ->assertSetStrict('form.content', 'Content from array')
        ;
    }

    function test_form_object_validation_runs_alongside_component_validation()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form;

            #[Validate('required')]
            public $username = '';

            function save()
            {
                $this->validate();
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('form.title')
        ->assertHasErrors('form.content')
        ->assertHasErrors('username')
        ;
    }

    function test_form_object_validation_wont_run_if_rules_are_passed_into_validate()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateStub $form;

            public $username = '';

            function save()
            {
                $this->validate(['username' => 'required']);
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasNoErrors('form.title')
        ->assertHasNoErrors('form.content')
        ->assertHasErrors('username')
        ;
    }

    function test_allows_form_object_without_rules_without_throwing_an_error()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormWithoutRules $form;

            public $username = '';

            public function rules()
            {
                return [
                    'username' => 'required',
                ];
            }

            function save()
            {
                $this->validate();
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('username')
        ;
    }

    function test_allows_form_object_without_rules_but_can_still_validate_it_with_its_own_rules()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormWithoutRules $form;

            public $username = '';

            public function rules()
            {
                return [
                    'username' => 'required',
                    'form.title' => 'required',
                ];
            }

            function save()
            {
                $this->validate();
            }
        })
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasErrors('username')
        ->assertHasErrors('form.title')
        ;
    }

    function test_form_object_without_rules_can_still_be_validated_and_return_proper_data()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormWithoutRules $form;

            public $username = '';

            public function rules()
            {
                return [
                    'username' => 'required',
                    'form.title' => 'required',
                ];
            }

            function save()
            {
                $data = $this->validate();

                \PHPUnit\Framework\Assert::assertEquals('foo', data_get($data, 'username'));
                \PHPUnit\Framework\Assert::assertEquals('bar', data_get($data, 'form.title'));
                \PHPUnit\Framework\Assert::assertEquals('not-found', data_get($data, 'form.content', 'not-found'));
            }
        })
        ->assertHasNoErrors()
        ->set('username', 'foo')
        ->set('form.title', 'bar')
        ->call('save')
        ->assertHasNoErrors('username')
        ;
    }

    function test_resetting_validation_errors_resets_form_objects_as_well()
    {
        Livewire::test(new class extends TestComponent {
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

    function test_can_intercept_form_object_validator_instance()
    {
        Livewire::test(new class extends TestComponent {
            public PostFormValidateWithInterceptStub $form;

            function save()
            {
                $this->validate();
            }

            function resetVal()
            {
                $this->resetValidation();
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

    function test_can_reset_and_return_property_with_pull_method()
    {
        Livewire::test(new class extends TestComponent {
            public ResetPropertiesForm $form;

            public $pullResult;

            function test(...$args)
            {
                $this->pullResult = $this->form->proxyPull(...$args);
            }
        })
        ->assertSet('form.foo', 'bar')
        ->assertSet('form.bob', 'lob')
        ->set('form.foo', 'baz')
        ->assertSet('form.foo', 'baz')
        ->call('test', 'foo')
        ->assertSet('form.foo', 'bar')
        ->assertSet('pullResult', 'baz');
    }

    function test_can_pull_all_properties()
    {
        $component = Livewire::test(new class extends TestComponent {
            public ResetPropertiesForm $form;

            public $pullResult;

            function test(...$args)
            {
                $this->pullResult = $this->form->proxyPull(...$args);
            }
        })
        ->assertSet('form.foo', 'bar')
        ->set('form.foo', 'baz')
        ->assertSet('form.foo', 'baz')
        ->assertSet('pullResult', null)
        ->call('test');

        $this->assertEquals('baz', $component->pullResult['foo']);
        $this->assertEquals('lob', $component->pullResult['bob']);
    }

    function test_can_pull_some_properties()
    {
        $component = Livewire::test(new class extends TestComponent {
            public ResetPropertiesForm $form;

            function test(...$args)
            {
                $this->form->proxyResetExcept(...$args);
            }
        })
        ->assertSet('form.foo', 'bar')
        ->set('form.foo', 'baz')
        ->assertSet('form.foo', 'baz')
        ->assertSet('form.bob', 'lob')
        ->set('form.bob', 'loc')
        ->assertSet('form.bob', 'loc')
        ->call('test', ['foo']);

        $this->assertEquals('baz', $component->form->foo);
        $this->assertEquals('lob', $component->form->bob);
    }
}

class PostFormStub extends Form
{
    public $title = '';

    public $content = '';
}

class PostFormStubWithDefaults extends Form
{
    public $title = 'foo';

    public $content = 'bar';
}

class PostFormWithTypedProperties extends Form
{
    public ?string $title = null;

    public ?string $content = null;
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

class PostFormValidateOnUpdateStub extends Form
{
    #[Validate]
    public $title = '';

    protected $rules = [
        'title' => 'min:5',
    ];
}

class PostFormWithoutRules extends Form
{
    public $title = '';

    public $content = '';
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

class FormWithLiveValidation extends Form
{
    #[Validate]
    public $title = 'title';

    #[Validate]
    public $content = 'content';

    public function rules()
    {
        return [
            'title' => [
                'required',
            ],

            'content' => [
                'required',
            ],
        ];
    }
}

class ResetPropertiesForm extends Form
{
    public $foo = 'bar';
    public $bob = 'lob';

    public function proxyResetExcept(...$args){
        return $this->resetExcept(...$args);
    }

    public function proxyPull(...$args){
        return $this->pull(...$args);
    }
}