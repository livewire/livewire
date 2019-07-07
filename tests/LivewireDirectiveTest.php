<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;

class LivewireDirectiveTest extends TestCase
{
    /** @test */
    function component_is_loaded_with_blade_directive()
    {
        Artisan::call('make:livewire foo');

        $output = view('render-component', [
            'component' => 'foo',
        ])->render();

        $this->assertContains('div', $output);
    }
}
