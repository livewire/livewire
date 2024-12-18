<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class WirePropertiesBrowserTest extends \Tests\BrowserTestCase
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

    public function test_dollar_wire_hook_not_leaks_after_element_is_removed_from_dom()
    {
        Livewire::visit(new class extends Component
        {
            public function render()
            {
                return <<<'HTML'
                    <div>
                        <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>

                        <div x-data="{ hookCount: 0 }">
                            <div x-init="() => {
                                $wire.$hook('commit', ({ component, commit, respond, succeed, fail }) => {
                                    hookCount++
                                })
                            }" wire:replace.self>
                                <span x-text="hookCount" dusk="hookCount"></span>
                            </div>
                        </div>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@hookCount', '0')
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@hookCount', '1')
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@hookCount', '2');
    }

    public function test_dollar_wire_watch_not_leaks_after_element_is_removed_from_dom()
    {
        Livewire::visit(new class extends Component
        {
            public string $foo = '';

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <input type="text" dusk="foo" wire:model.live="foo" />

                        <div x-data="{ watchCount: 0 }">
                            <div x-init="() => {
                                $wire.$watch('foo', () => {
                                    watchCount++
                                })
                            }" wire:replace.self>
                                <span x-text="watchCount" dusk="watchCount"></span>
                            </div>
                        </div>
                    </div>
                HTML;
            }
        })
            ->assertSeeIn('@watchCount', '0')
            ->waitForLivewire()->type('@foo', 'ab')
            ->assertSeeIn('@watchCount', '2')
            ->waitForLivewire()->type('@foo', 'ab')
            ->assertSeeIn('@watchCount', '4');
    }
}
