<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class RouterMacrosTest extends TestCase
{
    /** @test */
    public function it_resolves_the_mount_parameters()
    {
        Livewire::component('foo', ComponentWithNoBehavior::class);

        Route::livewire('/foo', 'foo')->layout('layouts.app-with-bar', [
            'bar' => 'baz',
        ]);

        $this->get('/foo')->assertSee('baz');
    }
}

class ComponentWithNoBehavior extends Component
{
    public function render()
    {
        return view('null-view');
    }
}
