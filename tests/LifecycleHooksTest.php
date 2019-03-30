<?php

namespace Tests;

use Livewire\LivewireComponent;
use Livewire\LivewireManager;

class LifecycleHooksTest extends TestCase
{
    /** @test */
    function created_hook()
    {
        $component = app(LivewireManager::class)->test(ForLifecycleHooks::class);

        $this->assertEquals([
            'created' => true,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->instance->lifecycles);

        $component->runAction('$refresh');

        $this->assertEquals([
            'created' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->instance->lifecycles);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals([
            'created' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => true,
            'updatedFoo' => true,
        ], $component->instance->lifecycles);
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
