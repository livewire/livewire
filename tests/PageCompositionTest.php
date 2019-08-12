<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class PageCompositionTest extends TestCase
{
    /** @test */
    public function composes_page_with_a_single_component()
    {
        Livewire::component('foo', ComposableComponent::class);

        Route::livewire('/test/{name}', 'foo')->middleware('web');

        $this
            ->get('/test/name-from-route')
            ->assertSee('wire:name="foo"')
            ->assertSeeText('name-from-route');
    }

    /** @test */
    public function composes_page_with_multiple_components()
    {
        Livewire::component('foo', ComposableComponent::class);
        Livewire::component('bar', ComposableComponent::class);

        Route::livewire('/test/{name}', ['foo', 'bar'])->middleware('web');

        $this
            ->get('/test/name-from-route')
            ->assertSeeInOrder(['wire:name="foo"', 'wire:name="bar"'])
            ->assertSeeText('name-from-route');
    }
}

class ComposableComponent extends Component
{
    public function mount(string $name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return app('view')->make('show-name')->with('name', $this->name);
    }
}
