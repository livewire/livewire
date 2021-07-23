<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentLayoutTest extends TestCase
{
    /** @test */
    public function can_extend_a_blade_layout()
    {
        Livewire::component(ComponentWithExtendsLayout::class);

        Route::get('/foo', ComponentWithExtendsLayout::class);

        $this->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_set_custom_section()
    {
        Livewire::component(ComponentWithCustomSection::class);

        Route::get('/foo', ComponentWithCustomSection::class);

        $this->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_set_custom_layout()
    {
        Livewire::component(ComponentWithCustomLayout::class);

        Route::get('/foo', ComponentWithCustomLayout::class);

        $this->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_set_custom_slot_for_a_layout()
    {
        Livewire::component(ComponentWithCustomSlotForLayout::class);

        Route::get('/foo', ComponentWithCustomSlotForLayout::class);

        $this->withoutExceptionHandling()->get('/foo')->assertSee('baz');
    }

    /** @test */
    public function can_load_dynamic_layout_properties()
    {
        Livewire::component(ComponentWithClassBasedComponentLayout::class);

        Route::get('/foo', ComponentWithClassBasedComponentLayout::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('bar')
            ->assertSee('baz');
    }

    /** @test */
    public function can_show_the_params()
    {
        Livewire::component(ComponentWithCustomParams::class);

        Route::get('/foo', ComponentWithCustomParams::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('foo');
    }

    /** @test */
    public function can_show_params_with_custom_layout()
    {
        Livewire::component(ComponentWithCustomParamsAndLayout::class);

        Route::get('/foo', ComponentWithCustomParamsAndLayout::class);

        $this->withoutExceptionHandling()->get('/foo')
            ->assertSee('livewire');
    }
}

class ComponentWithExtendsLayout extends Component
{
    public function render()
    {
        return view('null-view')->extends('layouts.app-extends', [
            'bar' => 'baz'
        ]);
    }
}

class ComponentWithCustomSection extends Component
{
    public $name = 'baz';

    public function render()
    {
        return view('show-name')->extends('layouts.app-custom-section')->section('body');
    }
}

class ComponentWithCustomLayout extends Component
{
    public function render()
    {
        return view('null-view')->layout('layouts.app-with-bar', [
            'bar' => 'baz'
        ]);
    }
}

class ComponentWithCustomSlotForLayout extends Component
{
    public $name = 'baz';

    public function render()
    {
        return view('show-name')->layout('layouts.app-custom-slot')->slot('main');
    }
}

class ComponentWithClassBasedComponentLayout extends Component
{
    public function render()
    {
        return view('null-view')->layout(\Tests\AppLayout::class, [
            'bar' => 'baz'
        ]);
    }
}

class ComponentWithCustomParams extends Component
{
    public function render()
    {
        return view('null-view')->layoutData([
            'slot' => 'foo'
        ]);
    }
}

class ComponentWithCustomParamsAndLayout extends Component
{
    public function render()
    {
        return view('null-view')->layout('layouts.data-test')->layoutData([
            'title' => 'livewire',
        ]);
    }
}
