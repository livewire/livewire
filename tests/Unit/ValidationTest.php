<?php

namespace Tests\Unit;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Livewire\Component;
use Livewire\Livewire;

class ValidationTest extends TestCase
{
    /** @test */
    public function validate_component_properties()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidation');

        $this->assertStringNotContainsString('The foo field is required', $component->payload['effects']['html']);
        $this->assertStringContainsString('The bar field is required', $component->payload['effects']['html']);
    }

    /** @test */
    public function validate_component_properties_with_custom_message()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithCustomMessage');

        $this->assertStringContainsString('Custom Message', $component->payload['effects']['html']);
    }

    /** @test */
    public function validate_component_properties_with_custom_message_property()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithMessageProperty');

        $this->assertStringContainsString('Custom Message', $component->payload['effects']['html']);
    }

    /** @test */
    public function validate_component_properties_with_custom_attribute_property()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithAttributesProperty');

        $this->assertStringContainsString('The foobar field is required.', $component->payload['effects']['html']);
    }

    /** @test */
    public function validate_component_properties_with_custom_attribute()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithCustomAttribute');

        $this->assertStringContainsString('The foobar field is required.', $component->payload['effects']['html']);
    }

    /** @test */
    public function validate_nested_component_properties()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runNestedValidation');

        $this->assertStringContainsString('emails.1 must be a valid email address.', $component->payload['effects']['html']);
    }

    /** @test */
    public function validate_deeply_nested_component_properties()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runDeeplyNestedValidation');

        $this->assertStringContainsString('items.1.baz field is required', $component->payload['effects']['html']);
        $this->assertStringNotContainsString('items.0.baz field is required', $component->payload['effects']['html']);
    }

    /** @test */
    public function validation_errors_persist_across_requests()
    {
        $component = Livewire::test(ForValidation::class);

        $component->call('runValidation')
            ->assertSee('The bar field is required')
            ->set('foo', 'bar')
            ->assertSee('The bar field is required');
    }

    /** @test */
    public function old_validation_errors_are_overwritten_if_new_request_has_errors()
    {
        $component = Livewire::test(ForValidation::class);

        $component->call('runValidation')
            ->set('foo', '')
            ->call('runValidation')
            ->call('$refresh')
            ->assertSee('The foo field is required');
    }

    /** @test */
    public function old_validation_is_cleared_if_new_validation_passes()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', '')
            ->set('bar', '')
            ->call('runValidation')
            ->assertSee('The foo field is required')
            ->assertSee('The bar field is required')
            ->set('foo', 'foo')
            ->set('bar', 'bar')
            ->call('runValidation')
            ->assertDontSee('The foo field is required')
            ->assertDontSee('The bar field is required');
    }

    /** @test */
    public function can_validate_only_a_specific_field_and_preserve_other_validation_messages()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'foo')
            ->set('bar', '')
            ->call('runValidation')
            ->assertDontSee('The foo field is required')
            ->assertSee('The bar field is required')
            ->set('foo', '')
            ->call('runValidationOnly', 'foo')
            ->assertSee('The foo field is required')
            ->assertSee('The bar field is required');
    }

    /** @test */
    public function validating_only_a_specific_field_wont_throw_an_error_if_the_field_doesnt_exist()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('bar', '')
            ->call('runValidationOnlyWithFooRules', 'bar')
            ->assertDontSee('The foo field is required')
            ->assertDontSee('The bar field is required');
    }

    /** @test */
    public function can_validate_only_a_specific_field_with_custom_message_property()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'foo')
            ->set('bar', '')
            ->call('runValidationOnlyWithMessageProperty', 'foo')
            ->assertDontSee('Foo Message') // Foo is set, no error
            ->assertDontSee('Bar Message') // Bar is not being validated, don't show
            ->set('foo', '')
            ->call('runValidationOnlyWithMessageProperty', 'bar')
            ->assertDontSee('Foo Message') // Foo is not being validated, don't show
            ->assertSee('Bar Message'); // Bar is not set, show message
    }

    /** @test */
    public function can_validate_only_a_specific_field_with_deeply_nested_array()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.0.baz')
            ->assertDontSee('items.0.baz field is required')
            ->runAction('runDeeplyNestedValidationOnly', 'items.1.baz')
            ->assertSee('items.1.baz field is required');
    }

    /** @test */
    public function old_deeply_nested_wildcard_validation_only_is_cleared_if_new_validation_passes()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.*.baz')
            ->assertSee('items.1.baz field is required')
            ->set('items.1.baz', 'blab')
            ->runAction('runDeeplyNestedValidationOnly', 'items.*.baz')
            ->assertDontSee('items.1.baz field is required');
    }

    /** @test */
    public function old_deeply_nested_wildcard_validation_only_is_cleared_if_new_validation_fails()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.*.baz')
            ->assertSee('items.1.baz field is required')
            ->set('items.1.baz', 'blab')
            ->set('items.0.baz', '')
            ->runAction('runDeeplyNestedValidationOnly', 'items.*.baz')
            ->assertDontSee('items.1.baz field is required')
            ->assertSee('items.0.baz field is required');
    }

    /** @test */
    public function old_deeply_nested_specific_validation_only_is_cleared_if_new_validation_passes()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.1.baz')
            ->assertSee('items.1.baz field is required')
            ->set('items.1.baz', 'blab')
            ->runAction('runDeeplyNestedValidationOnly', 'items.1.baz')
            ->assertDontSee('items.1.baz field is required');
    }

    /** @test */
    public function old_deeply_nested_specific_validation_only_is_cleared_if_new_validation_fails()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.1.baz')
            ->assertSee('items.1.baz field is required')
            ->set('items.1.baz', 'blab')
            ->set('items.0.baz', '')
            ->runAction('runDeeplyNestedValidationOnly', 'items.*.baz')
            ->assertDontSee('items.1.baz field is required')
            ->assertSee('items.0.baz field is required');
    }

    /** @test */
    public function validation_errors_are_shared_for_all_views()
    {
        $component = Livewire::test(ForValidation::class);

        app('view')->share('errors', $errors = new ViewErrorBag);

        $component
            ->set('bar', '')
            ->call('runValidation')
            ->assertSee('sharedError:The bar field is required');

        $this->assertTrue(app('view')->shared('errors') === $errors);
    }

    /** @test */
    public function multi_word_validation_rules_failing_are_assertable()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'bar123&*(O)')
            ->call('runValidationWithMultiWordRule')
            ->assertHasErrors(['foo' => 'alpha_dash']);
    }

    /** @test */
    public function class_based_validation_rules_failing_are_assertable()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'barbaz')
            ->call('runValidationWithClassBasedRule')
            ->assertHasErrors(['foo' => ValueEqualsFoobar::class]);
    }

    /** @test */
    public function can_assert_has_no_errors_when_no_validation_has_failed_and_specific_keys_are_supplied()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'foo')
            ->set('bar', 'bar')
            ->call('runValidation')
            ->assertHasNoErrors(['foo' => 'required']);
    }

    /** @test */
    public function multi_word_validation_rules_passing_are_assertable()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'foo-bar-baz')
            ->call('runValidationWithMultiWordRule')
            ->assertHasNoErrors(['foo' => 'alpha_dash']);
    }

    /** @test */
    public function class_based_validation_rules_are_assertable()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'foobar')
            ->call('runValidationWithClassBasedRule')
            ->assertHasNoErrors(['foo' => ValueEqualsFoobar::class]);
    }

    /** @test */
    public function custom_validation_messages_are_cleared_between_validate_only_validations()
    {
        $component = Livewire::test(ForValidation::class);

        // cleared when custom validation passes
        $component
            ->set('foo', 'foo')
            ->set('bar', 'b')
            ->call('runValidationOnlyWithCustomValidation', 'bar')
            ->assertDontSee('The bar field is required')
            ->assertSee('Lengths must be the same')
            ->set('bar', 'baz')
            ->call('runValidationOnlyWithCustomValidation', 'bar')
            ->assertDontSee('The bar field is required')
            ->assertDontSee('Lengths must be the same');

        // cleared when custom validation isn't run
        $component
            ->set('foo', 'foo')
            ->set('bar', 'b')
            ->call('runValidationOnlyWithCustomValidation', 'bar')
            ->assertDontSee('The bar field is required')
            ->assertSee('Lengths must be the same')
            ->set('bar', '')
            ->call('runValidationOnlyWithCustomValidation', 'bar')
            ->assertSee('The bar field is required')
            ->assertDontSee('Lengths must be the same');
    }

    /** @test */
    public function validation_fails_when_same_rule_is_used_without_matching()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('password', 'supersecret')
            ->call('runSameValidation')
            ->assertSee('The password and password confirmation must match');
    }

    /** @test */
    public function validation_passes_when_same_rule_is_used_and_matches()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('password', 'supersecret')
            ->set('passwordConfirmation', 'supersecret')
            ->call('runSameValidation')
            ->assertDontSee('The password and password confirmation must match');
    }

    /** @test */
    public function only_data_in_validation_rules_is_returned()
    {
        $component = new ForValidation();
        $component->bar = 'is required';

        $validatedData = $component->runValidationWithoutAllPublicPropertiesAndReturnValidatedData();
        $this->assertSame([
            'bar' => $component->bar,
        ], $validatedData);
    }

    /** @test */
    public function can_assert_validation_errors_on_errors_thrown_from_custom_validator()
    {
        $component = Livewire::test(ForValidation::class);

        $component->call('failFooOnCustomValidator')->assertHasErrors('plop');
    }

    /** @test */
    public function can_use_withvalidator_method()
    {
        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSet('count', 0)->call('runValidationWithClosure')->assertSet('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSet('count', 0)->call('runValidationWithThisMethod')->assertSet('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSet('count', 0)->call('runValidateOnlyWithClosure')->assertSet('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSet('count', 0)->call('runValidateOnlyWithThisMethod')->assertSet('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSet('count', 0)->call('clearWithValidatorAfterRunningValidateMethod')->assertSet('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSet('count', 0)->call('clearWithValidatorAfterRunningValidateOnlyMethod')->assertSet('count', 1);
    }
}

class ForValidation extends Component
{
    public $foo = 'foo';
    public $bar = '';
    public $emails = ['foo@bar.com', 'invalid-email'];
    public $items = [
        ['foo' => 'bar', 'baz' => 'blab'],
        ['foo' => 'bar', 'baz' => ''],
    ];
    public $password = '';
    public $passwordConfirmation = '';

    public function runValidation()
    {
        $this->validate([
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function runValidationWithMultiWordRule()
    {
        $this->validate([
            'foo' => 'alpha_dash',
        ]);
    }

    public function runValidationWithClassBasedRule()
    {
        $this->validate([
            'foo' => [new ValueEqualsFoobar],
        ]);
    }

    public function runValidationOnly($field)
    {
        $this->validateOnly($field, [
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function runValidationOnlyWithFooRules($field)
    {
        $this->validateOnly($field, [
            'foo' => 'required',
        ]);
    }

    public function runValidationOnlyWithCustomValidation($field)
    {
        $this->validateOnly($field, [
            'foo' => 'required',
            'bar' => 'required',
        ]);

        Validator::make(
            [
                'foo_length' => strlen($this->foo),
                'bar_length' => strlen($this->bar),
            ],
            [ 'foo_length' => 'same:bar_length' ],
            [ 'same' => 'Lengths must be the same' ]
        )->validate();
    }

    public function runValidationOnlyWithMessageProperty($field)
    {
        $this->messages = [
            'foo.required' => 'Foo Message',
            'bar.required' => 'Bar Message',
        ];

        $this->validateOnly($field, [
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function runDeeplyNestedValidationOnly($field)
    {
        $this->validateOnly($field, [
            'items' => ['required', 'array'],
            'items.*' => 'array',
            'items.*.foo' => ['required', 'string'],
            'items.*.baz' => ['required', 'string'],
        ]);
    }

    public function runValidationWithCustomMessage()
    {
        $this->validate([
            'bar' => 'required',
        ], ['required' => 'Custom Message']);
    }

    public function runValidationWithMessageProperty()
    {
        $this->messages = [
            'required' => 'Custom Message'
        ];

        $this->validate([
            'bar' => 'required'
        ]);
    }

    public function runValidationWithAttributesProperty()
    {
        $this->validationAttributes = ['bar' => 'foobar'];

        $this->validate([
            'bar' => 'required',
        ]);
    }

    public function runValidationWithCustomAttribute()
    {
        $this->validate([
            'bar' => 'required',
        ], [], ['bar' => 'foobar']);
    }

    public function runNestedValidation()
    {
        $this->validate([
            'emails.*' => 'email',
        ]);
    }

    public function runDeeplyNestedValidation()
    {
        $this->validate([
            'items' => ['required', 'array'],
            'items.*' => 'array',
            'items.*.foo' => ['required', 'string'],
            'items.*.baz' => ['required', 'string'],
        ]);
    }


    public function runSameValidation()
    {
        $this->validate([
            'password' => 'same:passwordConfirmation',
        ]);
    }

    public function runValidationWithoutAllPublicPropertiesAndReturnValidatedData()
    {
        return $this->validate(['bar' => 'required']);
    }

    public function failFooOnCustomValidator()
    {
        Validator::make([], ['plop' => 'required'])->validate();
    }

    public function render()
    {
        return app('view')->make('dump-errors');
    }
}

class ValueEqualsFoobar implements Rule
{
    public function passes($attribute, $value)
    {
        return $value === 'foobar';
    }

    public function message()
    {
        return '';
    }
}

class WithValidationMethod extends Component
{
    public $foo = 'bar';

    public $count = 0;

    public function runValidationWithClosure()
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                $this->count++;
            });
        })->validate([
            'foo' => 'required',
        ]);
    }

    public function runValidateOnlyWithClosure()
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                $this->count++;
            });
        })->validateOnly('foo', [
            'foo' => 'required',
        ]);
    }

    public function runValidationWithThisMethod()
    {
        $this->withValidator([$this, 'doSomethingWithValidator'])->validate([
            'foo' => 'required',
        ]);
    }

    public function runValidateOnlyWithThisMethod()
    {
        $this->withValidator([$this, 'doSomethingWithValidator'])->validateOnly('foo', [
            'foo' => 'required',
        ]);
    }

    public function clearWithValidatorAfterRunningValidateMethod()
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                $this->count++;
            });
        })->validate([
            'foo' => 'required',
        ]);

        $this->validate(['foo' => 'required']);
    }

    public function clearWithValidatorAfterRunningValidateOnlyMethod()
    {
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                $this->count++;
            });
        })->validateOnly('foo', [
            'foo' => 'required',
        ]);

        $this->validateOnly('foo', ['foo' => 'required']);
    }

    protected function doSomethingWithValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->count++;
        });
    }

    public function render()
    {
        return app('view')->make('dump-errors');
    }
}
