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
        Livewire::visit(new class extends Component {
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
            ->tap(fn(Browser $browser) => $browser->script([
                'window.renameMe = false',
                "window.Livewire.directive('foo', ({ el, directive, component }) => {
                    window.renameMe = true
                })",
            ]))
            ->assertScript('window.renameMe', false)
            ->waitForLivewire()->click('@refresh')
            ->assertScript('window.renameMe', true)
        ;
    }

    public function test_custom_wire_directive_doesnt_register_wildcard_event_listener(): void
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            public function inc()
            {
                $this->count++;
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <span dusk="target">{{ $count }}</span>

                        <div wire:foo="inc">
                            <button x-on:click="$dispatch('foo')" wire:click="inc" type="button" dusk="button">foo</button>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                window.Livewire.directive('foo', ({ el, directive, component }) => {
                                    // By registering this directive, we should be preventing Livewire
                                    // from registering an event listener for the "foo" event...
                                })
                            })
                        </script>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@target', '0')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@target', '1')
        ;
    }
}
