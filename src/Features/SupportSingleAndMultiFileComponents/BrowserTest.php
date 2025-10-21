<?php

namespace Livewire\Features\SupportSingleAndMultiFileComponents;

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            app('livewire.finder')->addComponent('sfc-scripts', path: __DIR__ . '/fixtures/sfc-scripts.blade.php');

            Route::livewire('/sfc-scripts', 'sfc-scripts');
        };
    }

    public function test_single_file_component_script()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/sfc-scripts')
                ->waitForLivewireToLoad()
                ->assertSeeIn('@foo', 'baz')
            ;
        });
    }
}