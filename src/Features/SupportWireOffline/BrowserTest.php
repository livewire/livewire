<?php

namespace Livewire\Features\SupportWireOffline;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_offline_directives_read_the_connection_state_when_mounted()
    {
        Livewire::visit(new class extends Component {
            public function render()
            {
                return <<<'HTML'
                    <div x-data="{ open: false }">
                        <button x-on:click="open = ! open" dusk="toggle">Toggle</button>

                        <template x-if="open">
                            <section dusk="panel">
                                <span wire:offline dusk="indicator">Offline</span>
                                <button wire:offline.attr="disabled" dusk="save">Save</button>
                            </section>
                        </template>
                    </div>
                HTML;
            }
        })
            ->click('@toggle')
            ->waitFor('@panel')
            ->assertMissing('@indicator')
            ->assertEnabled('@save')
            ->click('@toggle')
            ->waitUntilMissing('@panel')
            ->tap(fn ($browser) => $browser->script('Object.defineProperty(navigator, "onLine", { value: false, configurable: true })'))
            ->offline()
            ->click('@toggle')
            ->waitFor('@panel')
            ->assertVisible('@indicator')
            ->assertDisabled('@save')
            ->tap(fn ($browser) => $browser->script('delete navigator.onLine'))
            ->online()
            ->assertMissing('@indicator')
            ->assertEnabled('@save');
    }
}
