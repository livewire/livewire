<?php

namespace LegacyTests\Browser\Extensions;

use Laravel\Dusk\Browser;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class Test extends BrowserTestCase
{
    public function test_custom_wire_directive(): void
    {
        Livewire::visit(new class extends Component
        {
            public $count = 0;

            public function render()
            {
                $this->count++;

                return <<<'HTML'
                    <div>
                        <button wire:click="$refresh" dusk="refresh">refresh</button>

                        @if ($count > 1)
                            <button wire:foo>foo</button>
                        @endif
                    </div>
                HTML;
            }
        })
            ->tap(fn (Browser $browser) => $browser->script([
                'window.renameMe = false',
                "window.Livewire.directive('foo', ({ el, directive, component }) => {
                    window.renameMe = true
                })",
            ]))
            ->assertScript('window.renameMe', false)
            ->waitForLivewire()->click('@refresh')
            ->assertScript('window.renameMe', true);
    }
}
