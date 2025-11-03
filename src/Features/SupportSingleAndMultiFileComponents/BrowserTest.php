<?php

namespace Livewire\Features\SupportSingleAndMultiFileComponents;

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            app('livewire.finder')->addLocation(path: __DIR__ . '/fixtures');
        };
    }

    public function test_single_file_component_script()
    {
        Livewire::visit('sfc-scripts')
            ->waitForLivewireToLoad()
            ->assertSeeIn('@foo', 'baz');
    }
}