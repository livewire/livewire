<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

/** @group morphing */
class AlpineMorphingBrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function component_with_custom_directive_keeps_state_after_cloning()
    {
        Livewire::visit(new class extends Component {
            public int $counter = 0;

            function render() {
                return <<<'HTML'
                <div>
                    <div x-counter wire:model.live='counter'>
                        <span dusk='counter' x-text="__counter"></span>
                        <button x-counter:increment dusk='increment'>+</button>
                    </div>

                    <script>
                        document.addEventListener('alpine:init', () => {
                            Alpine.directive('counter', function (el, { value }) {
                                if (value === 'increment') {
                                    Alpine.bind(el, {
                                        'x-on:click.prevent'() {
                                            this.$data.__counter++;
                                        }
                                    })
                                } else if (! value) {
                                    Alpine.bind(el, {
                                        'x-modelable': '__counter',
                                        'x-data'() {
                                            return {
                                                __counter: 0
                                            }
                                        }
                                    })
                                }
                            })
                        })
                    </script>
                </div>
                HTML;
            }
        })
            ->waitForLivewire()->click('@increment')
            ->assertInputValue('@counter', '1')
        ;
    }

    /** @test */
    public function deep_alpine_state_is_preserved_when_morphing_with_uninitialized_livewire_html()
    {
        Livewire::visit(new class extends Component {
            function render() {
                return <<<'HTML'
                <div>
                    <div x-data="{ showCounter: false }">
                        <button @click="showCounter = true" dusk="button">show</button>

                        <template x-if="showCounter">
                            <div x-data="{ count: 0 }">
                                <button x-on:click="count++" dusk="increment">+</button>

                                <h1 x-text="count" dusk="count"></h1>
                            </div>
                        </template>
                    </div>

                    <button wire:click="$commit" dusk="refresh">Refresh</button>
                </div>
                HTML;
            }
        })
            ->assertMissing('@count')
            ->click('@button')
            ->assertVisible('@count')
            ->assertSeeIn('@count', '0')
            ->click('@increment')
            ->assertSeeIn('@count', '1')
            ->waitForLivewire()->click('@refresh')
            ->assertVisible('@count')
            ->assertSeeIn('@count', '1');
        ;
    }
}
