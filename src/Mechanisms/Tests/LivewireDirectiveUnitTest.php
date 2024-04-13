<?php

namespace Livewire\Mechanisms\Tests;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;

// TODO - Change this to \Tests\TestCase
class LivewireDirectiveUnitTest extends \LegacyTests\Unit\TestCase
{
    #[Test]
    public function component_is_loaded_with_blade_directive()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $output = view('render-component', [
            'component' => 'foo',
        ])->render();

        $this->assertStringContainsString('div', $output);
    }

    #[Test]
    public function component_is_loaded_with_blade_directive_by_classname()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);

        $output = view('render-component', [
            'component' => \App\Livewire\Foo::class,
        ])->render();

        $this->assertStringContainsString('div', $output);
    }
}
