<?php

namespace Tests;

use Illuminate\Support\ViewErrorBag;
use Livewire\Component;
use Livewire\LivewireManager;

class ValidationTest extends TestCase
{
    /** @test */
    public function validate_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidation');

        $this->assertStringNotContainsString('The foo field is required', $component->payload['dom']);
        $this->assertStringContainsString('The bar field is required', $component->payload['dom']);
    }

    /** @test */
    public function validate_component_properties_with_custom_message()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidationWithCustomMessage');

        $this->assertStringContainsString('Custom Message', $component->payload['dom']);
    }

    /** @test */
    public function validate_component_properties_with_custom_attribute()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidationWithCustomAttribute');

        $this->assertStringContainsString('The foobar field is required.', $component->payload['dom']);
    }

    /** @test */
    public function validate_nested_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runNestedValidation');

        $this->assertStringContainsString('emails.1 must be a valid email address.', $component->payload['dom']);
    }

    /** @test */
    public function validate_deeply_nested_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runDeeplyNestedValidation');

        $this->assertStringContainsString('items.1.baz field is required', $component->payload['dom']);
        $this->assertStringNotContainsString('items.0.baz field is required', $component->payload['dom']);
    }

    /** @test */
    public function validation_errors_persist_across_requests()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->call('runValidation')
            ->assertSee('The bar field is required')
            ->set('foo', 'bar')
            ->assertSee('The bar field is required');
    }

    /** @test */
    public function old_validation_errors_are_overwritten_if_new_request_has_errors()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->call('runValidation')
            ->set('foo', '')
            ->call('runValidation')
            ->call('$refresh')
            ->assertSee('The foo field is required');
    }

    /** @test */
    public function old_validation_is_cleared_if_new_validation_passes()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

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
        $component = app(LivewireManager::class)->test(ForValidation::class);

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
    public function validation_errors_are_shared_for_all_views()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        app('view')->share('errors', $errors = new ViewErrorBag);

        $component
            ->set('bar', '')
            ->call('runValidation')
            ->assertSee('sharedError:The bar field is required');

        $this->assertTrue(app('view')->shared('errors') === $errors);
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

    public function runValidation()
    {
        $this->validate([
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function runValidationOnly($field)
    {
        $this->validateOnly($field, [
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function runValidationWithCustomMessage()
    {
        $this->validate([
            'bar' => 'required',
        ], ['required' => 'Custom Message']);
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

    public function render()
    {
        return app('view')->make('dump-errors');
    }
}
