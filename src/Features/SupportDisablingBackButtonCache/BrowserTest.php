<?php

namespace Livewire\Features\SupportDisablingBackButtonCache;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            Route::get('/elsewhere', fn () => 'elsewhere')->middleware('web');
        };
    }

    public function test_wire_model_picks_up_values_the_browser_restores_after_going_back()
    {
        Livewire::visit(new class extends Component {
            public $email = '';

            public $subscribed = false;

            public $saved = '';

            public function save()
            {
                $this->saved = $this->email.'|'.($this->subscribed ? 'subscribed' : 'not subscribed');
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model="email" dusk="email">
                    <input type="checkbox" wire:model="subscribed" dusk="subscribed">
                    <button wire:click="save" dusk="save">Save</button>
                    <span dusk="saved">{{ $saved }}</span>
                    <a href="/elsewhere" dusk="leave">Leave</a>
                </div>
                HTML;
            }
        })
            ->type('@email', 'restored@example.com')
            ->check('@subscribed')
            ->click('@leave')
            ->waitForText('elsewhere')
            ->back()
            ->waitForLivewireToLoad()
            // The page was reloaded and the browser refilled the fields on its own...
            ->assertInputValue('@email', 'restored@example.com')
            ->assertChecked('@subscribed')
            // ...and the component state has to match what the fields show.
            ->waitForLivewire()->click('@save')
            ->assertSeeIn('@saved', 'restored@example.com|subscribed');
    }

    public function test_a_regular_page_load_does_not_send_updates_for_untouched_fields()
    {
        Livewire::visit(new class extends Component {
            public $name = 'Taylor';

            public $updates = 0;

            public function updated()
            {
                $this->updates++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input type="text" wire:model.live="name" dusk="name">
                    <span dusk="updates">{{ $updates }}</span>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->assertInputValue('@name', 'Taylor')
            ->pause(300)
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@updates', '0');
    }
}
