<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_register_a_custom_update_endpoint()
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
            })->name('custom');
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

    public function test_failed_requests_fired_without_a_caller_do_not_leave_unhandled_promise_rejections()
    {
        Livewire::visit(new class extends Component {
            public $value = '';

            public function updatedValue()
            {
                throw new \Exception('The update failed.');
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="input" wire:model.live="value">
                    <button dusk="set" wire:click="$set('value', 'bar')">Set</button>
                </div>
                HTML;
            }
        })
            ->tap(fn ($browser) => $browser->script('window.unhandledRejections = 0; window.addEventListener("unhandledrejection", () => window.unhandledRejections++)'))
            ->type('@input', 'foo')
            ->waitFor('#livewire-error')
            ->tap(fn ($browser) => $browser->script("document.getElementById('livewire-error').close()"))
            ->waitUntilMissing('#livewire-error')
            ->click('@set')
            ->waitFor('#livewire-error')
            ->pause(250)
            ->assertScript('window.unhandledRejections', 0);
    }

    public function test_a_failed_request_still_rejects_the_action_promise_for_callers_that_handle_it()
    {
        Livewire::visit(new class extends Component {
            public function explode()
            {
                throw new \Exception('The action failed.');
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button dusk="button" x-on:click="$wire.explode().catch(error => window.caught = error.status)">Explode</button>
                </div>
                HTML;
            }
        })
            ->tap(fn ($browser) => $browser->script('window.unhandledRejections = 0; window.addEventListener("unhandledrejection", () => window.unhandledRejections++)'))
            ->click('@button')
            ->waitFor('#livewire-error')
            ->pause(250)
            ->assertScript('window.caught', 500)
            ->assertScript('window.unhandledRejections', 0);
    }
}
