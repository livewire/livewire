<?php

namespace Livewire\Features\SupportEvents;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Drawer\Utils;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Blade;
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

    public function test_echo_presence_channel_is_left_when_navigating_away_with_wire_navigate()
    {
        Route::get('/dusk/fake-echo', function () {
            return Utils::pretendResponseIsFile(__DIR__.'/fake-echo.js');
        });

        Route::get('/echo-second-page', function () {
            return Blade::render(<<<'HTML'
                <html>
                <head>
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                </head>
                <body>
                    <div dusk="second-page">Second page</div>
                </body>
                </html>
            HTML);
        })->middleware('web');

        Livewire::visit(new class extends Component {
            #[On('echo-presence:room,here')]
            function here($users) {}

            function render()
            {
                return <<<'HTML'
                <div>
                    <a href="/echo-second-page" wire:navigate dusk="link">Go to second page</a>

                    <script src="/dusk/fake-echo"></script>
                </div>
                HTML;
            }
        })
        ->assertScript('return window.fakeEchoListeners.length', 1)
        ->assertScript('return window.fakeEchoLeftChannels.length', 0)
        ->waitForNavigate(function ($b) {
            $b->click('@link');
        })
        ->waitFor('@second-page')
        ->assertScript('return window.fakeEchoLeftChannels.includes("room")', true)
        ;
    }

    public function test_echo_regular_channel_is_left_when_navigating_away_with_wire_navigate()
    {
        Route::get('/dusk/fake-echo', function () {
            return Utils::pretendResponseIsFile(__DIR__.'/fake-echo.js');
        });

        Route::get('/echo-second-page-2', function () {
            return Blade::render(<<<'HTML'
                <html>
                <head>
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                </head>
                <body>
                    <div dusk="second-page">Second page</div>
                </body>
                </html>
            HTML);
        })->middleware('web');

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
                    <a href="/echo-second-page-2" wire:navigate dusk="link">Go to second page</a>

                    <script src="/dusk/fake-echo"></script>
                </div>
                HTML;
            }
        })
        ->assertScript('return window.fakeEchoListeners.length', 1)
        ->waitForNavigate(function ($b) {
            $b->click('@link');
        })
        ->waitFor('@second-page')
        ->assertScript('return window.fakeEchoListeners.length', 0)
        ;
    }
}
