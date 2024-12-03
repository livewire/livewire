<?php

namespace Livewire\Features\SupportEvents;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Drawer\Utils;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Route;

class EchoBrowserTest extends BrowserTestCase
{
    public function test_can_listen_for_echo_event()
    {
        Route::get('/dusk/fake-echo', function () {
            return Utils::pretendResponseIsFile(__DIR__.'/fake-echo.js');
        });

        Livewire::visit(new class extends Component {
            public $count = 0;

            #[On('echo:orders,OrderShipped')]
            function foo() {
                $this->count++;
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="count">{{ $count }}</span>

                    <script src="/dusk/fake-echo"></script>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')
        ->waitForLivewire(function ($b) {
            $b->script("window.Echo.fakeTrigger({ channel: 'orders', event: 'OrderShipped' })");
        })
        ->assertSeeIn('@count', '1');
    }

    public function test_can_listen_for_echo_event_with_payload()
    {
        Route::get('/dusk/fake-echo', function () {
            return Utils::pretendResponseIsFile(__DIR__.'/fake-echo.js');
        });

        Livewire::visit(new class extends Component {
            public $orderId = 0;

            #[On('echo:orders,OrderShipped')]
            function foo($event) {
                $this->orderId = $event['order_id'];
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="orderId">{{ $orderId }}</span>

                    <script src="/dusk/fake-echo"></script>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@orderId', '0')
        ->waitForLivewire(function ($b) {
            $b->script("window.Echo.fakeTrigger({ channel: 'orders', event: 'OrderShipped', payload: { order_id : 1234 }})");
        })
        ->assertSeeIn('@orderId', '1234');
    }

    // This test asserts agains a scenario that fails silently. Therefore I can't easily make a test for it.
    // I'm leaving it here as a playground for the issue (that has been mostly resolved)...
    // public function test_echo_listeners_are_torn_down_when_navigating_pages_using_wire_navigate()
    // {
    //     Route::get('/dusk/fake-echo', function () {
    //         return Utils::pretendResponseIsFile(__DIR__.'/fake-echo.js');
    //     });

    //     Route::get('/second-page', function (){
    //         return Blade::render(<<<'HTML'
    //             <x-layouts.app>
    //                 Second page

    //                 @livewireScripts
    //             </x-layouts.app>
    //         HTML);
    //     })->middleware('web');

    //     Livewire::visit(new class extends Component {
    //         public $count = 0;

    //         #[On('echo:orders,OrderShipped')]
    //         function foo() {
    //             $this->count++;
    //         }

    //         function render()
    //         {
    //             return <<<'HTML'
    //             <div>
    //                 <span dusk="count">{{ $count }}</span>

    //                 <a href="/second-page" wire:navigate>yoyoyo</a>

    //                 <script src="/dusk/fake-echo"></script>
    //             </div>
    //             HTML;
    //         }
    //     })
    //     ->tinker()
    //     ;
    // }
}
