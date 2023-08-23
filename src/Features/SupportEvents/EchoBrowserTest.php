<?php

namespace Livewire\Features\SupportEvents;

use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use Livewire\Drawer\Utils;
use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class EchoBrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_listen_for_echo_event()
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

    /** @test */
    public function can_listen_for_echo_event_with_payload()
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
}
