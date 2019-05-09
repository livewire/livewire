<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class LifecycleHooksTest extends TestCase
{
    /** @test */
    function mount_hook()
    {
        $component = app(LivewireManager::class)->test(ForLifecycleHooks::class);

        $this->assertEquals([
            'mount' => true,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->instance->lifecycles);

        $component->runAction('$refresh');

        $this->assertEquals([
            'mount' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->instance->lifecycles);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals([
            'mount' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => true,
            'updatedFoo' => true,
        ], $component->instance->lifecycles);
    }
}

class ForLifecycleHooks extends Component {
    public $foo;
    public $lifecycles = [
        'mount' => false,
        'updating' => false,
        'updated' => false,
        'updatingFoo' => false,
        'updatedFoo' => false,
    ];

    public function mount()
    {
        $this->lifecycles['mount'] = true;
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
