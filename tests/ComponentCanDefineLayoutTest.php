<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\LivewireManager;

class ComponentCanDefineLayoutTest extends TestCase
{
    /** @test */
    public function livewire_components_can_configure_layouts()
    {

        // $component = app(LivewireManager::class)->test(ComponentWithLayout::class);

        // $component->assertSee('baz');

        Livewire::component('foobar', ComponentWithLayout::class);

        Route::livewire('/foobar', 'foobar');

        $this->get('/foobar')->assertSee('baz');
    }
}


class ComponentWithLayout extends Component
{

    public function layout()
    {
        return 'layouts.app-with-bar';
    }

    public function layoutParams()
    {
        return [
            'bar' => 'baz'
        ];
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
