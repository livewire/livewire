<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class JavascriptHooksBrowserTest extends \Tests\BrowserTestCase
{
    public function test_dollar_wire_hook_works_with_inline_alpine_component()
    {
        Livewire::visit(new class () extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>

                    <div x-data="{
                        commitFired: false,
                        init: function() {
                            this.$wire.$hook('commit', ({ component, commit, respond, succeed, fail }) => {
                                this.commitFired = true
                            })
                        }
                    }">
                        <span x-text="commitFired" dusk="commitFired"></span>
                    </div>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@commitFired', 'false')
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@commitFired', 'true');
    }

    public function test_dollar_wire_hook_works_with_alpine_data_component()
    {
        Livewire::visit(new class () extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
                    
                    <div x-data="myComponent">
                        <span x-text="commitFired" dusk="commitFired"></span>
                    </div>
                        
                    <script>
                        document.addEventListener('alpine:init', () => {
                            Alpine.data('myComponent', () => ({
                                commitFired: false,
                                init() {
                                    this.$wire.$hook('commit', ({ component, commit, respond, succeed, fail }) => {
                                        this.commitFired = true
                                    })
                                }
                            }))
                        })
                    </script>
                </div>
                HTML;
            }
        })
            ->assertSeeIn('@commitFired', 'false')
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@commitFired', 'true');
    }
}
