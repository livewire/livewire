<?php

namespace Livewire\Mechanisms\Tests;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\Livewire;

// TODO - Change this to \Tests\TestCase
class LivewireDirectiveTest extends \LegacyTests\Unit\TestCase
{
    /** @test */
    public function component_is_loaded_with_blade_directive()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $output = view('render-component', [
            'component' => 'foo',
        ])->render();

        $this->assertStringContainsString('div', $output);
    }
}