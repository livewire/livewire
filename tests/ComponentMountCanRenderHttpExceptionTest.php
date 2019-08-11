<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class ComponentMountCanRenderHttpExceptionTest extends TestCase
{
    /** @test */
    public function mount_method_can_throw_exception()
    {
        Livewire::component('abort', ComponentCanAbort::class);

        Route::livewire('/test', 'abort')->middleware('web');

        $this->get('/test')->assertSee(404);
    }
}

class ComponentCanAbort extends Component
{
    public function mount()
    {
        abort(404);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
