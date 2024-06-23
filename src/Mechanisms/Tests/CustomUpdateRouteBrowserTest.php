<?php

namespace Livewire\Mechanisms\Tests;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class CustomUpdateRouteBrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            // This would normally be done in something like middleware
            URL::defaults(['tenant' => 'custom-tenant']);

            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/{tenant}/livewire/update', $handle)->name('tenant.livewire.update');
            });

            // Doesn't seem to be needed in real applications, but is needed in tests
            app('router')->getRoutes()->refreshNameLookups();

            Route::prefix('/{tenant}')->group(function () {
                Route::get('/page', function ($tenant) {
                    return (app('livewire')->new('test'))();
                })->name('tenant.page');
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
                            <h1 dusk="count">Count: {{ $count }}</h1>
                            <button wire:click="increment" dusk="button">Increment</button>
                            <p>Tenant: {{ request()->route()->parameter('tenant') }}</p>
                            <p>Route: {{ request()->route()->getName() }}</p>
                        </div>
                    HTML;
                }
            });
        };
    }

    public function test_can_use_a_custom_update_route_with_a_uri_segment()
    {
        $this->browse(function (\Laravel\Dusk\Browser $browser) {
            $browser
                ->visit('/custom-tenant/page')
                ->assertSee('Count: 0')
                ->assertSee('Tenant: custom-tenant')
                ->assertSee('Route: tenant.page')
                ->waitForLivewire()
                ->click('button')
                ->assertSee('Count: 1')
                ->assertSee('Tenant: custom-tenant')
                ->assertSee('Route: tenant.livewire.update');
        });
    }
}
