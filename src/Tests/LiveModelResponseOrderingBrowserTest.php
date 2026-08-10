<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class LiveModelResponseOrderingBrowserTest extends \Tests\BrowserTestCase
{
    public function test_an_older_live_model_response_cannot_overwrite_a_newer_one()
    {
        Livewire::visit(new class extends Component {
            public string $query = '';

            public function render()
            {
                return <<<'HTML'
                <div>
                    <input dusk="query" wire:model.live.debounce.1ms="query">

                    <span dusk="server-query">{{ $query }}</span>

                    @script
                    <script>
                        let originalFetch = window.fetch
                        let requestNumber = 0

                        window.fetch = async (...args) => {
                            let currentRequest = ++requestNumber
                            let response = await originalFetch(...args)

                            if (currentRequest === 1) {
                                await new Promise(resolve => setTimeout(resolve, 500))
                            }

                            return response
                        }
                    </script>
                    @endscript
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->keys('@query', 'j')
            ->pause(50)
            ->keys('@query', 'k')
            ->pause(700)
            ->assertValue('@query', 'jk')
            ->assertSeeIn('@server-query', 'jk');
    }
}
