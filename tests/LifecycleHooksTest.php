<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;
use Livewire\Connection\TestConnectionHandler;
use Livewire\Connection\ComponentHydrator;

class LifecycleHooksTest extends TestCase
{
    /** @test */
    function created_hook()
    {
        [$dom, $id, $serialized] = app(LivewireManager::class)->mount(ForLifecycleHooks::class);

        $this->assertEquals([
            'created' => true,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], ComponentHydrator::hydrate($serialized)->lifecycles);

        $response = TestConnectionHandler::runAction('$refresh', $serialized);

        $this->assertEquals([
            'created' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], ComponentHydrator::hydrate($response['serialized'])->lifecycles);

        $response = TestConnectionHandler::updateProperty('foo', 'bar', $serialized);

        $this->assertEquals([
            'created' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => true,
            'updatedFoo' => true,
        ], ComponentHydrator::hydrate($response['serialized'])->lifecycles);
    }
}

class ForLifecycleHooks extends LivewireComponent {
    public $foo;
    public $lifecycles = [
        'created' => false,
        'updating' => false,
        'updated' => false,
        'updatingFoo' => false,
        'updatedFoo' => false,
    ];

    public function created()
    {
        $this->lifecycles['created'] = true;
    }

    public function updating()
    {
        assert($this->lifecycles['updated'] === false);

        $this->lifecycles['updating'] = true;
    }

    public function updated()
    {
        $this->lifecycles['updated'] = true;
    }

    public function updatingFoo($value)
    {
        assert(is_null($this->foo));
        assert($value === 'bar');

        $this->lifecycles['updatingFoo'] = true;
    }

    public function updatedFoo()
    {
        assert($this->foo === 'bar');

        $this->lifecycles['updatedFoo'] = true;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
