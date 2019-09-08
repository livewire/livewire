<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Artisan;

class LivewireDirectivesTest extends TestCase
{
    /** @test */
    public function component_is_loaded_with_blade_directive()
    {
        Artisan::call('make:livewire foo');

        $output = view('render-component', [
            'component' => 'foo',
        ])->render();

        $this->assertContains('div', $output);
    }

    /** @test */
    public function this_directive_returns_javascript_component_object_string()
    {
        Livewire::test(ComponentForTestingDirectives::class)
            ->assertDontSee('@this')
            ->assertSee('window.livewire.find(');
    }

    /** @test */
    public function this_directive_isnt_registered_outside_of_livewire_component()
    {
        $output = view('this-directive')->render();

        $this->assertContains('@this', $output);
    }
}

class ComponentForTestingDirectives extends Component
{
    public function render()
    {
        return view('this-directive');
    }
}
