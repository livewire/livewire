<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_register_a_custom_update_endpoint()
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/update', function () use ($handle) {
                $response = app(HandleRequests::class)->handleUpdate();

                // Override normal Livewire and force the updated count to be "5" instead of 2...
                $response['components'][0]['effects']['html'] = (string) str($response['components'][0]['effects']['html'])->replace(
                    '<span dusk="output">2</span>',
                    '<span dusk="output">5</span>'
                );

                return $response;
            });
        });

        Livewire::visit(new class extends \Livewire\Component {
            public $count = 1;
            function inc() { $this->count++; }
            function render() { return <<<'HTML'
            <div>
                <button wire:click="inc" dusk="target">+</button>
                <span dusk="output">{{ $count }}</span>
            </div>
            HTML; }
        })
        ->assertSeeIn('@output', 1)
        ->waitForLivewire()->click('@target')
        ->assertSeeIn('@output', 5)
        ;
    }

    /** @test */
    public function can_add_additional_headers_to_update_request()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public $header = '';
            function doUpdate() {
                $this->header = request()->headers->get('X-FOO');
            }

            function render() { return <<<'BLADE'
            <div>
                <button wire:click="doUpdate" dusk="target">Update</button>

                <div dusk="header">{{ $header }}</div>

                <script>
                    document.addEventListener('livewire:init', () => {
                        Livewire.addHeaders({
                            'X-FOO': 'bar'
                        });
                    });
                </script>
            </div>
            BLADE; }
        })
            ->waitForLivewire()->click('@target')
            ->assertSeeIn('@header', 'bar')
        ;
    }
}
