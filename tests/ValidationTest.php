<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;

class ValidationTest extends TestCase
{
    /** @test */
    function validate_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidation');

        $this->assertNotContains('foo', $component->dom);
        $this->assertContains('bar', $component->dom);
    }

    /** @test */
    function validate_component_properties_with_custom_message()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runValidationWithCustomMessage');

        $this->assertContains('Custom Message', $component->dom);
    }

    /** @test */
    function validate_nested_component_properties()
    {
        $component = app(LivewireManager::class)->test(ForValidation::class);

        $component->runAction('runNestedValidation');

        $this->assertContains('emails', $component->dom);
    }
}

class ForValidation extends LivewireComponent {
    public $foo = 'foo';
    public $bar = '';
    public $emails = ['foo@bar.com', 'invalid-email'];

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

    public function runNestedValidation()
    {
        $this->validate([
            'emails.*' => 'email',
        ]);
    }

    public function render()
    {
        return app('view')->make('dump-errors');
    }
}
