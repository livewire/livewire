<?php

namespace Livewire\Features\SupportReleaseTokens;

use Livewire\Component;
use Livewire\Livewire;

use function Livewire\on;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            on('request', function ($request) {
                ReleaseToken::$LIVEWIRE_RELEASE_TOKEN = 'bob';
            });
        };
    }

    public function test_if_release_token_has_changed_it_should_show_the_page_expired_dialog()
    {
        Livewire::visit(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->click('@refresh')
            // Wait for Livewire to respond, but dusk helper won't
            // work as dialog box is stopping further execution
            ->pause(300)
            ->assertDialogOpened("This page has expired.\nWould you like to refresh the page?")
            // Dismiss dialog so next tests run
            ->dismissDialog()
        ;
    }
}
