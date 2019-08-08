<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;

class LifecycleHooksTest extends TestCase
{
    /** @test */
    public function mount_hook()
    {
        $component = app(LivewireManager::class)->test(ForLifecycleHooks::class);

        $this->assertEquals([
            'mount' => true,
            'hydrate' => false,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->instance->lifecycles);

        $component->runAction('$refresh');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'updating' => false,
            'updated' => false,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->instance->lifecycles);

        $component->updateProperty('baz', 'bing');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => false,
            'updatedFoo' => false,
        ], $component->instance->lifecycles);

        $component->updateProperty('foo', 'bar');

        $this->assertEquals([
            'mount' => true,
            'hydrate' => true,
            'updating' => true,
            'updated' => true,
            'updatingFoo' => true,
            'updatedFoo' => true,
        ], $component->instance->lifecycles);
    }
}

class ForLifecycleHooks extends Component
{
    public $foo;
    public $baz;
    public $lifecycles = [
        'mount' => false,
        'hydrate' => false,
        'updating' => false,
        'updated' => false,
        'updatingFoo' => false,
        'updatedFoo' => false,
    ];

    public function mount()
    {
        $this->lifecycles['mount'] = true;
    }

    public function hydrate()
    {
        $this->lifecycles['hydrate'] = true;
    }

    public function updating($name, $value)
    {
        assert($name === 'foo' || $name === 'baz');
        assert($value === 'bar' || $value === 'bing');

        $this->lifecycles['updating'] = true;
    }

    public function updated($name, $value)
    {
        assert($name === 'foo' || $name === 'baz');
        assert($value === 'bar' || $value === 'bing');

        $this->lifecycles['updated'] = true;
    }

    public function updatingFoo($value)
    {
        assert(is_null($this->foo));
        assert($value === 'bar');

        $this->lifecycles['updatingFoo'] = true;
    }

    public function updatedFoo($value)
    {
        assert($this->foo === 'bar');
        assert($value === 'bar');

        $this->lifecycles['updatedFoo'] = true;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
