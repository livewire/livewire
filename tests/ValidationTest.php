<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class ValidationTest extends TestCase
{
    /** @test */
    public function validate_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidation');

        $this->assertStringNotContainsString('The foo field is required', $component->dom);
        $this->assertStringContainsString('The bar field is required', $component->dom);
    }

    /** @test */
    public function validate_component_properties_with_custom_message()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidationWithCustomMessage');

        $this->assertStringContainsString('Custom Message', $component->dom);
    }

    /** @test */
    public function validate_component_properties_with_custom_attribute()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidationWithCustomAttribute');

        $this->assertStringContainsString('The foobar field is required.', $component->dom);
    }

    /** @test */
    public function validate_nested_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runNestedValidation');

        $this->assertStringContainsString('emails.1 must be a valid email address.', $component->dom);
    }

    /** @test */
    public function validate_deeply_nested_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runDeeplyNestedValidation');

        $this->assertStringContainsString('items.1.baz field is required', $component->dom);
        $this->assertStringNotContainsString('items.0.baz field is required', $component->dom);
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
