<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;

class JavascriptUnwatchBrowserTest extends \Tests\BrowserTestCase
{
    public function test_unwatch_works_with_inline_alpine_component()
    {
        Livewire::visit(new class () extends Component {
            public int $someProperty = 0;

            public function increment()
            {
                $this->someProperty++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button type="button" wire:click="increment" dusk="increment">Increment</button>

                    <div x-data="{
                        unwatch: null,
                        somePropertyWatched: 0,
                        init: function() {
                            this.unwatch = this.$wire.$watch('someProperty', (value, old) => {
                                this.somePropertyWatched = value;
                            });
                        },
                        executeUnwatch: function() {
                            if (typeof this.unwatch === 'function') {
                                this.unwatch();
                            }
                        }
                    }">
                        <button type="button" x-on:click="executeUnwatch" dusk="unwatch">Unwatch</button>
                        <span x-text="somePropertyWatched" dusk="somePropertyWatched"></span>
                    </div>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@somePropertyWatched', '0')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@somePropertyWatched', '1')
            ->click('@unwatch')
            ->pause(500) // Give Alpine a moment to process the unwatch
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@somePropertyWatched', '1');
    }

    public function test_unwatch_works_with_alpine_data_component()
    {
        Livewire::visit(new class () extends Component {
            public int $someProperty = 0;

            public function increment()
            {
                $this->someProperty++;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button type="button" wire:click="increment" dusk="increment">Increment</button>
                    
                    <div x-data="myComponent">
                        <button type="button" x-on:click="executeUnwatch" dusk="unwatch">Unwatch</button>
                        <span x-text="somePropertyWatched" dusk="somePropertyWatched"></span>
                    </div>
                        
                    <script>
                        document.addEventListener('alpine:init', () => {
                            Alpine.data('myComponent', () => ({
                                unwatch: null,
                                somePropertyWatched: 0,
                                init () {
                                    this.unwatch = this.$wire.$watch('someProperty', (value, old) => {
                                        this.somePropertyWatched = value;
                                    });
                                },
                                executeUnwatch () {
                                    if (typeof this.unwatch === 'function') {
                                        this.unwatch();
                                    }
                                }
                            }))
                        })
                    </script>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->assertSeeIn('@somePropertyWatched', '0')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@somePropertyWatched', '1')
            ->click('@unwatch')
            ->pause(500) // Give Alpine a moment to process the unwatch
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@somePropertyWatched', '1');
    }
}
