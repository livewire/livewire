<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;

class ComponentCanSkipRender extends TestCase
{
    /** @test */
    public function a_livewire_component_can_skip_render()
    {
        Livewire::test(ComponentWithSkipRender::class);
    }
}

class ComponentWithSkipRender extends Component
{
    public function mount()
    {
        $this->skipRender();
    }
}