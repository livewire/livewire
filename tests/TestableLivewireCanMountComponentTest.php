<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class TestableLivewireCanMountComponentTest extends TestCase
{
    /** @test */
    public function can_mount_component()
    {
        Livewire::component('foo', ComponentForTestableLivewireMountTest::class);

        Livewire::test('foo')->assertSet('name', 'bar');
    }

    /** @test */
    public function can_mount_component_after_route_test()
    {
        Livewire::component('foo', ComponentForTestableLivewireMountTest::class);

        Route::livewire('/foo', 'foo')->layout('layouts.app-with-bar', [
            'bar' => 'baz',
        ]);

        $this->get('/foo')->assertSee('baz');

        Livewire::test('foo')->assertSet('name', 'bar');
    }
}

class ComponentForTestableLivewireMountTest extends Component
{
    public $name = 'bar';

    public function mount()
    {
        $this->name = 'bar';
    }

    public function render()
    {
        return view('show-name');
    }
}
