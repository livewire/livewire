<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () { config()->set('app.debug', false); };
    }

    public function test_application_typeerror_shows_the_error_modal()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function bug(): int { return 'not-an-int'; }

            public function render() { return <<<'HTML'
                <div>
                    <button wire:click="bug" dusk="bug">Broken action</button>
                </div>
                HTML;
            }
        })
            ->click('@bug')
            ->waitFor('#livewire-error')
            ->assertVisible('#livewire-error');
    }

    public function test_invalid_method_name_shows_the_rejection_dialog()
    {
        Livewire::visit(new class extends \Livewire\Component {
            public function render() { return <<<'HTML'
                <div>
                    <button x-on:click="$wire.$call('|')" dusk="invalid">Invalid call</button>
                </div>
                HTML;
            }
        })
            ->click('@invalid')
            ->waitForDialog()
            ->assertDialogOpened("This page has expired.\nWould you like to refresh the page?")
            ->dismissDialog()
            ->assertMissing('#livewire-error');
    }

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
}
