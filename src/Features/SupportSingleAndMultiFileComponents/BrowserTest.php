<?php

namespace Livewire\Features\SupportSingleAndMultiFileComponents;

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            app('livewire.finder')->addLocation(viewPath: __DIR__ . '/fixtures');
        };
    }

    public function test_single_file_component_script()
    {
        Livewire::visit('sfc-scripts')
            ->waitForLivewireToLoad()
            // Pause for a moment to allow the script to be loaded...
            ->pause(100)
            ->assertSeeIn('@foo', 'baz');
    }

    public function test_single_file_component_script_with_js_action()
    {
        Livewire::visit('sfc-scripts-with-js-action')
            ->waitForLivewireToLoad()
            // Pause for a moment to allow the script to be loaded...
            ->pause(100)
            ->assertSeeIn('@foo', 'bar')
            ->click('@set-foo')
            ->assertSeeIn('@foo', 'baz');
    }
}
