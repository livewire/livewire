<?php

namespace Livewire\Features\SupportValidation;

use Tests\TestComponent;
use Livewire\Wireable;
use Livewire\Livewire;
use Livewire\Exceptions\MissingRulesException;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Rule;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

class UnitTest extends \Tests\TestCase
{
    public function test_update_triggers_rule_attribute()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate('required')]
            public $foo = '';

            #[Validate('required')]
            public $bar = '';

            function clear() { $this->clearValidation(); }

            function save() { $this->validate(); }
        })
            ->set('bar', 'testing...')
            ->assertHasNoErrors()
            ->set('foo', '')
            ->assertHasErrors(['foo' => 'required'])
            ->call('clear')
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasErrors([
                'foo' => 'required',
            ]);
    }

    public function test_deprecated_rule_alias_can_be_used()
    {
        Livewire::test(new class extends TestComponent {
            #[Rule('required')]
            public $foo = '';

            #[Rule('required')]
            public $bar = '';

            function clear() { $this->clearValidation(); }

            function save() { $this->validate(); }
        })
            ->set('bar', 'testing...')
            ->assertHasNoErrors()
            ->set('foo', '')
            ->assertHasErrors(['foo' => 'required'])
            ->call('clear')
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasErrors([
                'foo' => 'required',
            ]);
    }

    public function test_validate_can_be_used_without_a_rule()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate]
            public $foo = '';

            public $bar = '';

            public function rules()
            {
                return [
                    'foo' => 'required',
                    'bar' => 'required',
                ];
            }

            function clear() { $this->clearValidation(); }

            function save() { $this->validate(); }
        })
            ->set('foo', 'testing...')
            ->assertHasNoErrors()
            ->set('foo', '')
            ->assertHasErrors(['foo' => 'required']);
    }

    public function test_realtime_validation_can_be_opted_out_of()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate('required|min:3', onUpdate: false)]
            public $foo = '';

            #[Validate('required|min:3')]
            public $bar = '';

            function clear() { $this->clearValidation(); }

            function save() { $this->validate(); }
        })
            ->assertHasNoErrors()
            ->set('bar', 'te')
            ->assertHasErrors()
            ->set('bar', 'testing...')
            ->assertHasNoErrors()
            ->set('foo', 'te')
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasErrors();
    }

    public function test_rule_attribute_supports_custom_attribute()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate('required|min:3', attribute: 'The Foo')]
            public $foo = '';

            function clear() { $this->clearValidation(); }

            function save() { $this->validate(); }
        })
            ->set('foo', 'te')
            ->assertHasErrors()
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();

                $this->assertEquals('The The Foo field must be at least 3 characters.', $messages['foo'][0]);
            })
            ;
    }

    public function test_rule_attribute_supports_custom_attribute_as_alias()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate('required|min:3', as: 'The Foo')]
            public $foo = '';

            function clear() { $this->clearValidation(); }

            function save() { $this->validate(); }
        })
            ->set('foo', 'te')
            ->assertHasErrors()
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();

                $this->assertEquals('The The Foo field must be at least 3 characters.', $messages['foo'][0]);
            })
            ;
    }

    public function test_rule_attribute_alias_is_translatable()
    {
        Lang::addLines(['translatable.foo' => 'Translated Foo'], App::currentLocale());

        Livewire::test(new class extends TestComponent {
            #[Validate('required|min:3', as: 'translatable.foo')]
            public $foo = '';
        })
            ->set('foo', 'te')
            ->assertHasErrors()
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();

                $this->assertEquals('The Translated Foo field must be at least 3 characters.', $messages['foo'][0]);
            })
            ;
    }

    public function test_rule_attribute_alias_is_translatable_with_array()
    {
        Lang::addLines(['translatable.foo' => 'Translated Foo'], App::currentLocale());

        Livewire::test(new class extends TestComponent {
            #[Validate('required|min:3', as: ['foo' => 'translatable.foo'])]
            public $foo = '';
        })
            ->set('foo', 'te')
            ->assertHasErrors()
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();

                $this->assertEquals('The Translated Foo field must be at least 3 characters.', $messages['foo'][0]);
            })
        ;
    }

    public function test_rule_attribute_alias_translation_can_be_opted_out()
    {
        Lang::addLines(['translatable.foo' => 'Translated Foo'], App::currentLocale());

        Livewire::test(new class extends TestComponent {
            #[Validate('required|min:3', as: 'translatable.foo', translate: false)]
            public $foo = '';
        })
            ->set('foo', 'te')
            ->assertHasErrors()
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();

                $this->assertEquals('The translatable.foo field must be at least 3 characters.', $messages['foo'][0]);
            })
            ;
    }

    public function test_rule_attribute_supports_custom_messages()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate('min:5', message: 'Your foo is too short.')]
            public $foo = '';

            function clear() { $this->clearValidation(); }

            function save() { $this->validate(); }
        })
            ->set('foo', 'te')
            ->assertHasErrors()
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();

                $this->assertEquals('Your foo is too short.', $messages['foo'][0]);
            })
            ;
    }

    public function test_rule_attribute_supports_custom_messages_when_using_repeated_attributes()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate('required', message: 'Please provide a post title')]
            #[Validate('min:3', message: 'This title is too short')]
            public $title = '';
        })
            ->set('title', '')
            ->assertHasErrors(['title' => 'required'])
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();
                $this->assertEquals('Please provide a post title', $messages['title'][0]);
            })
        ;
    }

    public function test_rule_attribute_message_is_translatable()
    {
        Lang::addLines(['translatable.foo' => 'Your foo is too short.'], App::currentLocale());

        Livewire::test(new class extends TestComponent {
            #[Validate('min:5', message: 'translatable.foo')]
            public $foo = '';
        })
            ->set('foo', 'te')
            ->assertHasErrors()
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();

                $this->assertEquals('Your foo is too short.', $messages['foo'][0]);
            })
        ;
    }

    public function test_rule_attribute_message_is_translatable_with_array()
    {
        Lang::addLines(['translatable.foo' => 'Your foo is too short.'], App::currentLocale());

        Livewire::test(new class extends TestComponent {
            #[Validate('min:5', message: ['min' => 'translatable.foo'])]
            public $foo = '';
        })
            ->set('foo', 'te')
            ->assertHasErrors()
            ->tap(function ($component) {
                $messages = $component->errors()->getMessages();

                $this->assertEquals('Your foo is too short.', $messages['foo'][0]);
            })
        ;
    }

    public function test_rule_attributes_can_contain_rules_for_multiple_properties()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate(['foo' => 'required', 'bar' => 'required'])]
            public $foo = '';

            public $bar = '';

            function clear() { $this->clearValidation(); }

            function save() { $this->validate(); }
        })
            ->set('bar', '')
            ->assertHasNoErrors()
            ->set('foo', '')
            ->assertHasErrors(['foo' => 'required'])
            ->call('clear')
            ->assertHasNoErrors()
            ->call('save')
            ->assertHasErrors([
                'foo' => 'required',
                'bar' => 'required',
            ]);
    }

    public function test_rule_attributes_can_contain_multiple_rules()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate(['required', 'min:2', 'max:3'])]
            public $foo = '';
        })
            ->set('foo', '')
            ->assertHasErrors(['foo' => 'required'])
            ->set('foo', '1')
            ->assertHasErrors(['foo' => 'min'])
            ->set('foo', '12345')
            ->assertHasErrors(['foo' => 'max'])
            ->set('foo', 'ok')
            ->assertHasNoErrors()
        ;
    }

    public function test_rule_attributes_can_contain_multiple_rules_userland(): void
    {
        Livewire::test(new class extends TestComponent {
            #[\Livewire\Attributes\Validate('required')]
            #[\Livewire\Attributes\Validate('min:2')]
            #[\Livewire\Attributes\Validate('max:3')]
            public $foo = '';
        })
            ->set('foo', '')
            ->assertHasErrors(['foo' => 'required'])
            ->set('foo', '1')
            ->assertHasErrors(['foo' => 'min'])
            ->set('foo', '12345')
            ->assertHasErrors(['foo' => 'max'])
            ->set('foo', 'ok')
            ->assertHasNoErrors()
        ;
    }

    public function test_rule_attributes_can_be_repeated()
    {
        Livewire::test(new class extends TestComponent {
            #[Validate('required')]
            #[Validate('min:2')]
            #[Validate('max:3')]
            public $foo = '';

            #[
                Validate('sometimes'),
                Validate('max:1')
            ]
            public $bar = '';
        })
            ->set('foo', '')
            ->assertHasErrors(['foo' => 'required'])
            ->set('foo', '1')
            ->assertHasErrors(['foo' => 'min'])
            ->set('foo', '12345')
            ->assertHasErrors(['foo' => 'max'])
            ->set('foo', 'ok')
            ->assertHasNoErrors()
            ->set('bar', '12')
            ->assertHasErrors(['bar' => 'max'])
            ->set('bar', '1')
            ->assertHasNoErrors()
        ;
    }

    public function test_validate_with_rules_property()
    {
        Livewire::test(ComponentWithRulesProperty::class)
            ->set('foo', '')
            ->call('save')
            ->assertHasErrors(['foo' => 'required']);
    }

    public function test_validate_only_with_rules_property()
    {
        Livewire::test(ComponentWithRulesProperty::class)
            ->set('bar', '')
            ->assertHasErrors(['bar' => 'required']);
    }

    public function test_validate_without_rules_property_and_no_args_throws_exception()
    {
        $this->expectException(MissingRulesException::class);

        Livewire::test(ComponentWithoutRulesProperty::class)->call('save');
    }

    public function test_can_validate_collection_properties()
    {
        Livewire::test(ComponentWithRulesProperty::class)
            ->set('foo', 'filled')
            ->call('save')
            ->assertHasErrors('baz.*.foo')
            ->set('baz.0.foo', 123)
            ->set('baz.1.foo', 456)
            ->call('save')
            ->assertHasNoErrors('baz.*.foo');
    }

    public function test_validate_component_properties()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidation');

        $this->assertStringNotContainsString('The foo field is required', $component->html());
        $this->assertStringContainsString('The bar field is required', $component->html());
    }

    public function test_validate_component_properties_with_custom_message()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithCustomMessage');

        $this->assertStringContainsString('Custom Message', $component->html());
    }

    public function test_validate_component_properties_with_custom_message_property()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithMessageProperty');

        $this->assertStringContainsString('Custom Message', $component->html());
    }

    public function test_validate_component_properties_with_custom_attribute_property()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithAttributesProperty');

        $this->assertStringContainsString('The foobar field is required.', $component->html());
        $this->assertStringContainsString('The Items Baz field is required.', $component->html());
    }

    public function test_validate_component_properties_with_custom_attribute()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithCustomAttribute');

        $this->assertStringContainsString('The foobar field is required.', $component->html());
    }

    public function test_validate_component_properties_with_custom_value_property()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runValidationWithCustomValuesProperty');

        $this->assertStringContainsString('The bar field is required when foo is my custom value.', $component->html());
    }

    public function test_validate_nested_component_properties()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runNestedValidation');

        $this->assertStringContainsString('The emails.1 field must be a valid email address.', $component->effects['html']);
    }

    public function test_validate_deeply_nested_component_properties()
    {
        $component = Livewire::test(ForValidation::class);

        $component->runAction('runDeeplyNestedValidation');

        $this->assertStringContainsString('items.1.baz field is required', $component->html());
        $this->assertStringNotContainsString('items.0.baz field is required', $component->html());
    }

    public function test_validation_errors_persist_across_requests()
    {
        $component = Livewire::test(ForValidation::class);

        $component->call('runValidation')
            ->assertSee('The bar field is required')
            ->set('foo', 'bar')
            ->assertSee('The bar field is required');
    }

    public function test_old_validation_errors_are_overwritten_if_new_request_has_errors()
    {
        $component = Livewire::test(ForValidation::class);

        $component->call('runValidation')
            ->set('foo', '')
            ->call('runValidation')
            ->call('$refresh')
            ->assertSee('The foo field is required');
    }

    public function test_old_validation_is_cleared_if_new_validation_passes()
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

    public function test_can_validate_only_a_specific_field_and_preserve_other_validation_messages()
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

    public function test_validating_only_a_specific_field_wont_throw_an_error_if_the_field_doesnt_exist()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('bar', '')
            ->call('runValidationOnlyWithFooRules', 'bar')
            ->assertDontSee('The foo field is required')
            ->assertDontSee('The bar field is required');
    }

    public function test_validating_only_a_specific_field_wont_throw_an_error_if_the_array_key_doesnt_exist()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('items', [])
            ->call('runDeeplyNestedValidationOnly', 'items.0.baz')
            ->assertSee('items.0.baz field is required');
    }

    public function test_can_validate_only_a_specific_field_with_custom_message_property()
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

    public function test_can_validate_only_a_specific_field_with_custom_attributes_property()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->call('runValidationOnlyWithAttributesProperty', 'bar')
            ->assertSee('The foobar field is required.')
            ->call('runValidationOnlyWithAttributesProperty', 'items.*.baz') // Test wildcard works
            ->assertSee('The Items Baz field is required.')
            ->call('runValidationOnlyWithAttributesProperty', 'items.1.baz') // Test specific instance works
            ->assertSee('The Items Baz field is required.')
            ;
    }

    public function test_can_validate_only_a_specific_field_with_deeply_nested_array()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.0.baz')
            ->assertDontSee('items.0.baz field is required')
            ->runAction('runDeeplyNestedValidationOnly', 'items.1.baz')
            ->assertSee('items.1.baz field is required');
    }

    public function test_old_deeply_nested_wildcard_validation_only_is_cleared_if_new_validation_passes()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.*.baz')
            ->assertSee('items.1.baz field is required')
            ->set('items.1.baz', 'blab')
            ->runAction('runDeeplyNestedValidationOnly', 'items.*.baz')
            ->assertDontSee('items.1.baz field is required');
    }

    public function test_old_deeply_nested_wildcard_validation_only_is_cleared_if_new_validation_fails()
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

    public function test_old_deeply_nested_specific_validation_only_is_cleared_if_new_validation_passes()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->runAction('runDeeplyNestedValidationOnly', 'items.1.baz')
            ->assertSee('items.1.baz field is required')
            ->set('items.1.baz', 'blab')
            ->runAction('runDeeplyNestedValidationOnly', 'items.1.baz')
            ->assertDontSee('items.1.baz field is required');
    }

    public function test_old_deeply_nested_specific_validation_only_is_cleared_if_new_validation_fails()
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

    public function test_validation_errors_are_shared_for_all_views()
    {
        $component = Livewire::test(ForValidation::class);

        app('view')->share('errors', $errors = new ViewErrorBag);

        $component
            ->set('bar', '')
            ->call('runValidation')
            ->assertSee('sharedError:The bar field is required');

        $this->assertTrue(app('view')->shared('errors') === $errors);
    }

    public function test_validation_errors_are_shared_when_redirecting_back_to_full_page_component()
    {
        // We apply the web middleware here so that the errors will get shared
        // from the session to the view via ShareErrorsFromSession middleware
        Route::get('/full-page-component', ForValidation::class)->middleware('web');
        Route::post('/non-livewire-form', function () {
            request()->validate(['bar' => 'required']);
        });

        $post = $this
            ->from('/full-page-component')
            ->followingRedirects()
            ->post(url('/non-livewire-form'))
            ->assertSee('sharedError:The bar field is required');
    }

    public function test_multi_word_validation_rules_failing_are_assertable()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'bar123&*(O)')
            ->call('runValidationWithMultiWordRule')
            ->assertHasErrors(['foo' => 'alpha_dash']);
    }

    public function test_class_based_validation_rules_failing_are_assertable()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'barbaz')
            ->call('runValidationWithClassBasedRule')
            ->assertHasErrors(['foo' => ValueEqualsFoobar::class]);
    }

    public function test_can_assert_has_no_errors_when_no_validation_has_failed_and_specific_keys_are_supplied()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'foo')
            ->set('bar', 'bar')
            ->call('runValidation')
            ->assertHasNoErrors(['foo' => 'required']);
    }

    public function test_multi_word_validation_rules_passing_are_assertable()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'foo-bar-baz')
            ->call('runValidationWithMultiWordRule')
            ->assertHasNoErrors(['foo' => 'alpha_dash']);
    }

    public function test_class_based_validation_rules_are_assertable()
    {
        $component = Livewire::test(ForValidation::class);

        $component
            ->set('foo', 'foobar')
            ->call('runValidationWithClassBasedRule')
            ->assertHasNoErrors(['foo' => ValueEqualsFoobar::class]);
    }

    public function test_custom_validation_messages_are_cleared_between_validate_only_validations()
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

    public function test_validation_fails_when_same_rule_is_used_without_matching()
    {
        Livewire::test(ForValidation::class)
            ->set('password', 'supersecret')
            ->call('runSameValidation')
            ->assertSee('The password field must match password confirmation.');
    }

    public function test_validation_passes_when_same_rule_is_used_and_matches()
    {
        Livewire::test(ForValidation::class)
            ->set('password', 'supersecret')
            ->set('passwordConfirmation', 'supersecret')
            ->call('runSameValidation')
            ->assertDontSee('The password field must match password confirmation.');
    }

    public function test_only_data_in_validation_rules_is_returned()
    {
        $component = new ForValidation();
        $component->bar = 'is required';

        $validatedData = $component->runValidationWithoutAllPublicPropertiesAndReturnValidatedData();
        $this->assertSame([
            'bar' => $component->bar,
        ], $validatedData);
    }

    public function test_can_assert_validation_errors_on_errors_thrown_from_custom_validator()
    {
        $component = Livewire::test(ForValidation::class);

        $component->call('failFooOnCustomValidator')->assertHasErrors('plop');
    }

    public function test_can_use_withvalidator_method()
    {
        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSetStrict('count', 0)->call('runValidationWithClosure')->assertSetStrict('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSetStrict('count', 0)->call('runValidationWithThisMethod')->assertSetStrict('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSetStrict('count', 0)->call('runValidateOnlyWithClosure')->assertSetStrict('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSetStrict('count', 0)->call('runValidateOnlyWithThisMethod')->assertSetStrict('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSetStrict('count', 0)->call('clearWithValidatorAfterRunningValidateMethod')->assertSetStrict('count', 1);

        $component = Livewire::test(WithValidationMethod::class);
        $component->assertSetStrict('count', 0)->call('clearWithValidatorAfterRunningValidateOnlyMethod')->assertSetStrict('count', 1);
    }

    public function test_a_set_of_items_will_validate_individually()
    {
        Livewire::test(ValidatesOnlyTestComponent::class, ['image' => 'image', 'imageAlt' => 'This is an image'])
            ->call('runValidateOnly', 'image_alt')
            ->assertHasNoErrors(['image_alt', 'image_url', 'image'])
            ->call('runValidateOnly', 'image_url')
            ->assertHasNoErrors(['image', 'image_url', 'image_alt'])
            ->call('runValidateOnly', 'image')
            ->assertHasNoErrors(['image', 'image_url', 'image_alt']);
    }

    public function test_a_computed_property_is_able_to_validate()
    {
        Livewire::test(ValidatesComputedProperty::class, ['helper' => 10])
            ->call('runValidation')
            ->assertHasNoErrors(['computed'])
            ->set('helper', -1)
            ->call('runValidation')
            ->assertHasErrors(['computed']);

        $this->expectExceptionMessage('No property found for validation: [missing]');
        Livewire::test(ForValidation::class)
                ->call('runValidationOnlyWithMissingProperty', 'missing');

        $this->expectExceptionMessage('No property found for validation: [fail]');
        Livewire::test(ValidatesComputedProperty::class)
            ->call('runValidationRuleWithoutProperty');
    }

    public function test_when_unwrapping_data_for_validation_an_object_is_checked_if_it_is_wireable_first()
    {
        $this->markTestSkipped('Not sure we support setting data on a wireable without requiring a ->set method on the wireable...');

        Livewire::test(ValidatesWireableProperty::class)
            ->call('runValidation')
            ->assertHasErrors('customCollection.0.amount')
            ->set('customCollection.0.amount', 150)
            ->call('runValidation')
            ->assertHasNoErrors('customCollection.0.amount')
            ;
    }

    public function test_adding_validation_error_inside_mount_method()
    {
        Livewire::test(AddErrorInMount::class)
            ->call('addErrors')
            ->assertSee('first error')
            ->assertSee('second error')
            ->assertSee('third error')
            ->assertHasErrors(['first', 'second', 'third'])
            ->call('addFilterErrors')
            ->assertSee('first error')
            ->assertSee('second error')
            ->assertHasErrors(['first', 'second']);

        Livewire::test(AddErrorInMount::class)
            ->assertSee('first error')
            ->assertSee('second error')
            ->assertSee('third error')
            ->assertHasErrors(['first', 'second', 'third'])
            ->call('addFilterErrors')
            ->assertSee('first error')
            ->assertSee('second error')
            ->assertHasErrors(['first', 'second']);
    }
}

class ComponentWithRulesProperty extends TestComponent
{
    public $foo;
    public $bar = 'baz';
    public $baz;

    protected $rules = [
        'foo' => 'required',
        'bar' => 'required',
        'baz.*.foo' => 'numeric',
    ];

    public function mount()
    {
        $this->baz = collect([
            ['foo' => 'a'],
            ['foo' => 'b'],
        ]);
    }

    public function updatedBar()
    {
        $this->validateOnly('bar');
    }

    public function save()
    {
        $this->validate();
    }
}

class ComponentWithoutRulesProperty extends TestComponent
{
    public $foo;

    public function save()
    {
        $this->validate();
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

    public $messages = [];
    public $validationAttributes = [];
    public $validationCustomValues = [];

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

    public function runValidationOnlyWithMissingProperty($field)
    {
        $this->validateOnly($field, [
            'missing' => 'required',
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

    public function runValidationOnlyWithAttributesProperty($field)
    {
        $this->validationAttributes = [
            'bar' => 'foobar',
            'items.*.baz' => 'Items Baz',
        ];

        $this->validateOnly($field, [
            'bar' => 'required',
            'items.*.baz' => 'required',
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
        $this->validationAttributes = [
            'bar' => 'foobar',
            'items.*.baz' => 'Items Baz'
        ];

        $this->validate([
            'bar' => 'required',
            'items.*.baz' => 'required',
        ]);
    }

    public function runValidationWithCustomAttribute()
    {
        $this->validate([
            'bar' => 'required',
        ], [], ['bar' => 'foobar']);
    }

    public function runValidationWithCustomValuesProperty()
    {
        $this->foo = 'my';

        $this->validationCustomValues = [
            'foo' => [
                'my' => 'my custom value',
            ],
        ];

        $this->validate([
            'bar' => 'required_if:foo,my',
        ]);
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

class ValueEqualsFoobar implements \Illuminate\Contracts\Validation\Rule
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

class ValidatesOnlyTestComponent extends TestComponent
{
    public $image = '';
    public $image_alt = '';
    public $image_url = '';

    public $rules = [
        'image' => 'required_without:image_url|string',
        'image_alt' => 'required|string',
        'image_url' => 'required_without:image|string'
    ];

    public function mount($image, $imageAlt, $imageUrl = '')
    {
        $this->image = $image;
        $this->image_alt = $imageAlt;
        $this->image_url = $imageUrl;
    }

    public function runValidation()
    {
        $this->validate();
    }

    public function runValidateOnly($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function runResetValidation()
    {
        $this->resetValidation();
    }
}

class ValidatesComputedProperty extends TestComponent
{
    public $helper;

    public $rules = [
        'computed' => 'required|gte:0'
    ];

    public function prepareForValidation($attributes) {
        $attributes['computed'] = $this->getComputedProperty();
        return $attributes;
    }

    public function getComputedProperty() {
        return $this->helper;
    }

    public function getFailProperty() {
        return 'I will fail';
    }

    public function mount($helper = null)
    {
        $this->helper = $helper;
    }

    public function runValidationRuleWithoutProperty()
    {
        $this->rules['fail'] = 'require|min:1';
        $this->validate();
    }

    public function runValidation()
    {
        $this->validate();
    }

    public function runResetValidation()
    {
        $this->resetValidation();
    }
}

class ValidatesWireableProperty extends TestComponent
{
    public CustomWireableValidationCollection $customCollection;

    public $rules = [
        'customCollection.*.amount' => 'required|gt:100'
    ];

    public function mount()
    {
        $this->customCollection = new CustomWireableValidationCollection([
            new CustomWireableValidationDTO(50),
        ]);
    }

    public function runValidation()
    {
        $this->validate();
    }
}

class CustomWireableValidationCollection extends Collection implements Wireable
{
    public function toLivewire()
    {
        return $this->mapWithKeys(function($dto, $key) {
            return [$key => $dto instanceof CustomWireableValidationDTO ? $dto->toLivewire() : $dto];
        })->all();
    }

    public static function fromLivewire($value)
    {
        return static::wrap($value)
        ->mapWithKeys(function ($dto, $key) {
            return [$key => CustomWireableValidationDTO::fromLivewire($dto)];
        });
    }
}

class CustomWireableValidationDTO implements Wireable
{
    public $amount;

    public function __construct($amount)
    {
        $this->amount = $amount;
    }

    public function toLivewire()
    {
        return [
            'amount' => $this->amount
        ];
    }

    public static function fromLivewire($value)
    {
        return new static(
            $value['amount']
        );
    }
}

class AddErrorInMount extends Component
{
    public function mount()
    {
        $this->addErrors();
    }

    public function addErrors(): void
    {
        $this->addError('first', 'first error');
        $this->addError('second', 'second error');
        $this->addError('third', 'third error');
    }

    public function addFilterErrors(): void
    {
        $this->resetErrorBag();

        $this->addError('first', 'first error');
        $this->addError('second', 'second error');
    }

    public function render()
    {
        return view('show-errors');
    }
}
