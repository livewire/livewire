<?php

namespace Livewire\Features\SupportEvents;

use Illuminate\Support\Facades\Route;
use Livewire\Attributes\On;
use Livewire\Drawer\Utils;
use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class MercureBrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_listen_for_mercure_event_with_payload()
    {
        Route::get('/dusk/fake-eventsource', function () {
            return Utils::pretendResponseIsFile(__DIR__.'/fake-eventsource.js');
        });

        Livewire::useScriptTagAttributes([
            'data-mercure-url' => 'http://localhost:8888/.well-known/mercure',
        ]);

        Livewire::visit(new class extends Component {
            public $orderId = 0;

            #[On('mercure:orderShipped')]
            function foo($event) {
                $this->orderId = $event['order_id'];
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <span dusk="orderId">{{ $orderId }}</span>

                    <script src="/dusk/fake-eventsource"></script>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@orderId', '0')
        ->waitForLivewire(function ($b) {
            $b->script("sources['http://localhost:8888/.well-known/mercure?topic=orderShipped'].emitMessage({'event': 'orderShipped', 'data': JSON.stringify({'order_id': '1234'})})");
        })
        ->assertSeeIn('@orderId', '1234');
    }
}
