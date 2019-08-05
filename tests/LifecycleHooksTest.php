<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\LivewireManager;
use Illuminate\Support\Facades\Route;

class LifecycleHooksTest extends TestCase
{
    /** @test */
    public function mount_hook()
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

    /** @test */
    public function mount_hook_receives_route_model_bindings()
    {
        Livewire::component('foo', HasRouteModelBindingForMountHook::class);

        Route::livewire('/test', 'foo');

        $this->get('/test')->assertSee('output-from-method-binding');
    }

    /** @test */
    public function mount_hook_method_receives_custom_bindings()
    {
        Livewire::component('foo', HasRouteModelBindingForMountHook::class);

        Route::livewire('/test/{foo}', 'foo')->middleware('web');

        Route::bind('foo', function ($value) {
            $something = new ToBeBound;
            $something->input = $value;

            return $something;
        });

        $this->get('/test/should-show-up')->assertSee('should-show-up');
    }
}

class ForLifecycleHooks extends Component
{
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

class ToBeBound
{
    public $input = 'output-from-method-binding';

    public function output()
    {
        return $this->input;
    }
}

class HasRouteModelBindingForMountHook extends Component
{
    public $output;

    public function mount(ToBeBound $toBeBound)
    {
        $this->output = $toBeBound->output();
    }

    public function render()
    {
        return app('view')->make('show-name', ['name' => $this->output]);
    }
}
