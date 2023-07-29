<?php

namespace Livewire\Features\SupportModels;

use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends \Tests\BrowserTestCase
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
                            Alpine.directive('counter', function (el, {value}) {
                                if(value === 'increment') {
                                    Alpine.bind(el, {
                                        'x-on:click.prevent'() {
                                            this.$data.__counter++;
                                        }
                                    })
                                } else if(!value) {
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
            ->click('@increment')
            ->waitForLivewire()
            ->assertInputValue('@counter', '1')
        ;
    }
}

