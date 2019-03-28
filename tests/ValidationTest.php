<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;
use Livewire\Connection\TestConnectionHandler;

class ValidationTest extends TestCase
{
    /** @test */
    function validate_component_properties()
    {
        [$dom, $id, $serialized] = app(LivewireManager::class)->mount(ForValidation::class);

        $response = TestConnectionHandler::runAction('runValidation', $serialized);

        $this->assertNotContains('foo', $response['dom']);
        $this->assertContains('bar', $response['dom']);
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
