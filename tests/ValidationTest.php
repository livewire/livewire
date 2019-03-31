<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;

class ValidationTest extends TestCase
{
    /** @test */
    function validate_component_properties()
    {
        $component = app(LivewireManager::class)->test( ForValidation::class);

        $component->runAction('runValidation');

        $this->assertNotContains('foo', $component->dom);
        $this->assertContains('bar', $component->dom);
    }
}

class ForValidation extends LivewireComponent {
    public $foo = 'foo';
    public $bar = '';

    public function runValidation()
    {
        $this->validate([
            'foo' => 'required',
            'bar' => 'required',
        ]);
    }

    public function render()
    {
        return app('view')->make('dump-errors');
    }
}
