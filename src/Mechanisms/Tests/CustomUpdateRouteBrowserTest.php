<?php

namespace Livewire\Mechanisms\Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class CustomUpdateRouteBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            Route::prefix('/{tenant}')->group(function () {
                Livewire::setUpdateRoute(function ($handle) {
                    return Route::post('/livewire/update', $handle);
                });

                Route::get('/page', function ($tenant) {
                    return (app('livewire')->new('test'))();
                });
            });

            Livewire::component('test', new class extends Component {
                public $count = 0;

                function increment()
                {
                    $this->count++;
                }

                public function render() {
                    return <<<'HTML'
                        <div>
                            <h1 dusk="count">{{ $count }}</h1>
                            <button wire:click="increment" dusk="button">Increment</button>
                        </div>
                    HTML;
                }
            });
        };
    }

    /** @test */
    public function can_use_a_custom_update_route_with_a_uri_segment()
    {
        $this->browse(function ($browser) {
            $browser
                ->visit('/custom-tenant/page')
                ->tinker();
        });
    }
}
