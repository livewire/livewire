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

    public function test_only_one_expired_dialog_is_shown_when_multiple_requests_fail()
    {
        Livewire::visit(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click.async="$refresh" dusk="first">First</button>
                    <button wire:click.async="$refresh" dusk="second">Second</button>

                    @script
                    <script>
                        window.__confirmCount = 0
                        window.confirm = () => { window.__confirmCount++; return false }
                    </script>
                    @endscript
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->waitForLivewire()->click('@first')
            ->waitForLivewire()->click('@second')
            ->assertScript('window.__confirmCount', 1)
        ;
    }

    public function test_polling_stops_after_session_expired()
    {
        Livewire::visit(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <div wire:poll.50ms>A</div>

                    @script
                    <script>
                        window.__requestCount = 0
                        window.confirm = () => false

                        this.interceptMessage(() => {
                            window.__requestCount++
                        })
                    </script>
                    @endscript
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->pause(300)
            ->assertScript('window.__requestCount', 1)
        ;
    }
}
