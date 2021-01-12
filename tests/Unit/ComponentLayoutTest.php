<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class ComponentLayoutTest extends TestCase
{
    /** @test */
    public function can_set_slot_value_of_a_layout()
    {
        Livewire::component(ComponentWithSettingSlotValue::class);

        Route::get('/foo', ComponentWithSettingSlotValue::class);

        $this->get('/foo')->assertSee('baz');
    }
}

class ComponentWithSettingSlotValue extends Component
{
    public function render()
    {
        return view('null-view')->layout('layouts.app-with-bar')
            ->setSlot('bar', 'baz');
    }
}
